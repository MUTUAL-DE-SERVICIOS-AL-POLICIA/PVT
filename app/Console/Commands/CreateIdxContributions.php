<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateIdxContributions extends Command
{
    protected $signature = 'db:create-idx-contribs';
    protected $description = 'Crea índice concurrente en contributions(affiliate_id, month_year) y aid_contributions(affiliate_id, month_year)';

    public function handle(): int
    {
        DB::statement("
            CREATE INDEX CONCURRENTLY IF NOT EXISTS
              idx_contributions_affiliate_month
            ON contributions (affiliate_id, month_year)
        ");

        DB::statement("
            CREATE INDEX CONCURRENTLY IF NOT EXISTS
              idx_aid_contributions_affiliate_month
            ON aid_contributions (affiliate_id, month_year)
        ");

        $this->info('Índices creados: idx_contributions_affiliate_month');
        return 0;
    }
}
