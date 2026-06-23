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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('expense_subcategory_id')->nullable()->after('expense_category_id')->constrained()->nullOnDelete();
            $table->foreignId('merchant_id')->nullable()->after('expense_subcategory_id')->constrained()->nullOnDelete();
            $table->foreignId('subscription_id')->nullable()->after('merchant_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['expense_subcategory_id']);
            $table->dropForeign(['merchant_id']);
            $table->dropForeign(['subscription_id']);
            $table->dropColumn(['expense_subcategory_id', 'merchant_id', 'subscription_id']);
        });
    }
};
