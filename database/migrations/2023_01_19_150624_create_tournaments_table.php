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
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->string('fees');
            $table->timestamp('closing_time');
            $table->string('team_size');
            $table->string('prize_pool');
            $table->string('first_prize');
            $table->string('second_prize');
            $table->string('third_prize');
            $table->longText('rules');
            $table->unsignedBigInteger('image_id')->nullable();
            $table->unsignedBigInteger('game_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('image_id')->references('id')->on('tournament_avatars');
            $table->foreign('game_id')->references('id')->on('games');
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('tournaments');
    }
};
