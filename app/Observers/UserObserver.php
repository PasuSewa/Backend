<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\EmailTwoFactorAuth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        $main_email_code = rand(100000, 999999);
        $recovery_email_code = rand(100000, 999999);

        $user->two_factor_code_email = Crypt::encryptString($main_email_code);
        $user->two_factor_code_recovery = Crypt::encryptString($recovery_email_code);

        $user->save();

        $antiFishingSecret = Crypt::decryptString($user->anti_fishing_secret);

        $user->notify(new EmailTwoFactorAuth($main_email_code, $antiFishingSecret, $user->preferred_lang));

        Notification::route('mail', $user->recovery_email)->notify(new EmailTwoFactorAuth($recovery_email_code, $antiFishingSecret, $user->preferred_lang));
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
