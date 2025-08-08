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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_id');
            $table->string('ticket_name');
            $table->bigInteger('priority')->nullable(true);
            $table->bigInteger('ticket_type')->nullable(true);	
            $table->json('tags')->nullable(true);	
            $table->bigInteger('requester_client_id')->nullable(true); 
            $table->bigInteger('assignee_id')->nullable(true); 
            $table->json('followers')->nullable(true);	
            $table->enum('status', ['A','I','D','C'])->nullable(true)->default('A');
            $table->longText('message')->nullable(true);	
            $table->bigInteger('message_from')->nullable(true);	
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
