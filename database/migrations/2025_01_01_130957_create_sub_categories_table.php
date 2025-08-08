<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id(); // Auto-incrementing ID
            $table->foreignId('category_id') // Foreign key for category
                ->constrained('categories')
                ->onDelete('cascade'); // Deletes subcategories if the category is deleted
            $table->string('name'); // Subcategory name
            $table->string('slug')->unique(); // Unique slug
            $table->timestamps(); // Created_at and updated_at timestamps
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
