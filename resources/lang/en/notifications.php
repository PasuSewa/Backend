<?php

return [
    "subject" => "Authentication Code for PasuNashi",
    "greeting" => "Hello!",
    "anti_fishing" => "This is an official PasuNashi email and this is the proof: ",
    "code" => "Your Authentication Code is: ",
    "thanks" => "Thank you for trusting in PasuNashi",
    "user_updated" => [
        "subject" => "Your credentials to access PasuNashi were updated",
        "body" => "You successfully updated your access information.",
    ],
    "payments" => [
        "crypto" => [
            "pending_subject" => "Processing payment instance",
            "pending_body" => "Your payment is currently under validation. We'll let you know when its finished.",
            "success_subject" => "Payment successful",
            "success_body" => "Your payment is now fully verified. You have now full access to your purchase.",
            "error_subject" => "Payment failed",
            "error_body" => "Your payment is now fully verified and its not valid. Please contact PasuNashi at oficial@pasunashi.xyz in order to solve the situation.",
        ],
        "paypal" => [
            "success_subject" => "Payment successful",
            "success_body" => "Your payment is now fully verified. You have now full access to your purchase.",
        ],
    ]
];
