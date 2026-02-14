<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.7.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2025 KONKORD DIGITAL
 */

namespace Modules\Users\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Facades\Innoclapps;
use Symfony\Component\HttpFoundation\Response;

class Localizeable
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        if ($locale) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Determine the locale for the current request.
     */
    protected function determineLocale(Request $request): ?string
    {
        $locales = Innoclapps::locales();

        $locale = $this->determineUserLocale($request) ?? $request->getPreferredLanguage($locales);

        return in_array($locale, $locales) ? $locale : null;
    }

    /**
     * Determine the user locale.
     */
    protected function determineUserLocale(Request $request): ?string
    {
        // Return the user's preferred locale if available
        if ($user = $request->user()) {
            return $user->preferredLocale();
        }

        // Check for a session locale if the request is not part of the API
        if (! $request->isApi() && $request->session()->has('locale')) {
            return $request->session()->get('locale');
        }

        return null;
    }
}
