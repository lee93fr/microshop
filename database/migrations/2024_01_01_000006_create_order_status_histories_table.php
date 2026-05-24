<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()
                  ->references('id')->on('users')->nullOnDelete();
            $table->enum('from_status', [
                'pending','processing','supplier_preparing',
                'ready_at_supplier','picked_up','delivered','cancelled',
            ])->nullable();
            $table->enum('to_status', [
                'pending','processing','supplier_preparing',
                'ready_at_supplier','picked_up','delivered','cancelled',
            ]);
            $table->text('comment')->nullable();
            $table->timestamp('changed_at');

            $table->index('order_id');
        });
    }

    public function down(): void { Schema::dropIfExists('order_status_histories'); }
};
