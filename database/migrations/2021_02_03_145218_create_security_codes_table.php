<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_codes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_id')->constrained()->onDelete('cascade');

            // this 3 are nullable because the user may have one, or the other, but most likely won't have all 3 at the same time
            $table->string('unique_code')->nullable(); // only 1 security code
            $table->string('multiple_codes', 250)->nullable(); // 10+ security codes
            $table->integer('multiple_codes_length')->nullable();
            $table->text('crypto_codes')->nullable(); // the 27 words used to acces a crypto wallet
            $table->integer('crypto_codes_length')->nullable();
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
        Schema::table('security_codes', function (BluePrint $table) {
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        Schema::dropIfExists('security_codes');
    }
}
