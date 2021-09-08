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
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('recovery_email');
            $table->string('phone_number');
            $table->string('invitation_code')->nullable();
            $table->string('two_factor_secret', 250)->nullable();
            $table->string('two_factor_code_email', 250)->nullable();
            $table->string('two_factor_code_recovery', 250)->nullable();
            $table->string('anti_fishing_secret', 250);
            $table->string('preferred_lang', 2);
            $table->integer('slots_available')->default(5);
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
