<?php

use App\Enums\PurchaseStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->date('purchase_date');

            $table->string('status')
                ->default(PurchaseStatus::DRAFT->value);

            $table->decimal('subtotal', 12, 2)->default(0);

            $table->decimal('discount', 12, 2)->default(0);

            $table->decimal('shipping_cost', 12, 2)->default(0);

            $table->decimal('total', 12, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('purchase_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};