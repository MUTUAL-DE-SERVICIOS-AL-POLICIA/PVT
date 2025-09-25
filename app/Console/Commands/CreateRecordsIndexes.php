<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateRecordsIndexes extends Command
{
    protected $signature = 'db:records-indexes';
    protected $description = 'Crear indices PostgreSQL en la tabla records para optimizar consultas';

    public function handle(): int
    {
        $statements = [
            "CREATE EXTENSION IF NOT EXISTS pg_trgm;",
            "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_records_action_trgm
             ON records USING GIN (action gin_trgm_ops);",
            "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_records_morph_role_created
             ON records (recordable_type, recordable_id, role_id, created_at DESC);",
            "CREATE INDEX CONCURRENTLY IF NOT EXISTS idx_records_loans_role_created
             ON records (recordable_id, role_id, created_at DESC)
             WHERE recordable_type = 'loans';",
        ];

        foreach ($statements as $sql) {
            DB::statement($sql);
            $this->info('OK: ' . strtok(trim($sql), "\n")); // línea resumida
        }

        return 0;
    }
}