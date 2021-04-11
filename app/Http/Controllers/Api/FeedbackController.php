<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Models\Feedback;
use App\Models\User;

class FeedbackController extends Controller
{
    public function index()
    {
        $suggestions = Feedback::where('feedback_type', 'suggestion')->where('is_published', true)->get();

        $ratings = Feedback::where('feedback_type', 'rating')->where('is_published', true)->get();

        return response()->json([
            'feedback' => [
                'suggestions' => $suggestions,
                'ratings' => $ratings
            ]
        ], 200);
    }

    public function store(Request $request, $feedbackType)
    {
        $data = $request->only('userName', 'body', 'rating');

        $rules = ['required', 'string', 'min:10', 'max:190'];

        $validation = Validator::make($data, [
            'userName' => $rules,
            'body' => $rules,
            'rating' => [Rule::requiredIf($feedbackType === 'rating'), 'integer', 'min:1', 'max:10'],
        ]);

        if($validation->fails())
        {
            return response()->json([
                'message' => 'There was an Error in the validation.',
                'errors' => $validation->errors()
            ], 400);
        }

        if (!isset($data['rating']) && !$feedbackType === 'rating') 
        {
            $type = 'suggestion';

        } elseif ($feedbackType === 'rating' && isset($data['rating'])) 
        {
            $type = 'rating';
        }else 
        {
            $type = '';
        }
        
        Feedback::create([
            'user_name' => $data['userName'],
            'body' => $data['body'],
            'rating' => isset($data['rating']) ? $data['rating'] : null,
            'feedback_type' => $type,
        ]);

        return response()->json([
            'message' => 'Thank you for your feedback, we will take it in count.',
        ], 200);
    }

    public function testGet()
    {
        User::create([
            'name' => 'webhook from get',
            'email' => 'webhook@get.com',
            'recovery_email' => 'webhook@get.com',
            'phone_number' => 'numero',
            'two_factor_secret' => 'secret',
            'anti_fishing_secret' => 'secret',
            'preferred_lang' => 'es',
            'slots_available' => 200
        ]);

        return response()->json(['success' => 'success get'], 200);
    }
    
    public function testPost(Request $request)
    {
        

        // User::create([
        //     'name' => 'webhook from get',
        //     'email' => 'webhook@get.com',
        //     'recovery_email' => 'webhook@get.com',
        //     'phone_number' => 'numero',
        //     'two_factor_secret' => 'secret',
        //     'anti_fishing_secret' => 'secret',
        //     'preferred_lang' => 'es',
        //     'slots_available' => 200
        // ]);

        User::create([
            'name' => 'webhook post',
            'email' => 'mr.corvy2@gmail.com',
            'recovery_email' => 'gonzalosalvadorcorvalan2@gmail.com',
            'phone_number' => '+5401150488031',
            'two_factor_secret' => "2YXIJ4AE6RP4HTW3",
            'anti_fishing_secret' => 'secret',
            'preferred_lang' => 'es',
        ]); 

        return response()->json(['success' => 'success post'], 200);
    }
}