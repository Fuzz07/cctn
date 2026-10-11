<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('description');
            $table->decimal('discount_percent', 5, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('promo_id')->nullable()->constrained('promos')->nullOnDelete();
            $table->string('promo_title', 120)->nullable();
            $table->text('promo_description')->nullable();
            $table->decimal('promo_discount_percent', 5, 2)->default(0);
            $table->decimal('promo_discount_amount', 10, 2)->default(0);
            $table->decimal('original_amount', 10, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('promo_id');
            $table->dropColumn([
                'promo_title',
                'promo_description',
                'promo_discount_percent',
                'promo_discount_amount',
                'original_amount',
            ]);
        });

        Schema::dropIfExists('promos');
    }
};
