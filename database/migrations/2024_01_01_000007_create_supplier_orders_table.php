<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('supplier_orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 30)->unique();
            $table->jsonb('order_ids');
            $table->enum('status', ['draft', 'sent', 'confirmed'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->enum('sent_via', ['email', 'sms', 'both'])->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void { Schema::dropIfExists('supplier_orders'); }
};
