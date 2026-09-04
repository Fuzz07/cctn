<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->onDelete('cascade');
            $table->string('payment_type', 50); // gcash, maya, bank_transfer, credit_card, debit_card
            $table->string('provider_name', 100); // GCash, Maya, BDO, BPI, UnionBank, Metrobank, Visa, Mastercard
            $table->string('account_name', 150);
            $table->string('account_number', 100);
            $table->boolean('is_default')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_payment_methods');
    }
};
