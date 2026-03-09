<?php

use App\Enums\IssuerTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->enum('issuer_type', array_column(IssuerTypeEnum::cases(), 'value'))
                ->default(IssuerTypeEnum::PADRAO->value)
                ->after('regime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('issuers', function (Blueprint $table) {
            $table->dropColumn('issuer_type');
        });
    }
};
