<?php
// app/Providers/AppServiceProvider.php

namespace App\Providers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        Order::class   => OrderPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    public function register(): void {}

    public function boot(): void
	{
		if (config('app.env') === 'production') {
        \URL::forceScheme('https');
        }
        // Super Admin bypasse toutes les policies
        Gate::before(function ($user, $ability) {
            if ($user->isSuperAdmin()) return true;
        });

        $this->applySmtpSettings();
    }

    private function applySmtpSettings(): void
    {
        try {
            $host = Setting::get('smtp_host');
            if (blank($host)) return;

            Config::set('mail.default', 'smtp');
            Config::set('mail.mailers.smtp.host',     $host);
            Config::set('mail.mailers.smtp.port',     Setting::get('smtp_port', 587));
            Config::set('mail.mailers.smtp.username', Setting::get('smtp_username'));
            Config::set('mail.mailers.smtp.password', Setting::get('smtp_password'));

            $encryption = Setting::get('smtp_encryption', 'tls');
            $scheme = match ($encryption) {
                'ssl'  => 'smtps',
                'tls'  => 'smtp',
                default => null,
            };
            Config::set('mail.mailers.smtp.scheme', $scheme);

            Config::set('mail.from.address', Setting::get('smtp_from_address') ?: Setting::get('shop_email', 'noreply@alcogest.test'));
            Config::set('mail.from.name',    Setting::get('smtp_from_name')    ?: Setting::get('shop_name', config('app.name')));
        } catch (\Throwable) {
            // Table absente (premières migrations) → on garde la config .env
        }
    }
}
