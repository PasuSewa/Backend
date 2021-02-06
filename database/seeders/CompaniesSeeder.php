<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Feedback;

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
            'url_logo' => 'https://www.afip.gob.ar/frameworkAFIP/v1/img/logo_afip.png',
            'file_name' => 'hsdfbksdfhb'
        ]);
    }
}
