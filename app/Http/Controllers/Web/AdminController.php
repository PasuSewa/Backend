<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\Company;
use Storage;

class AdminController extends Controller
{
    public function index()
    {
        $companies = Company::select('id', 'name', 'url_logo')->paginate(25);

        $suggestions = Feedback::where('feedback_type', 'suggestion')->select('id', 'user_name', 'body')->get();

        $ratings = Feedback::where('feedback_type', 'rating')->select('id', 'user_name', 'body', 'rating')->get();

        return view('dashboard', compact('companies', 'ratings', 'suggestions'));
    }
/************************************************************************************************* manage companies */
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

        return back()->withMessage('Company added successfully.');
    }

    public function updateCompany(Request $request)
    {
        $companyData = $request->validate([
            'company_name' => ['required', 'string', 'max:190'],
            'company_logo' => ['nullable', 'file'],
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $updateCompany = Company::find($companyData['company_id']);

        $updateCompany->name = $companyData['company_name'];

        if (isset($companyData['company_logo'])) 
        {
            Storage::disk('s3')->delete('logos/' . $updateCompany->file_name);

            $path = $request->file('company_logo')->store('logos', 's3');

            Storage::disk('s3')->setVisibility($path, 'public');

            $updateCompany->file_name = basename($path);

            $updateCompany->url_logo = Storage::disk('s3')->url($path);
        }

        $updateCompany->save();

        return back()->withMessage('Company updated successfully.');
    }

    public function deleteCompany($id)
    {
        $company = Company::find($id);

        Storage::disk('s3')->delete('logos/' . $company->file_name);

        $company->delete();

        return back()->withMessage('Company deleted successfully.');
    }
/************************************************************************************************* ratings & suggestions */
    public function discardSuggestion($id)
    {
        Suggestion::find($id)->delete();

        return back()->withMessage('Suggestion discarded.');
    }

    public function publishSuggestion($id)
    {
        $suggestion = Suggestion::find($id);

        $suggestion->is_public = true;

        $suggestion->save();

        return back()->withMessage('Suggestion published.');
    }

    public function discardRating($id)
    {
        Rating::find($id)->delete();

        return back()->withMessage('Rating discarded.');
    }

    public function publishRating($id)
    {
        $rating = Rating::find($id);

        $rating->is_public = true;

        $rating->save();

        return back()->withMessage('Rating published.');
    }
}
