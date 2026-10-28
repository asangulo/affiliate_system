<?php

return [
    // Contraseña inicial de las cuentas de titulares creadas automáticamente.
    // Se puede cambiar con PORTAL_DEFAULT_PASSWORD sin tocar el código.
    'default_password' => env('PORTAL_DEFAULT_PASSWORD', 'Horizonte2026!'),

    // Dominio del correo interno cuando el titular no tiene uno propio.
    'email_domain' => env('PORTAL_EMAIL_DOMAIN', 'clientes.horizonte.local'),
];
