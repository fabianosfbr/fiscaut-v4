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
        Schema::table('users', function (Blueprint $table) {

            $table->enum('status', ['active', 'pending', 'inactive'])->default('pending')->after('password');
            $table->boolean('isAdmin')->default(false)->after('status');
            $table->string('phone', 25)->after('isAdmin');
            $table->string('short_description')->nullable();
            $table->string('avatar', 64)->nullable();
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
