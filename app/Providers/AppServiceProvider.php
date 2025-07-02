<?php

namespace App\Providers;

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
        //
       //Password::defaults(function () {
       //    return Password::min(12)
       //        ->mixedCase()        // mayúsculas y minúsculas
       //        ->letters()          // al menos una letra
       //        ->numbers()          // al menos un dígito
       //        ->symbols()          // al menos un símbolo
       //        ->uncompromised();   // no filtrada en brechas
       //});
    }
}
