<?php

namespace D076\SanctumRefreshTokens\Console\Commands;

use Illuminate\Console\Command;
use D076\SanctumRefreshTokens\Models\PersonalRefreshToken;

class PruneRefreshExpired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:prune-refresh-expired {--hours=24 : The number of hours to retain expired refresh tokens}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune tokens expired for more than specified number of hours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $hours = $this->option('hours');

        $this->components->task(
            'Pruning tokens with expired expires_at timestamps',
            fn () => PersonalRefreshToken::query()->where('expires_at', '<', now()->subHours($hours))->delete()
        );

        if ($expiration = config('sanctum.refresh_token_expiration')) {
            $this->components->task(
                'Pruning tokens with expired expiration value based on configuration file',
                fn () => PersonalRefreshToken::query()->where('created_at', '<', now()->subMinutes($expiration + ($hours * 60)))->delete()
            );
        } else {
            $this->components->warn('Expiration value not specified in configuration file.');
        }

        $this->components->info("Tokens expired for more than [$hours hours] pruned successfully.");

        return 0;
    }
}
