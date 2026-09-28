<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InjectLogoutForm
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only inject for HTML responses and authenticated users
        if (!Auth::check()) {
            return $response;
        }

        $contentType = $response->headers->get('Content-Type');
        if ($contentType && strpos($contentType, 'text/html') === false) {
            return $response;
        }

        $content = $response->getContent();
        if (!is_string($content) || stripos($content, '</body>') === false) {
            return $response;
        }

        // Avoid injecting multiple times
        if (stripos($content, 'id="logout-form"') !== false) {
            return $response;
        }

        $injection = <<<'HTML'
            <!-- Injected logout form and handler -->
            <form id="logout-form" action="/logout" method="POST" style="display:none;">
                <input type="hidden" name="_token" value="%CSRF%" />
            </form>
            <script>
            document.addEventListener('DOMContentLoaded', function () {
                try {
                    var anchors = document.querySelectorAll('a[href="#logout"], a[href*="#logout"], .menu-logout');
                    anchors.forEach(function (a) {
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            var f = document.getElementById('logout-form');
                            if (f) f.submit();
                        });
                    });
                } catch (err) {
                    console.error('logout inject error', err);
                }
            });
            </script>
        HTML;

        // Insert CSRF token
        $csrf = csrf_token();
        $injection = str_replace('%CSRF%', e($csrf), $injection);

        // Inject just before </body>
        $content = str_ireplace('</body>', $injection . "</body>", $content);
        $response->setContent($content);

        return $response;
    }
}
