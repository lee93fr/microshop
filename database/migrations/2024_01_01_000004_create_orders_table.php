<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()
                  ->references('id')->on('users')->nullOnDelete();

            $table->enum('status', [
                'pending', 'processing', 'supplier_preparing',
                'ready_at_supplier', 'picked_up', 'delivered', 'cancelled',
            ])->default('pending');

            $table->string('delivery_address');
            $table->string('delivery_city', 100);
            $table->string('delivery_postal_code', 20);
            $table->string('delivery_country', 100)->default('France');

            $table->text('notes')->nullable();
            $table->text('supplier_notes')->nullable();

            $table->enum('payment_method', ['stripe', 'revolut', 'rib', 'cash'])->default('cash');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('payment_link')->nullable();
            $table->string('stripe_session_id')->nullable();
            $table->string('stripe_payment_intent')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('user_id');
            $table->index('payment_status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
