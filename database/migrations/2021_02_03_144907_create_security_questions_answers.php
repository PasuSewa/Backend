<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecurityQuestionsAnswers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_questions_answers', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('slot_id');
            $table->foreign('slot_id')->references('id')->on('slots')->onDelete('cascade');

            $table->string('security_question', 250);
            $table->string('security_answer', 250);
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
        Schema::table('security_questions_answers', function(BluePrint $table){
            $table->dropForeign(['slot_id']);
            $table->dropColumn('slot_id');
        });

        Schema::dropIfExists('security_questions_answers');
    }
}
