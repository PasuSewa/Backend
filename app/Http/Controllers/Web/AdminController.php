<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use Storage;

class AdminController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name', 'url_logo')->paginate(25);

        return view('dashboard', compact('companies'));
    }

    public function createCompany(Request $request)
    {
        $companyData = $request->validate([
            'company_name' => ['required', 'string', 'max:190'],
            'company_logo' => ['required', 'file'],
        ]);

        $path = $request->file('company_logo')->store('logos', 's3');

        Storage::disk('s3')->setVisibility($path, 'public');

        Company::create([
            'name' => $companyData['company_name'],
            'file_name' => basename($path),
            'url_logo' => Storage::disk('s3')->url($path),
        ]);
    }

    public function update()
    {
        //
    }

    public function deleteCompany($id)
    {
        Company::find($id)->delete();

        return back();
    }
}
