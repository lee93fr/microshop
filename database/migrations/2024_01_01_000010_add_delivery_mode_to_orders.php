<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('delivery_mode', ['pickup', 'home'])->default('home')->after('delivery_country');
            $table->decimal('delivery_fee', 8, 2)->default(0)->after('delivery_mode');
            $table->string('delivery_address')->nullable()->change();
            $table->string('delivery_city', 100)->nullable()->change();
            $table->string('delivery_postal_code', 20)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['delivery_mode', 'delivery_fee']);
            $table->string('delivery_address')->nullable(false)->change();
            $table->string('delivery_city', 100)->nullable(false)->change();
            $table->string('delivery_postal_code', 20)->nullable(false)->change();
        });
    }
};
