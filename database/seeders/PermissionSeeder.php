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
                'recovery_code' => Crypt::encryptString('AJF682JK9S')
            ]);
        });

        $adminRole = Role::create(['name' => 'admin']);

        Role::create(['name' => 'free']);
        Role::create(['name' => 'semi-premium']);
        $premiumRole = Role::create(['name' => 'premium']);

        $premiumPermissions = array();
        $adminPermissions = array();

        array_push($adminPermissions, Permission::create(['name' => 'access_dashboard']));
        array_push($adminPermissions, Permission::create(['name' => 'create_companies']));
        array_push($adminPermissions, Permission::create(['name' => 'update_companies']));
        array_push($adminPermissions, Permission::create(['name' => 'delete_companies']));
        array_push($adminPermissions, Permission::create(['name' => 'discard_suggestions']));
        array_push($adminPermissions, Permission::create(['name' => 'publish_suggestions']));
        array_push($adminPermissions, Permission::create(['name' => 'discard_ratings']));
        array_push($adminPermissions, Permission::create(['name' => 'publish_ratings']));

        //user's feedback permissions
        $permissionToGiveRatings = Permission::create(['name' => 'retrieve_ratings']);
        $permissionToGiveSuggestions = Permission::create(['name' => 'retrieve_suggestions']);

        array_push($adminPermissions, $permissionToGiveRatings);
        array_push($adminPermissions, $permissionToGiveSuggestions);

        array_push($premiumPermissions, $permissionToGiveRatings);
        array_push($premiumPermissions, $permissionToGiveSuggestions);

        $adminRole->syncPermissions($adminPermissions);
        $premiumRole->syncPermissions($premiumPermissions);

        $admin->assignRole('admin');
    }
}
