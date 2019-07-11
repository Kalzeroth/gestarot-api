<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->bigIncrements('id')->unique();
            $table->string('from');
            $table->string('to');
            $table->string('text');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email');
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');

        Schema::table('users', function ($table) {
            $table->string('email')->unique()->nullable();
        });

        Schema::table('users', function ($table) {
            $table->string('phone')->unique()->nullable();
        });
    }
}
