<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Hanya mengizinkan pengguna dengan role yang diminta.
     * Contoh pemakaian di route: ->middleware('role:admin')
     *
     * @param Request $request Permintaan HTTP yang membawa data pengguna yang sedang login.
     * @param Closure(Request):Response $next Middleware atau controller berikutnya.
     * @param string ...$roles Role yang diizinkan, misalnya "admin" atau "admin,customer".
     * @return Response Melanjutkan permintaan atau menolak dengan kode HTTP 403.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Bila belum login atau role tidak cocok, permintaan dihentikan dan halaman 403 ditampilkan.
        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
