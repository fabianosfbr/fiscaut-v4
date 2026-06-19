<?php

use App\Enums\SeveridadeValidacaoEnum;
use App\Enums\StatusValidacaoEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfe_validacoes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('nfe_id')
                ->constrained('nfes')
                ->cascadeOnDelete();

            $table->foreignId('tenant_id')
                ->constrained('tenants')
                ->cascadeOnDelete();

            $table->foreignId('issuer_id')
                ->constrained('issuers')
                ->cascadeOnDelete();

            $table->string('regra', 100);
            $table->string('tipo_imposto', 20)->nullable();
            $table->unsignedTinyInteger('n_item')->nullable();

            $table->string('severidade', 20)->default(SeveridadeValidacaoEnum::AVISO->value);
            $table->text('mensagem');
            $table->string('valor_esperado', 255)->nullable();
            $table->string('valor_encontrado', 255)->nullable();

            $table->string('status', 20)->default(StatusValidacaoEnum::PENDENTE->value);

            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['nfe_id', 'status']);
            $table->index(['issuer_id', 'status']);
            $table->index('regra');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfe_validacoes');
    }
};
