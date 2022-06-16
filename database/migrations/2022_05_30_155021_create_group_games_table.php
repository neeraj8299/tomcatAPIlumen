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
        Schema::create('room_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('session_id')->constrained('sessions', 'id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->char('display_room_name');
            $table->float('score')->default(0.0);
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
        Schema::dropIfExists('group_games');
    }
};
