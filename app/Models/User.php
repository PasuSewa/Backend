<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

use App\Models\Slot;
use App\Models\OpenPayment;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
        "email",
        "recovery_email",
        "phone_number",
        "invitation_code",
        "2fa_secret",
        "2fa_code_email",
        "2fa_code_phone",
        "anti_fishing_secret",
        "preferred_lang",
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        "2fa_secret",
        "2fa_code_email",
        "2fa_code_phone",
        "anti_fishing_secret",
        "remember_token",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        "email_verified_at" => "datetime",
    ];

    public function slots()
    {
        return $this->hasMany(Slot::class);
    }
    
    public function openPayments()
    {
        return $this->hasMany(OpenPayment::class);
    }
}
