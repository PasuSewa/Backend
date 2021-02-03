<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Slot;

class SecurityCode extends Model
{
    use HasFactory;

    protected $table = "security_codes";

    protected $fillable = [
        "slot_id",
        "unique_security_code",
        "multiple_security_code",
        "crypto_currency_access_code",
    ];

    public function slot()
    {
        return $this->belongsTo(Slot::class, "slot_id");
    }
}
