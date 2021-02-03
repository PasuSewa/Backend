<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $table = "security_questions_answers";

    protected $fillable = [
        "slot_id",
        "security_question",
        "security_answer",
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, 'slot_id');
    }
}
