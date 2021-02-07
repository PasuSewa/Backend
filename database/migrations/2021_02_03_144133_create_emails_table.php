<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_id')->constrained()->onDelete('cascade');

            $table->string('email', 250);
            $table->string('opening', 1);
            $table->integer('char_count');
            $table->string('ending');
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
        Schema::table('emails', function(BluePrint $table){
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        Schema::dropIfExists('emails');
    }
}
