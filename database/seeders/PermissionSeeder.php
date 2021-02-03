<?php

namespace Database\Seeders;

use DB;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Crypt;

use PragmaRX\Google2FA\Google2FA;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $google2fa = new Google2FA();

        $admin = User::create([
            'name' => 'Gonzalo Salvador Corvalán',
            'email' => 'mr.corvy@gmail.com',
            'recovery_email' => 'gonzalosalvadorcorvalan@gmail.com',
            'phone_number' => Crypt::encryptString('+5401150488031'),
            '2fa_secret' => Crypt::encryptString($google2fa->generateSecretKey()),
            'anti_fishing_secret' => Crypt::encryptString('anti fishing secret word'),
            'preferred_lang' => 'ESP',
        ]); 

        $adminRole = Role::create(['name' => 'admin']);

        $admin->assignRole('admin');
    }
}
