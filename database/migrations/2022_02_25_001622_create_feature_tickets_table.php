<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use LucasDotVin\Soulbscription\Models\Feature;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! class_exists(Feature::class)) {
            return;
        }

        Schema::create('feature_tickets', function (Blueprint $table) {
            $table->id();
            $table->decimal('charges')->nullable();
            $table->timestamp('expired_at')->nullable();
            $table->unsignedBigInteger('feature_id');
            $table->timestamps();

            if (config('soulbscription.models.subscriber.uses_uuid')) {
                $table->uuidMorphs('subscriber');
            } else {
                $table->numericMorphs('subscriber');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feature_tickets');
    }
};
