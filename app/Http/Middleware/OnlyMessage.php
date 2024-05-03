<?php

namespace App\Http\Middleware;

use App\Repositories\UserRepository;
use Closure;
use Illuminate\Support\Facades\Log;

class OnlyMessage
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      if($request && ( isset($request['message']) || isset($request['callback_query']) )) {
          return $next($request);
      }
      abort(200);
    }

}
