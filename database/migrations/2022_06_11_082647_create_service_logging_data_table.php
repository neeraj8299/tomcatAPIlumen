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
        Schema::create('service_logging_data', function (Blueprint $table) {
            $table->id();
            $table->string('service_name');
            $table->json('request_body');
            $table->json('request_header');
            $table->json('response_body')->nullable();
            $table->json('response_header')->nullable();
            $table->integer('response_code')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_logging_data');
    }
};
