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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_warehouse_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('to_warehouse_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->integer('qty');
            $table->date('date');
            $table->string('status')->default('pending'); // <--- Tambahkan baris ini
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
