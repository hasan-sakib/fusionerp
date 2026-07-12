<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakePlatformAdmin extends Command
{
    protected $signature   = 'admin:make {email : The email address of the user to promote}';
    protected $description = 'Promote a user to platform super-admin';

    public function handle(): int
    {
        $email = $this->argument('email');

        $user = User::withoutGlobalScopes()->where('email', $email)->first();

        if ($user === null) {
            $this->error("No user found with email: {$email}");
            return self::FAILURE;
        }

        if ($user->is_platform_admin) {
            $this->info("{$user->name} ({$email}) is already a platform admin.");
            return self::SUCCESS;
        }

        $user->update(['is_platform_admin' => true]);

        $this->info("✓ {$user->name} ({$email}) has been promoted to platform admin.");
        $this->line("  They can now access /admin after logging in.");

        return self::SUCCESS;
    }
}
