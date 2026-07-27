<?php

namespace App\Http\Middleware;

use App\Repositories\Master\UserAccessRepository;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AccessModule
{
    protected $user_access_repo;

    public function __construct(UserAccessRepository $user_access_repo)
    {
        $this->user_access_repo = $user_access_repo;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $param): Response
    {
        $user = Auth::user();
        if($user->user_access_id != 0){
            $user_access = $this->user_access_repo->getRecord($user->user_access_id);
            $access_modules = json_decode($user_access->access_module, true);
            $not_set = true;
            $active_flag = false;
            foreach ($access_modules as $mod) {
                if($mod['module'] == $param){
                    $not_set = false;
                    $active_flag = (bool) $mod['active_flag'];
                }
            }
            if(!$not_set){
                if(!$active_flag){
                    return redirect(route('home'));
                }
            } else {
                return redirect(route('home'));
            }
        }
        return $next($request);
    }
}
