<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // $table->string('size')->nullable(); 
            $table->string('location')->nullable(); 
            $table->string('country_id')->nullable(); 
            $table->string('city_id')->nullable(); 
            $table->string('state_id')->nullable(); 
            $table->string('customer_type')->nullable(); 
            $table->string('primary_contact_name')->nullable(); 
            $table->string('primary_contact_email')->nullable(); 
            $table->string('primary_contact_phone')->nullable();
            $table->string('secondary_contact_name')->nullable(); 
            $table->string('secondary_contact_email')->nullable(); 
            $table->string('secondary_contact_phone')->nullable();
            $table->string('key_challenges')->nullable();
            $table->string('important_considerations')->nullable();
            $table->string('specific_requirements')->nullable(); 
            $table->string('potential_risks')->nullable();
            $table->string('product_features_promised')->nullable(); 
            $table->string('service_level_aggriment')->nullable(); 
            $table->string('discounts')->nullable(); 
            $table->string('general_notes')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('size')->nullable();  
            $table->string('location')->nullable(); 
            $table->string('country_id')->nullable(); 
            $table->string('city_id')->nullable(); 
            $table->string('state_id')->nullable(); 
            $table->string('customer_type')->nullable(); 
            $table->string('primary_contact_name')->nullable(); 
            $table->string('primary_contact_email')->nullable(); 
            $table->string('primary_contact_phone')->nullable();
            $table->string('secondary_contact_name')->nullable(); 
            $table->string('secondary_contact_email')->nullable(); 
            $table->string('secondary_contact_phone')->nullable();
            $table->string('key_challenges')->nullable();
            $table->string('important_considerations')->nullable();
            $table->string('specific_requirements')->nullable(); 
            $table->string('potential_risks')->nullable();
            $table->string('product_features_promised')->nullable(); 
            $table->string('service_level_aggriment')->nullable(); 
            $table->string('discounts')->nullable(); 
            $table->string('general_notes')->nullable();
                });
    }
};
