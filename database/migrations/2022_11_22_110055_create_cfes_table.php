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
        Schema::create('cfes', function (Blueprint $table) {
            $table->id();
            $table->string('chave')->nullable();
            $table->string('nCupom')->nullable();
            $table->string('situacao')->nullable();
            $table->date('data_emissao')->nullable();
            $table->decimal('vCFe', 10, 4)->nullable();
            $table->longText('xml')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cfes');
    }
};
