<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tenants', function (Blueprint $table) {

            $table->string('cnpj')->nullable();
            $table->string('responsavel_tecnico')->nullable();
            $table->string('cpf_responsavel_tecnico')->nullable();
            $table->string('crc_responsavel_tecnico')->nullable();
            $table->string('url')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('endereco')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable();
            $table->string('cep')->nullable();
            $table->string('logo')->nullable();

            $table->enum('active', ['Y', 'N'])->default('Y');

            $table->date('subscription')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('subscription_id', 255)->nullable();
            $table->boolean('subscription_active')->default(false);
            $table->boolean('subscription_suspended')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
