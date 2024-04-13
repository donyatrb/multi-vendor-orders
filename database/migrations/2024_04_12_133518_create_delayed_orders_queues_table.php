<?php

use App\Modules\DelayReport\Models\DelayedOrdersQueue;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delayed_orders_queues', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->unique()
                ->references('id')
                ->on('orders')
                ->cascadeOnUpdate();

            $table->foreignId('agent_id')
                ->nullable()
                ->references('id')
                ->on('agents')
                ->cascadeOnUpdate();

            $table->enum('status', array_values(DelayedOrdersQueue::STATUSES))->default(DelayedOrdersQueue::STATUSES['unchecked']);

            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delayed_orders_queues');
    }
};
