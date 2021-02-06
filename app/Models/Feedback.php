<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = "feedback";

    protected $fillable = [
        "user_name",
        "body",
        "rating",
        "is_public",
        "feedback_type",
    ];
}
