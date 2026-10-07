<?php

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->dateTime('sale_date');

            $table->string('status')
                ->default(SaleStatus::DRAFT->value);

            $table->string('payment_method')
                ->default(PaymentMethod::CASH->value);

            $table->decimal('subtotal', 12, 2)
                ->default(0);

            $table->decimal('discount', 12, 2)
                ->default(0);

            $table->decimal('total', 12, 2)
                ->default(0);

            $table->decimal('amount_paid', 12, 2)
                ->default(0);

            $table->decimal('change_amount', 12, 2)
                ->default(0);

            $table->text('notes')
                ->nullable();

            $table->timestamps();

            $table->index('sale_date');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};