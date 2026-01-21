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
        Schema::table('upload_files', function (Blueprint $table) {
            $table->integer('doc_type')->nullable()->after('extension');
            $table->double('doc_value', 10, 2)->nullable()->after('doc_type');
            $table->boolean('blocked')->default(0)->after('doc_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('upload_files', function (Blueprint $table) {
            $table->dropColumn('doc_type');
            $table->dropColumn('doc_value');
            $table->dropColumn('blocked');
        });
    }
};
