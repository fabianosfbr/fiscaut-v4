<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucasDotVin\Soulbscription\Models\Subscription;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! class_exists(Subscription::class)) {
            return;
        }

        Schema::create('subscription_renewals', function (Blueprint $table) {
            $table->id();
            $table->boolean('overdue');
            $table->boolean('renewal');
            $table->foreignId('subscription_id');
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
        Schema::dropIfExists('subscription_renewals');
    }
};
