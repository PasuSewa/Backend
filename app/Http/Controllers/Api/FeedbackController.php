<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;

use Validator;

use App\Models\Feedback;

class FeedbackController extends Controller
{
    /**
     * Index
     * 
     * Get all the suggestions and ratings that are public
     * 
     * <aside class="notice">The results are stored in cache for 1 week</aside>
     * 
     * @group Feedback
     * 
     * @header Accept-Language es | en | jp
     * 
     */
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

    /**
     * Create
     * 
     * Store a suggestion and/or rating in the database
     * 
     * (Reuqires user role to be "premium")
     * 
     * @group Feedback
     * 
     * @authenticated
     * 
     * @bodyParam body string The body of the suggestion/rating (required)
     * @bodyParam rating int The points given as rating (required only if "type" is false)
     * @bodyParam type boolean true = this is a suggestion, while false = this is a rating
     * 
     * @response {
     *      "status": 200,
     *      "message": "Succes!",
     *      "data": {}
     * }
     * 
     * @response status=401 scenario="validation failed" {
     *      "status": 401,
     *      "message": "error message",
     *      "data": {
     *          "errors": [
     *              {
     *                  "body": ""body" must have at least 5 characters."
     *              }
     *          ],
     *          "request": {
     *              "body": "",
     *              "type": true, 
     *          }
     *      }
     * }
     */
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
