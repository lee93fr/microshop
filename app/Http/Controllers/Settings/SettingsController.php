<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    private array $settingKeys = [
        'shop_name', 'shop_email',
        'supplier_name', 'supplier_email', 'supplier_phone',
        'rib_iban', 'rib_bic', 'rib_bank_name', 'rib_account_owner',
        'stripe_key', 'stripe_secret', 'stripe_webhook_secret',
        'sms_provider', 'sms_api_key', 'sms_from',
        'smtp_host', 'smtp_port', 'smtp_encryption', 'smtp_username', 'smtp_from_name', 'smtp_from_address', 'smtp_password',
        'delivery_fee', 'revolut_id', 'paypal_link',
        'payment_stripe_enabled', 'payment_bank_enabled', 'payment_revolut_enabled',
        'payment_paypal_enabled', 'payment_cash_enabled',
        'admin_notification_email',
        'notif_order_confirmation', 'notif_admin_new_order', 'notif_status_update',
        'notif_payment_received', 'notif_order_cancelled',
    ];

    private array $secrets  = ['stripe_secret', 'stripe_webhook_secret', 'sms_api_key', 'smtp_password'];
    private array $booleans = [
        'payment_stripe_enabled', 'payment_bank_enabled', 'payment_revolut_enabled',
        'payment_paypal_enabled', 'payment_cash_enabled',
        'notif_order_confirmation', 'notif_admin_new_order', 'notif_status_update',
        'notif_payment_received', 'notif_order_cancelled',
    ];

    public function edit()
    {
        $settings = [];
        foreach ($this->settingKeys as $key) {
            $settings[$key] = Setting::get($key);
        }
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($this->settingKeys as $key) {
            // Les booléens (checkboxes) ne sont pas soumis quand décochés — forcer à 0
            if (in_array($key, $this->booleans)) {
                Setting::set($key, $request->has($key) ? '1' : '0');
                continue;
            }
            if (!$request->has($key)) continue;
            // Ne pas écraser un secret vide
            if (in_array($key, $this->secrets) && blank($request->$key)) continue;
            Setting::set($key, $request->$key);
        }

        return back()->with('success', 'Paramètres sauvegardés.');
    }

    public function testSmtp(Request $request)
    {
        $to = $request->user()->email;

        try {
            Mail::raw(
                'Ceci est un email de test envoyé depuis AlcoGest. La configuration SMTP fonctionne correctement.',
                function ($message) use ($to) {
                    $message->to($to)->subject('✅ Test SMTP AlcoGest');
                }
            );

            return response()->json([
                'success' => true,
                'message' => "Email envoyé à {$to}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
