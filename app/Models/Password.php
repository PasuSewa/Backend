<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class Password extends Model
{
    use HasFactory;

    protected $table = "passwords";

    protected $fillable = [
        "slot_id",
        "password",
        "char_count",
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
