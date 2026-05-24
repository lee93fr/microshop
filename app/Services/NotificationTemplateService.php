<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;

class NotificationTemplateService
{
    // Définition de toutes les notifications éditables
    public static function definitions(): array
    {
        return [
            'order_confirmation' => [
                'label' => 'Confirmation de commande',
                'icon'  => '📧',
                'desc'  => 'Envoyée au client dès qu\'une commande est passée.',
                'vars'  => ['client_name', 'order_reference', 'order_total', 'order_date', 'order_url', 'order_items', 'payment_method', 'delivery_mode', 'shop_name'],
            ],
            'admin_new_order' => [
                'label' => 'Nouvelle commande (admin)',
                'icon'  => '🛒',
                'desc'  => 'Alerte envoyée à l\'admin à chaque nouvelle commande.',
                'vars'  => ['client_name', 'client_email', 'order_reference', 'order_total', 'order_date', 'order_url', 'order_items', 'payment_method', 'delivery_mode', 'shop_name'],
            ],
            'status_update' => [
                'label' => 'Mise à jour de statut',
                'icon'  => '🔄',
                'desc'  => 'Envoyée au client quand l\'admin change le statut de sa commande.',
                'vars'  => ['client_name', 'order_reference', 'order_total', 'order_status', 'order_url', 'payment_status', 'shop_name'],
            ],
            'payment_received' => [
                'label' => 'Paiement reçu',
                'icon'  => '✅',
                'desc'  => 'Envoyée au client quand son paiement est confirmé.',
                'vars'  => ['client_name', 'order_reference', 'order_total', 'payment_method', 'order_url', 'shop_name'],
            ],
            'order_cancelled' => [
                'label' => 'Annulation de commande',
                'icon'  => '❌',
                'desc'  => 'Envoyée au client quand une commande est annulée.',
                'vars'  => ['client_name', 'order_reference', 'order_total', 'order_url', 'shop_name'],
            ],
        ];
    }

    public static function defaultSubject(string $key): string
    {
        return match ($key) {
            'order_confirmation' => 'Confirmation de votre commande {{order_reference}} — {{shop_name}}',
            'admin_new_order'    => '🛒 Nouvelle commande {{order_reference}} — {{shop_name}}',
            'status_update'      => 'Mise à jour de votre commande {{order_reference}} — {{shop_name}}',
            'payment_received'   => '✅ Paiement confirmé — Commande {{order_reference}} — {{shop_name}}',
            'order_cancelled'    => 'Annulation de votre commande {{order_reference}} — {{shop_name}}',
            default              => '{{shop_name}}',
        };
    }

    public static function defaultBody(string $key): string
    {
        return match ($key) {
            'order_confirmation' => <<<HTML
<p>Bonjour <strong>{{client_name}}</strong>,</p>
<p>Votre commande <strong>{{order_reference}}</strong> du {{order_date}} a bien été enregistrée. Merci pour votre confiance !</p>
<p><strong>Mode de livraison :</strong> {{delivery_mode}}<br>
<strong>Mode de paiement :</strong> {{payment_method}}<br>
<strong>Total :</strong> {{order_total}}</p>
{{order_items}}
<p style="text-align:center;margin-top:24px;">
  <a href="{{order_url}}" style="display:inline-block;padding:12px 28px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;font-weight:700;">Suivre ma commande</a>
</p>
HTML,
            'admin_new_order' => <<<HTML
<p>Une nouvelle commande vient d'être passée sur la boutique.</p>
<p><strong>Référence :</strong> {{order_reference}}<br>
<strong>Client :</strong> {{client_name}} ({{client_email}})<br>
<strong>Date :</strong> {{order_date}}<br>
<strong>Total :</strong> {{order_total}}<br>
<strong>Livraison :</strong> {{delivery_mode}}<br>
<strong>Paiement :</strong> {{payment_method}}</p>
{{order_items}}
<p style="text-align:center;margin-top:24px;">
  <a href="{{order_url}}" style="display:inline-block;padding:12px 28px;background:#1e293b;color:white;text-decoration:none;border-radius:8px;font-weight:700;">Voir la commande →</a>
</p>
HTML,
            'status_update' => <<<HTML
<p>Bonjour <strong>{{client_name}}</strong>,</p>
<p>Le statut de votre commande <strong>{{order_reference}}</strong> a été mis à jour :</p>
<p style="text-align:center;">
  <span style="display:inline-block;padding:8px 20px;border-radius:20px;background:#e0e7ff;color:#3730a3;font-weight:700;font-size:15px;">{{order_status}}</span>
</p>
<p><strong>Total :</strong> {{order_total}}<br>
<strong>Paiement :</strong> {{payment_status}}</p>
<p style="text-align:center;margin-top:24px;">
  <a href="{{order_url}}" style="display:inline-block;padding:12px 28px;background:#1e293b;color:white;text-decoration:none;border-radius:8px;font-weight:700;">Voir ma commande</a>
</p>
HTML,
            'payment_received' => <<<HTML
<p>Bonjour <strong>{{client_name}}</strong>,</p>
<p>Votre paiement pour la commande <strong>{{order_reference}}</strong> a bien été reçu et validé.</p>
<p><strong>Montant :</strong> {{order_total}}<br>
<strong>Mode de paiement :</strong> {{payment_method}}</p>
<p style="text-align:center;margin-top:24px;">
  <a href="{{order_url}}" style="display:inline-block;padding:12px 28px;background:#059669;color:white;text-decoration:none;border-radius:8px;font-weight:700;">Voir ma commande</a>
</p>
HTML,
            'order_cancelled' => <<<HTML
<p>Bonjour <strong>{{client_name}}</strong>,</p>
<p>Votre commande <strong>{{order_reference}}</strong> a bien été annulée.</p>
<p><strong>Montant :</strong> {{order_total}}</p>
<p style="text-align:center;margin-top:24px;">
  <a href="{{order_url}}" style="display:inline-block;padding:12px 28px;background:#4f46e5;color:white;text-decoration:none;border-radius:8px;font-weight:700;">Retourner au catalogue</a>
</p>
HTML,
            default => '',
        };
    }

    public function render(string $key, Order $order): array
    {
        $subject = Setting::get("notif_tpl_{$key}_subject") ?: static::defaultSubject($key);
        $body    = Setting::get("notif_tpl_{$key}_body")    ?: static::defaultBody($key);

        $vars = $this->buildVars($key, $order);

        return [
            'subject'      => $this->replace($subject, $vars),
            'body'         => $this->replace($body, $vars),
            'header_color' => $this->headerColor($key),
            'shop_name'    => $vars['shop_name'],
        ];
    }

    private function buildVars(string $key, Order $order): array
    {
        $shopName = Setting::get('shop_name', 'La Tournée!');

        $itemsHtml = '<table style="width:100%;border-collapse:collapse;font-size:13px;margin:16px 0;">'
            . '<tr style="background:#f1f5f9;"><th style="text-align:left;padding:8px;">Produit</th><th style="text-align:center;padding:8px;">Qté</th><th style="text-align:right;padding:8px;">Total</th></tr>';
        foreach ($order->items as $item) {
            $itemsHtml .= '<tr style="border-bottom:1px solid #f1f5f9;">'
                . '<td style="padding:8px;">' . e($item->product->name ?? 'Produit') . '</td>'
                . '<td style="text-align:center;padding:8px;">' . $item->quantity . '</td>'
                . '<td style="text-align:right;padding:8px;font-weight:600;">' . number_format($item->line_total, 2, ',', ' ') . ' €</td>'
                . '</tr>';
        }
        $itemsHtml .= '<tr><td colspan="2" style="text-align:right;padding:8px;font-weight:700;">Total</td>'
            . '<td style="text-align:right;padding:8px;font-weight:700;font-size:15px;">' . number_format($order->total, 2, ',', ' ') . ' €</td></tr>'
            . '</table>';

        return [
            'client_name'    => $order->user->name ?? '',
            'client_email'   => $order->user->email ?? '',
            'order_reference'=> $order->reference,
            'order_total'    => number_format($order->total, 2, ',', ' ') . ' €',
            'order_date'     => $order->created_at->format('d/m/Y à H:i'),
            'order_url'      => route('client.orders.show', $order),
            'order_status'   => $order->status_label ?? $order->status,
            'payment_status' => $order->payment_status_label ?? $order->payment_status,
            'payment_method' => $order->payment_method,
            'delivery_mode'  => $order->delivery_mode === 'pickup' ? 'Retrait sur place' : 'Livraison à domicile',
            'order_items'    => $itemsHtml,
            'shop_name'      => $shopName,
        ];
    }

    private function replace(string $template, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $template = str_replace('{{' . $key . '}}', $value, $template);
        }
        return $template;
    }

    private function headerColor(string $key): string
    {
        return match ($key) {
            'order_confirmation' => '#4f46e5',
            'admin_new_order'    => '#1e293b',
            'status_update'      => '#1e293b',
            'payment_received'   => '#059669',
            'order_cancelled'    => '#dc2626',
            default              => '#4f46e5',
        };
    }
}
