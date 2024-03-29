<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsernamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('usernames', function (Blueprint $table) {
            $table->id();

            $table->foreignId('slot_id')->constrained()->onDelete('cascade');

            $table->text('username');
            $table->integer('char_count');
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
        Schema::table('usernames', function (BluePrint $table) {
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        Schema::dropIfExists('usernames');
    }
}
