<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement("ALTER TABLE statistic_issuers MODIFY COLUMN tipo ENUM('entrada', 'saida', 'tomador')");
        DB::statement("ALTER TABLE counted_docs_issuers MODIFY COLUMN valor_tipo ENUM('entrada', 'saida', 'tomador')");
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
