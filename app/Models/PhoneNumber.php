<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class PhoneNumber extends Model
{
    use HasFactory;

    protected $table = "phone_numbers";

    protected $fillable = [
        "slot_id",
        "phone_number",
        "opening",
        "char_count",
        "ending",
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
