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
        Schema::create('tasks', function (Blueprint $table) {
            $table->bigInteger('id')->autoIncrement()->unsigned();
            $table->string('title');
            $table->string('description');
            $table->integer('priorities');
            $table->integer('assignee'); 
            $table->integer('reporter');
            $table->enum('status', ['open','inprogress','resolved','closed'])->nullable(true);
            $table->date('date');  
            $table->time('time'); 
            $table->integer('ticket_id'); 
            $table->timestamps();        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
