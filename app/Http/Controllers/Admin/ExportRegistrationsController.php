<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportRegistrationsController extends Controller
{
    /**
     * Download all registrations as a CSV file.
     */
    public function __invoke(): StreamedResponse
    {
        $fileName = 'registrations-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () {
            $output = new SplFileObject('php://output', 'w');

            $output->fputcsv(['ID', 'Name', 'Email', 'Type', 'Discord Code', 'Status', 'Registration Date']);

            Registration::latest()->each(function (Registration $registration) use ($output) {
                $output->fputcsv([
                    $registration->id,
                    $registration->name,
                    $registration->email,
                    $registration->participation_type->label(),
                    $registration->discord_code,
                    $registration->status->value,
                    $registration->created_at?->format('Y-m-d'),
                ]);
            });
        }, $fileName, ['Content-Type' => 'text/csv']);
    }
}
