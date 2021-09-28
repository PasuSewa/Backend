<?php

return [
    "success" => [
        "auth" => [
            "email_sent" => "Email sent successfully.",
            "user_created" => "User was registered successuflly.",
            "email_verified" => "Email was verified successfully.",
            "refresh_2fa_secret" => "Yuor secret key was regenerated successfully. Please scan the QR code, or copy and paste the secret key, in order to maintain access to your account.",
            "2fa_code_is_correct" => "The second factor authentication succeeded, you may login now.",
            "logged_out" => "Logged you out successfully.",
            "access_granted" => "Access Granted.",
            "user_updated_successfully" => "Your access credentials were updated successfully.",
        ],
        "feedback" => [
            "received" => "Thank you for your feedback, we will take it in count.",
            "obtained" => "Ratings and Suggestions obtained successfully."
        ],
        "user_updated_preferred_lang" => "Request received. From now on we'll message you in this language.",
        "coinbase_webhook_received" => "Status notification received successfully.",
        "payment_instance_started" => "We're now verifying the payment, we'll let you know when it finishes.",
        "purchase_finished" => "Payment successful.",
        "credentials" => [
            "created" => "New credential was created successfully."
        ]
    ],
    "error" => [
        "generic" => "Oops... Seems like there was an error... Please try again later.",
        "user_was_not_found_or_isnt_allowed" => "The user that was suposed to do this action was not found, or they doesn't have access.",
        "parameter_was_incorrect" => "The parameter given was incorrect.",
        "unauthorized" => "You are not allowed to make use of this resourse.",
        "validation" => "One or more of the credentials sent were incorrect.",
        "2fa_code_invalid" => "The second factor authentication has failed. Please try again.",
        "coinbase_signature_failed" => "The given signature is invalid."
    ],
];
