<?php

namespace App\Providers;

use App\Helpers\Currency;
use App\Models\Property;
use App\Observers\PropertyObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        mb_internal_encoding('UTF-8');
        Schema::defaultStringLength(191);
        Model::unguard();

        Property::observe(PropertyObserver::class);

        // @money(amount) — affiche un montant XOF dans la devise préférée de l'utilisateur
        Blade::directive('money', function (string $expression) {
            return "<?php echo \\App\\Helpers\\Currency::format((float)({$expression})); ?>";
        });

        // @moneyIn(amount, currency) — affiche dans une devise précise
        Blade::directive('moneyIn', function (string $expression) {
            return "<?php echo \\App\\Helpers\\Currency::format((float)({$expression})); ?>";
        });
    }
}
