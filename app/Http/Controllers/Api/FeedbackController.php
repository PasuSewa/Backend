<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

use Validator;

use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function index()
    {
        $data = cache()->remember('feedback', 60 * 60 * 24 * 7, function () {
            $suggestions = Feedback::where('type', true)->where('is_public', true)->get();

            $ratings = Feedback::where('type', false)->where('is_public', true)->get();

            return [
                'suggestions' => $suggestions,
                'ratings' => $ratings
            ];
        });

        return response()->success(['feedback' => $data], 'feedback.obtained');
    }

    public function create(Request $request)
    {
        $data = $request->only('body', 'rating', 'type');

        $rules = ['required', 'string', 'min:5', 'max:190'];

        $validation = Validator::make($data, [
            'body' => $rules,
            'rating' => [Rule::requiredIf(!$data['type']), 'integer', 'min:0', 'max:10'],
            'type' => ['required', 'boolean'],
        ]);

        if ($validation->fails()) {
            $data = [
                'errors' => $validation->errors(),
                'request' => $request->all(),
            ];

            return response()->error($data, 'api_messages.error.generic', 400);
        }

        $user = $request->user();

        Feedback::create([
            'user_name' => $user->name,
            'body' => $data['body'],
            'rating' => !$data['type'] && isset($data['rating']) ? $data['rating'] : null,
            'type' => $data['type'],
        ]);

        return response()->success([], 'feedback.received');
    }
}
