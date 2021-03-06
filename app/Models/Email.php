<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class Email extends Model
{
    use HasFactory;
    
    protected $table = "emails";

    protected $fillable = [
        "slot_id",
        "email",
        "opening",
        "char_count",
        "ending",
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
