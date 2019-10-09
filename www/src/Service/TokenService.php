<?php

namespace App\Service;

class TokenService
{
    /**
     * Generate a random security token only
     */
    public function generateToken()
    {
        return rtrim(strtr(base64_encode(random_bytes(32)), '/+', '-_'), '=');
    }
}