<?php

return [
    "success" => [
        "auth" => [
            "email_sent" => "Email enviado éxitosamente.",
            "user_created" => "El usuario fue registrado exitosamente.",
            "email_verified" => "Email verificado exitosamente.",
            "refresh_2fa_secret" => "Llave secreta regenerada exitosamente. Porfavor escanee el código QR, o copie y pegue la llave secreta, para garantizar el acceso a su cuenta.",
            "2fa_code_is_correct" => "La autenticación en dos factores se dió exitosamente. Puedes ingresar a tu cuenta.",
            "logged_out" => "La sesión fue cerrada exitosamente.",
            "access_granted" => "Acceso Concedido.",
            "user_updated_successfully" => "Sus datos de acceso fueron actualizados exitosamente.",
        ],
        "feedback" => [
            "received" => "Gracias por el feedback, lo tendremos en cuenta.",
            "obtained" => "Ratings y Sugerencias obtenidas con éxito."
        ],
        "user_updated_preferred_lang" => "Petición recibida. A partir de ahora te enviaremos mensajes en este idioma.",
        "coinbase_webhook_received" => "Notificación de estado recibida exitosamente.",
        "payment_instance_started" => "Estamos verificando el pago, te avisaremos cuando haya finalizado.",
        "purchase_finished" => "Instancia de pago finalizada.",
        "credentials" => [
            "created" => "La nueva credencial fue creada exitosamente."
        ]
    ],
    "error" => [
        "generic" => "Oops... Parece que hubo un error... Por favor, intente nuevamente más tarde.",
        "user_was_not_found_or_isnt_allowed" => "El ususario que se supone está realizando esta acción no fue encontrado, o no tiene el permiso de realizarla",
        "parameter_was_incorrect" => "El parámetro dado es incorrecto.",
        "unauthorized" => "No estás autorizado/a a acceder a este recurso.",
        "validation" => "Una o más de las credenciales enviadas son incorrectas.",
        "2fa_code_invalid" => "La autenticación en dos factores ha fallado. Porfavor intente nuevamente.",
        "coinbase_signature_failed" => "La firma otorgada no es válida."
    ]
];
