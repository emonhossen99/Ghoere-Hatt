<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $tables = [
            'contacts' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
            'districts' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
            'ecom_pixels' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
            'failed_jobs' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'ip_blocks' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
            'personal_access_tokens' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'roles' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT',
            'shipping_charges' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
            'social_media' => 'INT UNSIGNED NOT NULL AUTO_INCREMENT',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($tables as $table => $definition) {
                if (!Schema::hasTable($table)) {
                    continue;
                }
                $col = collect(DB::select("DESCRIBE `{$table}`"))->firstWhere('Field', 'id');
                if ($col && !str_contains($col->Extra, 'auto_increment')) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$definition}");
                }
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down()
    {
        // Do not strip AUTO_INCREMENT on rollback
    }
};
