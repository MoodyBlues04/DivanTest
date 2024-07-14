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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('rate');
            $table->foreignId('source_currency_id');
            $table->foreign('source_currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('cascade');
            $table->foreignId('destination_currency_id');
            $table->foreign('destination_currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
