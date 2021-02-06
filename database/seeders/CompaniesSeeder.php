<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompaniesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Company::create([
            'name' => 'AFIP',
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png'
        ]);
        Company::create([
            'name' => 'AFIP 1',
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png'
        ]);
        Company::create([
            'name' => 'AFIP 2',
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png'
        ]);
        Company::create([
            'name' => 'AFIP 3',
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png'
        ]);
        Company::create([
            'name' => 'AFIP 4',
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png'
        ]);
    }
}
