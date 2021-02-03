<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Company;
use App\Models\Email;
use App\Models\Password;
use App\Models\PhoneNumber;
use App\Models\QuestionAnswer;
use App\Models\SecurityCode;
use App\Models\Username;

use App\Models\User;

class Slot extends Model
{
    use HasFactory;

    protected $table = "slots";

    protected $fillable = [
        'user_id',
        'company_id',
        'company_name',
        'name',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function company()
    {
        return $this->hasOne(Company::class);
    }

    public function email()
    {
        return $this->hasOne(Email::class);
    }
    
    public function password()
    {
        return $this->hasOne(Password::class);
    }

    public function phoneNumber()
    {
        return $this->hasOne(PhoneNumber::class);
    }

    public function questionAnswer()
    {
        return $this->hasOne(QuestionAnswer::class);
    }
    
    public function securityCodes()
    {
        return $this->hasOne(SecurityCode::class);
    }
    
    public function username()
    {
        return $this->hasOne(Username::class);
    }
}
