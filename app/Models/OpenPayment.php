<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenPayment extends Model
{
    use HasFactory;

    protected $table = "open_payments";

    protected $guarded = [];

    protected $hidden = [
        'user_id',
        'method',
        'amount',
        'type',
        'code'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
}
