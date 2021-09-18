<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\Feedback;

class FeedbackController extends Controller
{
    public function publish($id)
    {
        $feedback = Feedback::find($id);

        $feedback->is_public = true;

        $feedback->save();

        return redirect()->route('home')->withMessage($feedback->type ? 'Suggestion published.' : 'Rating published.');
    }

    public function discard($id)
    {
        $feedback = Feedback::find($id);

        $type = $feedback->type ? 'Suggestion' : 'Rating';

        $feedback->delete();

        return redirect()->route('home')->withMessage($type . ' discarded.');
    }
}
