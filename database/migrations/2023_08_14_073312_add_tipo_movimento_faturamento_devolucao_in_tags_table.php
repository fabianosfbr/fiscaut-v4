<?php

use App\Models\Tag;
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
        Schema::table('tagging_tags', function (Blueprint $table) {
            $table->enum('tipo_movimento', Tag::$tipoMovimento)->nullable();
            $table->boolean('faturamento')->default(false);
            $table->boolean('devolucao')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tagging_tags', function (Blueprint $table) {
            $table->dropColumn(['tipo_movimento', 'faturamento', 'devolucao']);
        });
    }
};
