<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Company;
use App\Models\Slot;
use App\Models\User;
use Storage;

class AdminController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name', 'url_logo')->paginate(25);

        $suggestions = Feedback::where('feedback_type', 'suggestion')->select('id', 'user_name', 'body', 'is_public')->get();

        $ratings = Feedback::where('feedback_type', 'rating')->select('id', 'user_name', 'body', 'is_public', 'rating')->get();

        return view('dashboard', compact('companies', 'ratings', 'suggestions'));
    }
    /************************************************************************************************* manage companies */
    public function create_company(Request $request)
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

        return redirect()->route('home')->withMessage('Company added successfully.');
    }

    public function update_company(Request $request)
    {
        $companyData = $request->validate([
            'company_name' => ['required', 'string', 'max:190'],
            'company_logo' => ['nullable', 'file'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $updateCompany = Company::find($companyData['company_id']);

        $updateCompany->name = $companyData['company_name'];

        if (isset($companyData['company_logo'])) {
            Storage::disk('s3')->delete('logos/' . $updateCompany->file_name);

            $path = $request->file('company_logo')->store('logos', 's3');

            Storage::disk('s3')->setVisibility($path, 'public');

            $updateCompany->file_name = basename($path);

            $updateCompany->url_logo = Storage::disk('s3')->url($path);
        }

        $updateCompany->save();

        return redirect()->route('home')->withMessage('Company updated successfully.');
    }

    public function delete_company($id)
    {
        $company = Company::find($id);

        Storage::disk('s3')->delete('logos/' . $company->file_name);

        $company->delete();

        return redirect()->route('home')->withMessage('Company deleted successfully.');
    }
    /************************************************************************************************* statistics */
    public function show_statistics()
    {
        $statistics = array();

        array_push($statistics, [
            'count' => User::role('free')->count(),
            'icon' => 'single-02',
            'title' => 'Total Amount of free users',
        ]);
        array_push($statistics, [
            'count' => User::role('semi-premium')->count(),
            'icon' => 'money-coins',
            'title' => 'total amount of semi-premium users',
        ]);
        array_push($statistics, [
            'count' => User::role('premium')->count(),
            'icon' => 'trophy',
            'title' => 'total amount of premium users',
        ]);
        array_push($statistics, [
            'count' => $statistics[0]['count'] + $statistics[1]['count'] + $statistics[2]['count'],
            'icon' => 'circle-08',
            'title' => 'total amount of users',
        ]);
        array_push($statistics, [
            'count' => Feedback::where('feedback_type', 'suggestion')->count(),
            'icon' => 'air-baloon',
            'title' => 'total amount of suggestions',
        ]);
        array_push($statistics, [
            'count' => Feedback::where('feedback_type', 'rating')->count(),
            'icon' => 'book-bookmark',
            'title' => 'total amount of ratings',
        ]);
        array_push($statistics, [
            'count' => Slot::count(),
            'icon' => 'badge',
            'title' => 'total amount of registered slots',
        ]);
        array_push($statistics, [
            'count' => Slot::whereNull('company_id')->whereNotNull('company_name')->count(),
            'icon' => 'shop',
            'title' => "total amount of slots that doesn't have an existing company associated",
        ]);

        return view('statistics', compact('statistics'));
    }
}
