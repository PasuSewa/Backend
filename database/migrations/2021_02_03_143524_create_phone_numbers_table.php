<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_numbers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_id')->constrained()->onDelete('cascade');

            $table->string('phone_number', 250);
            $table->string('opening', 3); // the country code
            $table->integer('char_count');
            $table->string('ending'); // the last 4 digits
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
        Schema::table('phone_numbers', function(BluePrint $table){
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        Schema::dropIfExists('phone_numbers');
    }
}
