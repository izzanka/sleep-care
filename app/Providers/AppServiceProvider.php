<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

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
        Model::unguard();
        Model::shouldBeStrict(! app()->isProduction());
        Date::use(CarbonImmutable::class);
        DB::prohibitDestructiveCommands(app()->isProduction());

        Blade::directive('currency', function ($value) {
            return "Rp <?= number_format($value,0,',','.'); ?>";
        });

        Response::macro('success', function ($data = null, string $message = 'success', int $status = 200) {
            return response()->json([
                'message' => $message,
                'data' => $data,
            ], $status);
        });

        Response::macro('error', function (string $message = 'something went wrong', int $status = 400, $errors = null) {
            $response = [
                'message' => $message,
            ];

            if ($errors) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $status);
        });
    }
}
