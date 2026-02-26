<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeSuperadminCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:superadmin
        {email : The email of the user to make superadmin}
        {--create : Create the user if they do not exist}
        {--name= : Name for the new user (default: derived from email)}
        {--password= : Password for the new user (default: random 12 chars)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark a user as superadmin by email, or create one with --create';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (! $user) {
            if (! $this->option('create')) {
                $this->error("User with email '{$email}' not found. Use --create to create one.");

                return self::FAILURE;
            }

            $name = $this->option('name') ?? Str::headline(Str::before($email, '@'));
            $password = $this->option('password') ?? Str::password(12);

            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_superadmin' => true,
            ]);

            $this->info("Created superadmin user '{$email}'.");
            $this->line("Password: {$password}");
            $this->newLine();
            $this->warn('Save the password above. It will not be shown again.');

            return self::SUCCESS;
        }

        if ($user->is_superadmin ?? false) {
            $this->info("User '{$email}' is already a superadmin.");

            return self::SUCCESS;
        }

        $user->update(['is_superadmin' => true]);
        $this->info("User '{$email}' is now a superadmin.");

        return self::SUCCESS;
    }
}
