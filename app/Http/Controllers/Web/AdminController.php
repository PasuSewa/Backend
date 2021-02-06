<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;

class AdminController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name', 'url_logo')->paginate(2);

        return view('dashboard', compact('companies'));
    }

    public function create()
    {
        // 
    }

    public function update()
    {
        //
    }

    public function delete()
    {
        //
    }
}
