<?php

namespace App\Http\Middleware;

use App\Models\ServiceLoggingData;
use Closure;

class ServiceLoggingMiddleware
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
        $serviceLogData = ServiceLoggingData::create([
            'service_name' => $request->path(),
            'request_body' => json_encode($request->all()),
            'request_header' => json_encode($request->header()),
        ]);

        $response = $next($request);

        ServiceLoggingData::where('id', $serviceLogData->id)->update([
            'response_body' => json_encode($response->getContent()),
            'response_header' => json_encode($response->headers),
            'response_code' => $response->status(),
        ]);

        return $response;
    }
}
