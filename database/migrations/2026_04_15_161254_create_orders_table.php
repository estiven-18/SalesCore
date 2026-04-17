<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * DEPRECATED: Usar sales en su lugar
     */
    public function up(): void
    {
        // Migración obsoleta - usar sales en su lugar
        // Schema::create('orders', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('number')->unique();
        //     $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
        //     $table->decimal('total', 10, 2)->default(0);
        //     $table->string('status')->default('pending');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('orders');
    }
};
