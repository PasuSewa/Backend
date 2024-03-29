<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class Username extends Model
{
    use HasFactory;

    protected $table = "usernames";

    protected $guarded = [];

    protected $hidden = [
        'slot_id',
        'username',
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
