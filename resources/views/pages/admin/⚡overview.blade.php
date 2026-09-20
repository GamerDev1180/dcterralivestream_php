<?php

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Enums\ScheduleEventType;
use App\Enums\SponsorTier;
use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use App\Models\Registration;
use App\Models\ScheduleEvent;
use App\Models\Sponsor;
use App\Models\TeamSignup;
use Illuminate\Database\Eloquent\Builder;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Layout('layouts::admin')] #[Title('Overzicht')] class extends Component {
    /**
     * How far the two trend charts look back, in days.
     */
    #[Url]
    public int $days = 30;

    /**
     * The periods the reader can switch between.
     */
    public const PERIODS = [7 => 'Laatste 7 dagen', 30 => 'Laatste 30 dagen', 90 => 'Laatste 90 dagen'];

    public function mount(): void
    {
        $this->days = $this->normalisedDays();
    }

    public function updatedDays(): void
    {
        $this->days = $this->normalisedDays();
    }

    /**
     * The x-axis of the trend charts: one bucket per day, or per week for the
     * longest period so the columns stay readable.
     *
     * @return array<int, array{start: CarbonInterface, end: CarbonInterface, short: string, full: string}>
     */
    #[Computed]
    public function buckets(): array
    {
        $days = $this->normalisedDays();
        $bucketSize = $days > 31 ? 7 : 1;
        $buckets = [];

        for ($stepsBack = (int) ceil($days / $bucketSize) - 1; $stepsBack >= 0; $stepsBack--) {
            $end = now()->endOfDay()->subDays($stepsBack * $bucketSize);
            $start = $end->copy()->subDays($bucketSize - 1)->startOfDay();

            $buckets[] = [
                'start' => $start,
                'end' => $end,
                'short' => $start->locale('nl')->isoFormat('D MMM'),
                'full' => $bucketSize === 1
                    ? $start->locale('nl')->isoFormat('dddd D MMMM')
                    : $start->locale('nl')->isoFormat('D MMM').' t/m '.$end->locale('nl')->isoFormat('D MMM'),
            ];
        }

        return $buckets;
    }

    /**
     * Registrations that came in per bucket.
     *
     * @return array<int, int>
     */
    #[Computed]
    public function registrationsPerBucket(): array
    {
        return $this->countPerBucket($this->timestampsInPeriod(Registration::query()));
    }

    /**
     * The running total of registrations at the end of every bucket.
     *
     * @return array<int, int>
     */
    #[Computed]
    public function cumulativeRegistrations(): array
    {
        $runningTotal = Registration::where('created_at', '<', $this->buckets[0]['start'])->count();

        return collect($this->registrationsPerBucket)
            ->map(function (int $count) use (&$runningTotal): int {
                $runningTotal += $count;

                return $runningTotal;
            })
            ->all();
    }

    /**
     * Team signups per bucket, split into volunteers and organisations.
     *
     * @return array<int, array{name: string, color: string, values: array<int, int>}>
     */
    #[Computed]
    public function teamSignupSeries(): array
    {
        $signups = TeamSignup::where('created_at', '>=', $this->buckets[0]['start'])->get(['type', 'created_at']);

        return collect(TeamSignupType::cases())
            ->map(fn (TeamSignupType $type): array => [
                'name' => $type->label(),
                'color' => $type === TeamSignupType::Volunteer ? 'var(--viz-3)' : 'var(--viz-4)',
                'values' => $this->countPerBucket($signups->where('type', $type)->pluck('created_at')),
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, color: string, value: int}>
     */
    #[Computed]
    public function registrationStatusSegments(): array
    {
        $counts = $this->countsByColumn(Registration::query(), 'status');

        return collect(RegistrationStatus::cases())
            ->map(fn (RegistrationStatus $status): array => [
                'name' => $status->label(),
                'color' => match ($status) {
                    RegistrationStatus::Confirmed => 'var(--viz-good)',
                    RegistrationStatus::Pending => 'var(--viz-warning)',
                    RegistrationStatus::Cancelled => 'var(--viz-critical)',
                },
                'value' => $counts[$status->value] ?? 0,
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, color: string, value: int}>
     */
    #[Computed]
    public function participationSegments(): array
    {
        $counts = $this->countsByColumn(Registration::query(), 'participation_type');

        return collect(ParticipationType::cases())
            ->map(fn (ParticipationType $type): array => [
                'name' => $type->label(),
                'color' => $type === ParticipationType::OnCamera ? 'var(--viz-1)' : 'var(--viz-2)',
                'value' => $counts[$type->value] ?? 0,
            ])
            ->all();
    }

    /**
     * @return array<int, array{name: string, color: string, value: int}>
     */
    #[Computed]
    public function teamStatusSegments(): array
    {
        $counts = $this->countsByColumn(TeamSignup::query(), 'status');

        return collect(TeamSignupStatus::cases())
            ->map(fn (TeamSignupStatus $status): array => [
                'name' => $status->label(),
                'color' => match ($status) {
                    TeamSignupStatus::Approved => 'var(--viz-good)',
                    TeamSignupStatus::Pending => 'var(--viz-warning)',
                    TeamSignupStatus::Rejected => 'var(--viz-critical)',
                },
                'value' => $counts[$status->value] ?? 0,
            ])
            ->all();
    }

    /**
     * Active sponsors per tier.
     *
     * @return array<int, array{label: string, value: int}>
     */
    #[Computed]
    public function sponsorRows(): array
    {
        $counts = $this->countsByColumn(Sponsor::where('is_active', true), 'tier');

        return collect(SponsorTier::cases())
            ->map(fn (SponsorTier $tier): array => [
                'label' => $tier->label(),
                'value' => $counts[$tier->value] ?? 0,
            ])
            ->all();
    }

    /**
     * Programmed hours per event type.
     *
     * @return array<int, array{label: string, value: float, display: string}>
     */
    #[Computed]
    public function scheduleRows(): array
    {
        $events = ScheduleEvent::where('is_active', true)->get(['event_type', 'start_time', 'end_time']);

        return collect(ScheduleEventType::cases())
            ->map(function (ScheduleEventType $type) use ($events): array {
                $hours = round($events->where('event_type', $type)->sum(
                    fn (ScheduleEvent $event): float => $event->start_time->diffInMinutes($event->end_time, absolute: true) / 60
                ), 1);

                return [
                    'label' => $type->label(),
                    'value' => $hours,
                    'display' => str_replace('.', ',', (string) $hours).' uur',
                ];
            })
            ->all();
    }

    /**
     * The labels the chart components need, without the Carbon instances.
     *
     * @return array<int, array{short: string, full: string}>
     */
    #[Computed]
    public function chartLabels(): array
    {
        return array_map(
            fn (array $bucket): array => ['short' => $bucket['short'], 'full' => $bucket['full']],
            $this->buckets,
        );
    }

    /**
     * Keep an unknown period (it can come in through the query string) on the default.
     */
    private function normalisedDays(): int
    {
        return array_key_exists($this->days, self::PERIODS) ? $this->days : 30;
    }

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return Collection<int, CarbonInterface>
     */
    private function timestampsInPeriod(Builder $query): Collection
    {
        return $query->where('created_at', '>=', $this->buckets[0]['start'])->pluck('created_at');
    }

    /**
     * @param  Collection<int, CarbonInterface>  $timestamps
     * @return array<int, int>
     */
    private function countPerBucket(Collection $timestamps): array
    {
        return collect($this->buckets)
            ->map(fn (array $bucket): int => $timestamps->filter(
                fn (CarbonInterface $timestamp): bool => $timestamp->between($bucket['start'], $bucket['end'])
            )->count())
            ->all();
    }

    /**
     * Count the rows of a table grouped by one column.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return array<string, int>
     */
    private function countsByColumn(Builder $query, string $column): array
    {
        return $query->reorder()
            ->groupBy($column)
            ->selectRaw("{$column}, count(*) as aggregate")
            ->pluck('aggregate', $column)
            ->map(fn (int|string $aggregate): int => (int) $aggregate)
            ->all();
    }
}; ?>

<div class="space-y-6">
    {{-- Filters --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">Cijfers over de registraties, het team, de sponsoren en het programma.</p>

        <flux:select wire:model.live="days" class="w-48" aria-label="Periode">
            @foreach ($this::PERIODS as $value => $label)
                <flux:select.option :value="$value">{{ $label }}</flux:select.option>
            @endforeach
        </flux:select>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Nieuwe registraties</x-ui.card.title>
                <x-ui.card.description>{{ $this::PERIODS[$days] }}, {{ $days > 31 ? 'per week' : 'per dag' }}</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.columns
                    :labels="$this->chartLabels"
                    :series="[['name' => 'Registraties', 'color' => 'var(--viz-1)', 'values' => $this->registrationsPerBucket]]"
                />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Totaal aantal registraties</x-ui.card.title>
                <x-ui.card.description>Doorlopend totaal aan het eind van elke {{ $days > 31 ? 'week' : 'dag' }}</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.area :labels="$this->chartLabels" :values="$this->cumulativeRegistrations" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Registraties per status</x-ui.card.title>
                <x-ui.card.description>Alle registraties, niet alleen de gekozen periode</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.donut :segments="$this->registrationStatusSegments" total-label="registraties" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Voor of achter de camera</x-ui.card.title>
                <x-ui.card.description>Hoe deelnemers willen meedoen</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.donut :segments="$this->participationSegments" total-label="deelnemers" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Team aanmeldingen</x-ui.card.title>
                <x-ui.card.description>{{ $this::PERIODS[$days] }}, vrijwilligers en organisaties</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.columns :labels="$this->chartLabels" :series="$this->teamSignupSeries" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Team per status</x-ui.card.title>
                <x-ui.card.description>Alle aanmeldingen, niet alleen de gekozen periode</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.donut :segments="$this->teamStatusSegments" total-label="aanmeldingen" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Sponsoren per tier</x-ui.card.title>
                <x-ui.card.description>Alleen zichtbare sponsoren</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.bars :rows="$this->sponsorRows" color="var(--viz-1)" />
            </x-ui.card.content>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card.header>
                <x-ui.card.title>Programma-uren per onderdeel</x-ui.card.title>
                <x-ui.card.description>Alleen actieve programmaonderdelen</x-ui.card.description>
            </x-ui.card.header>
            <x-ui.card.content>
                <x-charts.bars :rows="$this->scheduleRows" color="var(--viz-2)" />
            </x-ui.card.content>
        </x-ui.card>
    </div>
</div>
