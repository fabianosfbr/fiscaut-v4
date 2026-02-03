<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        $exists = DB::table('schedules')
            ->whereNull('deleted_at')
            ->where('command', 'dashboard:refresh-stats')
            ->exists();

        if ($exists) {
            return;
        }

        DB::table('schedules')->insert([
            'command' => 'dashboard:refresh-stats',
            'command_custom' => null,
            'description' => 'Atualiza estatísticas do Dashboard Fiscal (cache mensal)',
            'params' => '{}',
            'expression' => '0 2 * * *',
            'environments' => null,
            'options' => '{}',
            'options_with_value' => '{}',
            'log_filename' => 'dashboard-fiscal',
            'even_in_maintenance_mode' => false,
            'without_overlapping' => true,
            'on_one_server' => true,
            'webhook_before' => null,
            'webhook_after' => null,
            'email_output' => null,
            'sendmail_error' => false,
            'log_success' => true,
            'log_error' => true,
            'status' => 'active',
            'limit_history_count' => true,
            'max_history_count' => 30,
            'run_in_background' => true,
            'sendmail_success' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        DB::table('schedules')
            ->where('command', 'dashboard:refresh-stats')
            ->delete();
    }
};

