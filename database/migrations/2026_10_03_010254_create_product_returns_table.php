<?php

use App\Enums\ReturnReason;
use App\Enums\ReturnStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('sale_item_id')
                ->constrained('sale_items')
                ->restrictOnDelete();

            $table->foreignId('product_variant_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->unsignedInteger('quantity');

            $table->string('reason');

            $table->boolean('restock')->default(true);

            $table->string('status')
                ->default(ReturnStatus::PENDING->value);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('sale_id');
            $table->index('sale_item_id');
            $table->index('product_variant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};