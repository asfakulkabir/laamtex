<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Repairs the super-admin login after a database import or a password change
 * that never made it into the stored hash.
 *
 * The seeded accounts ship with documented passwords, but a migrated database
 * can carry an older, mismatching hash. Rather than re-seeding the whole shop
 * on a live server, run this one-liner against the same email the login form
 * takes:  php artisan admin:reset-password admin@outfitt.com adminpassword
 */
class ResetAdminPassword extends Command
{
    protected $signature = 'admin:reset-password
        {email=admin@outfitt.com : The admin email (or user id) to repair.}
        {password=adminpassword : The password to set.}';

    protected $description = 'Reset the super admin password so the documented credentials work again.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $password = $this->argument('password');

        $user = User::query()
            ->where('email', $email)
            ->orWhere('id', (int) $email)
            ->first();

        if (! $user) {
            $this->error("No user found with email or id '{$email}'.");
            return self::FAILURE;
        }

        $user->forceFill([
            'password' => $password,
            'role' => User::ROLE_SUPER_ADMIN,
        ])->save();

        $this->info("Password reset for {$user->email} (id {$user->id}).");
        $this->newLine();
        $this->line('Login with:');
        $this->line("    Email:    {$user->email}");
        $this->line("    Password: {$password}");
        $this->line('Then change it under the Staff section if you want something stronger.');

        return self::SUCCESS;
    }
}