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
        Schema::table('tagging_automations', function (Blueprint $table) {
            $table->integer('tag_id');
            $table->string('docs_fiscais');
            $table->string('cfops');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tagging_automations', function (Blueprint $table) {
            $table->dropColumn(['docs_fiscais', 'cfops']);
        });
    }
};
