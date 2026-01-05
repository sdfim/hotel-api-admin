<?php

return [
    'secret' => env('JWT_SECRET', 'CabinSelectJWTSecret'),
    'secret_external' => env('JWT_SECRET_EXTERNAL', 'CabinSelectExternalJWTSecret'),
];
