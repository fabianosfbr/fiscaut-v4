<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucasDotVin\Soulbscription\Models\Feature;
use LucasDotVin\Soulbscription\Models\Plan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! class_exists(Feature::class) || ! class_exists(Plan::class)) {
            return;
        }

        Schema::create('feature_plan', function (Blueprint $table) {
            $table->id();
            $table->decimal('charges')->nullable();
            $table->unsignedBigInteger('feature_id');
            $table->unsignedBigInteger('plan_id');
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
        Schema::dropIfExists('feature_plan');
    }
};
