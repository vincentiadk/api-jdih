<?php

namespace App\Http\Middleware;

use Closure;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\LineFormatter;
use Carbon\Carbon;

class LogRoute
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
        $response = $next($request);

        if (app()->environment('local')) {
            $log = [
                'URI' => $request->getUri(),
                'METHOD' => $request->getMethod(),
                'REQUEST_BODY' => $request->all(),
                'RESPONSE' => $response->getContent()
            ];
            $dateFormat = "Y n j, g:i a";
            // the default output format is "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
            // we now change the default output format according to our needs.
            $output = "%datetime% > %level_name% > %message% %context% %extra%\n";
            // finally, create a formatter
            $formatter = new LineFormatter($output, $dateFormat);

            // Create a handler
            $log_file_name =  Carbon::now()->format('Y-m-d');
            $stream = new StreamHandler(storage_path('logs/api-'.$log_file_name.'.log'), Logger::DEBUG);
            $stream->setFormatter($formatter);

            // create a log channel
            $log1 = new Logger('apilogger');
            $log1->pushHandler($stream);
            $log1->info(json_encode($log));
        }

        return $response;
    }
}