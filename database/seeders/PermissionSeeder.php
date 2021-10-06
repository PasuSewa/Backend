<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

use Illuminate\Support\Facades\Crypt;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin = User::withoutEvents(function () {
            return User::create([
                'name' => 'Gonzalo Salvador Corvalán',
                'email' => 'mr.corvy@gmail.com',
                'recovery_email' => 'gonzalosalvadorcorvalan@gmail.com',
                'phone_number' => Crypt::encryptString('+5401150488031'),
                'two_factor_secret' => Crypt::encryptString("2YXIJ4AE6RP4HTW3"),
                'anti_fishing_secret' => Crypt::encryptString('secret'),
                'preferred_lang' => 'es',
                'invitation_code' => '4LGDR0COFF',
                'recovery_code' => Crypt::encryptString('AJF682JK9S'),
                'slots_available' => 3
            ]);
        });

        $admin_role = Role::create(['name' => 'admin']);

        Role::create(['name' => 'free']);
        Role::create(['name' => 'semi-premium']);
        $premium_role = Role::create(['name' => 'premium']);

        $premium_permissions = array();
        $admin_permissions = array();

        array_push($admin_permissions, Permission::create(['name' => 'access_dashboard']));
        array_push($admin_permissions, Permission::create(['name' => 'create_companies']));
        array_push($admin_permissions, Permission::create(['name' => 'update_companies']));
        array_push($admin_permissions, Permission::create(['name' => 'delete_companies']));
        array_push($admin_permissions, Permission::create(['name' => 'discard_feedback']));
        array_push($admin_permissions, Permission::create(['name' => 'publish_feedback']));

        //user's feedback permissions
        $permission_to_retrieve_feedback = Permission::create(['name' => 'retrieve_feedback']);

        array_push($admin_permissions, $permission_to_retrieve_feedback);
        array_push($premium_permissions, $permission_to_retrieve_feedback);

        $admin_role->syncPermissions($admin_permissions);
        $premium_role->syncPermissions($premium_permissions);

        $admin->assignRole('admin');
    }
}
