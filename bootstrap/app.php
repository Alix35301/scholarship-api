    <?php

    use Illuminate\Foundation\Application;
    use Illuminate\Foundation\Configuration\Exceptions;
    use Illuminate\Foundation\Configuration\Middleware;
    use Illuminate\Http\Request;

    return Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__ . '/../routes/web.php',
            commands: __DIR__ . '/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware) {
            $middleware->web(append: [
                \App\Http\Middleware\HandleInertiaRequests::class,
                \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            ]);

            // // Use numeric values for proxy headers
            // $middleware->trustProxies(headers:
            //     0x1 | // X_FORWARDED_FOR
            //     0x2 | // X_FORWARDED_HOST
            //     0x4 | // X_FORWARDED_PORT
            //     0x8 | // X_FORWARDED_PROTO
            //     0x10  // X_FORWARDED_AWS_ELB
            // );

            // $middleware->trustProxies(at: '*');
        })
        ->withExceptions(function (Exceptions $exceptions) {
            //
        })->create();
