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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Auto-increment primary key
            $table->unsignedBigInteger('category')->nullable();
            $table->unsignedBigInteger('sub_category')->nullable();
            $table->json('uploadedImages')->nullable(); // For storing JSON data
            $table->json('size')->nullable(); // For storing size options
            $table->string('product_id')->unique()->nullable();
            $table->json('price_collection')->nullable(); // For table data with size, listing price, etc.
            $table->decimal('netWeight', 8, 2)->nullable(); // Use decimal for weight
            $table->string('name')->nullable();
            $table->integer('netQuantity')->nullable();
            $table->text('manufacturerDetails')->nullable();
            $table->text('packerDetails')->nullable();
            $table->text('description')->nullable();
            $table->text('importerDetails')->nullable();
            $table->string('warrantyPeriod')->nullable();
            $table->string('warrantyType')->nullable();
            $table->string('brand')->nullable();
            $table->string('product_count')->nullable();
            $table->timestamps(); // Adds created_at and updated_at columns
            $table->softDeletes(); // Adds deleted_at column for soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
