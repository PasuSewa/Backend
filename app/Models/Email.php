<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class Email extends Model
{
    use HasFactory;

    protected $table = "emails";

    protected $guarded = [];

    protected $hidden = [
        'slot_id',
        'email',
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
