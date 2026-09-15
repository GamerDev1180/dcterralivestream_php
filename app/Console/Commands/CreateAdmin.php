<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

use function Laravel\Prompts\password;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

/**
 * Creates (or updates) an admin account from the terminal, so no default password
 * has to live in the code. Usage: php artisan app:create-admin
 */
#[Signature('app:create-admin')]
#[Description('Create or update an admin user that can log in to the admin panel')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = text('Admin username', required: true);
        $email = text('Admin email', required: true);
        $password = password('Admin password (min 12 chars)', required: true);
        $role = select('Role', [UserRole::SuperAdmin->value => 'Super Admin', UserRole::Admin->value => 'Admin'], default: UserRole::SuperAdmin->value);

        $validator = Validator::make(
            ['username' => $username, 'email' => $email, 'password' => $password],
            ['username' => ['min:3', 'max:100'], 'email' => ['email'], 'password' => ['min:12']],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        User::updateOrCreate(['username' => $username], [
            'name' => $username,
            'email' => $email,
            'password' => $password,
            'role' => UserRole::from($role),
        ]);

        $this->info("Admin user \"{$username}\" ({$role}) is ready.");

        return self::SUCCESS;
    }
}
