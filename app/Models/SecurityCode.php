<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class SecurityCode extends Model
{
    use HasFactory;

    protected $table = "security_codes";

    protected $guarded = [];

    protected $hidden = [
        'slot_id',
        'unique_code',
        'multiple_codes',
        'crypto_codes',
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
