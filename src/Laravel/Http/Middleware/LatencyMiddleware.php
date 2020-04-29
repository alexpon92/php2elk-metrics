<?php
declare(strict_types=1);

namespace Php2ElkMetrics\Laravel\Http\Middleware;

use Illuminate\Http\Request;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Php2ElkMetrics\Metrics\DefaultMetrics\Common\LatencyMetric;

class LatencyMiddleware
{
    /**
     * @var float|null
     */
    private $start;

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $this->start = microtime(true);
        return $next($request);
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    public function terminate($request, $response): void
    {
        if (!$this->start) {
            return;
        }

        $latency    = microtime(true) - $this->start;
        $methodName = $this->compileMethodName(
            $request->getMethod(),
            Route::currentRouteName() ?? $request->getRequestUri()
        );

        event(
            new LatencyMetric(
                $methodName,
                $latency,
                new \DateTime()
            )
        );
    }

    private function compileMethodName(string $httpMethod, string $methodName): string
    {
        return "$httpMethod.$methodName";
    }
}