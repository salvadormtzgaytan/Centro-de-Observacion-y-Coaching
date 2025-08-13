<?php

namespace App\Providers;

use App\Models\GuideItemResponse;
use App\Models\GuideResponse;
use App\Models\TemplateItem;
use App\Observers\GuideItemResponseObserver;
use App\Observers\GuideResponseObserver;
use App\Observers\TemplateItemObserver;
use Illuminate\Auth\Events\Attempting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forzar URLs sin /public/ en desarrollo
        // if (app()->environment('local') && request()->getHost() === 'local.coaching') {
        //     \Illuminate\Support\Facades\URL::forceRootUrl('http://local.coaching');
        // }

        //
        // Password::defaults(function () {
        //    return Password::min(12)
        //        ->mixedCase()        // mayúsculas y minúsculas
        //        ->letters()          // al menos una letra
        //        ->numbers()          // al menos un dígito
        //        ->symbols()          // al menos un símbolo
        //        ->uncompromised();   // no filtrada en brechas
        // });
        GuideItemResponse::observe(app(GuideItemResponseObserver::class));
        GuideResponse::observe(app(GuideResponseObserver::class));
        TemplateItem::observe(TemplateItemObserver::class);
        Event::listen(Attempting::class, function ($event) {
            $user = \App\Models\User::where('email', $event->credentials['email'])->first();
            if ($user && ! $user->is_active) {
                abort(403, 'Tu cuenta está deshabilitada.');
            }
        });
    }
}
