<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('delay_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->references('id')
                ->on('orders')
                ->cascadeOnUpdate();

            $table->foreignId('agent_id')
                ->nullable()
                ->references('id')
                ->on('agents')
                ->cascadeOnUpdate();

            $table->foreignId('vendor_id')
                ->references('id')
                ->on('vendors')
                ->cascadeOnUpdate();

            $table->integer('delay_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delay_reports');
    }
};
