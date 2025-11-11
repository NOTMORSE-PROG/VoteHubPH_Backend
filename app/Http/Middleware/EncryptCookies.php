<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        'next-auth.session-token',
        '__Secure-next-auth.session-token',
        'next-auth_session-token',
        '__Host-next-auth.session-token',
        'next-auth.csrf-token',
        'next-auth_csrf-token',
        'next-auth.callback-url',
        'next-auth_callback-url',
    ];
}
