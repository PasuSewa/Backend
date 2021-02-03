<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('recovery_email');
            $table->string('phone_number');
            $table->string('2fa_secret', 250);
            $table->string('2fa_code_email', 250)->nullable();
            $table->string('2fa_code_phone', 250)->nullable();
            $table->string('anti_fishing_secret', 250);
            $table->string('preferred_lang', 3);
            $table->integer('slots_available');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
