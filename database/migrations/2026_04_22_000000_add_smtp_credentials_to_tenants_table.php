<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->string('smtp_host')->nullable()->after('superlogica_access_token');
            $table->integer('smtp_port')->nullable()->after('smtp_host');
            $table->string('smtp_username')->nullable()->after('smtp_port');
            $table->string('smtp_password')->nullable()->after('smtp_username');
            $table->string('smtp_encryption')->nullable()->after('smtp_password');
            $table->string('smtp_from_email')->nullable()->after('smtp_encryption');
            $table->string('smtp_from_name')->nullable()->after('smtp_from_email');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'smtp_host',
                'smtp_port',
                'smtp_username',
                'smtp_password',
                'smtp_encryption',
                'smtp_from_email',
                'smtp_from_name',
            ]);
        });
    }
};