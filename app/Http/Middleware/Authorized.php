<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepositoryEloquent;
use Closure;
use Sentinel;

class Authorized
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    private $userRepository;

    public function handle($request, Closure $next, $permission = null)
    {
        $this->userRepository = new UserRepositoryEloquent(app());
        $user = $request->user();
        $user = $this->userRepository->find($user->id);

        if ($user && (Sentinel::inRole('admin') ||
                    (Sentinel::inRole('staff') && ($permission==null || $user->authorized($permission))))) {
            return $next($request);
        }

        return redirect()->back()->withErrors(['message' => 'Permission denied']);
    }
}
