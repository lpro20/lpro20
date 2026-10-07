<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('company')->nullable();

            $table->string('phone')->nullable();

            $table->string('email')->nullable();

            $table->string('address')->nullable();

            $table->string('city')->nullable();

            $table->string('country')->default('Cameroun');

            $table->text('notes')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index('name');
            $table->index('phone');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};