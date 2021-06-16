<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    /**
     * La solución para el cors es la siguiente:
     * 
     * 1) Aprendible tiene un video en el que lo explica, buscar por CORS
     * 
     * 2) Aprendible muestra que habria que poner el dominio exacto del que esperamos las peticiones, ya que a mi 
     * no me funcionó, decidí dejarlo como un * y resolvió el problema
     * 
     * En caso de no encontrar el video de Aprendible.com, 
     * 
     * 1) instalar el paquete fruitcacke/cors
     * 
     * 2) publicar el vendor
     * 
     * 3) en config/cors.php dejar todo como acá
     * 
     * 4) agregar el middleware publicado a la lista de middlewares en kernel (el array de $middlewares)
     */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['*'],

    'max_age' => 0,

    'supports_credentials' => true,

];
