<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hpp', function (Blueprint $table) {
            $table->id();
            $table->enum('description', ['Pembelian', 'Penjualan']);
            $table->date('date');
            $table->integer('qty');
            $table->float('cost');
            $table->integer('price');
            $table->float('total_cost');
            $table->integer('qty_balance');
            $table->float('value_balance');
            $table->float('hpp');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hpp');
    }
};
