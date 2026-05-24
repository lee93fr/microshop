<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Utilisateurs ───────────────────────────────────────
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@alcogest.fr',
            'password' => Hash::make('password'),
            'role'     => 'super_admin',
            'phone'    => '+33600000000',
        ]);

        User::create([
            'name'        => 'Client Test',
            'email'       => 'client@alcogest.fr',
            'password'    => Hash::make('password'),
            'role'        => 'client',
            'phone'       => '+33611111111',
            'address'     => '12 rue de la Paix',
            'city'        => 'Paris',
            'postal_code' => '75001',
            'country'     => 'France',
        ]);

        // ── Catégories ─────────────────────────────────────────
        $categories = [
            'Vins rouges', 'Vins blancs', 'Vins rosés',
            'Champagnes & Pétillants', 'Whisky', 'Rhum',
            'Gin', 'Vodka', 'Cognac & Armagnac', 'Bières',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }

        // ── Produits exemples ──────────────────────────────────
        $rouges = Category::where('name', 'Vins rouges')->first();
        $whisky = Category::where('name', 'Whisky')->first();
        $champ  = Category::where('name', 'Champagnes & Pétillants')->first();

        Product::insert([
            [
                'name'           => 'Bordeaux Saint-Émilion 2020',
                'slug'           => 'bordeaux-saint-emilion-2020',
                'description'    => 'Un grand vin rouge aux tanins fondus, notes de fruits noirs et de réglisse.',
                'category_id'    => $rouges->id,
                'purchase_price' => 12.50,
                'sale_price'     => 22.00,
                'unit'           => 'bouteille',
                'volume_ml'      => 750,
                'alcohol_degree' => 13.50,
                'sku'            => 'BDX-SE-2020',
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'name'           => 'Whisky Jameson Original',
                'slug'           => 'whisky-jameson-original',
                'description'    => 'Le whisky irlandais le plus vendu au monde. Doux et accessible.',
                'category_id'    => $whisky->id,
                'purchase_price' => 18.00,
                'sale_price'     => 32.00,
                'unit'           => 'bouteille',
                'volume_ml'      => 700,
                'alcohol_degree' => 40.00,
                'sku'            => 'WSK-JAM-ORI',
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'name'           => 'Champagne Brut Sans Année',
                'slug'           => 'champagne-brut-sans-annee',
                'description'    => 'Champagne frais et élégant, parfait pour les célébrations.',
                'category_id'    => $champ->id,
                'purchase_price' => 22.00,
                'sale_price'     => 38.00,
                'unit'           => 'bouteille',
                'volume_ml'      => 750,
                'alcohol_degree' => 12.00,
                'sku'            => 'CHP-BRUT-NV',
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ]);

        // ── Paramètres ─────────────────────────────────────────
        $settings = [
            ['key' => 'shop_name',          'value' => 'AlcoGest',               'group' => 'general',  'is_secret' => false],
            ['key' => 'shop_email',          'value' => 'contact@alcogest.fr',    'group' => 'general',  'is_secret' => false],
            ['key' => 'supplier_name',       'value' => 'Mon Fournisseur',        'group' => 'supplier', 'is_secret' => false],
            ['key' => 'supplier_email',      'value' => 'fournisseur@example.com','group' => 'supplier', 'is_secret' => false],
            ['key' => 'supplier_phone',      'value' => '+33600000000',           'group' => 'supplier', 'is_secret' => false],
            ['key' => 'rib_account_owner',   'value' => 'AlcoGest SARL',          'group' => 'payment',  'is_secret' => false],
            ['key' => 'rib_bank_name',       'value' => 'Ma Banque',              'group' => 'payment',  'is_secret' => false],
            ['key' => 'rib_iban',            'value' => 'FR76 0000 0000 0000 0000 0000 000','group' => 'payment', 'is_secret' => false],
            ['key' => 'rib_bic',             'value' => 'XXXXXXXX',               'group' => 'payment',  'is_secret' => false],
            ['key' => 'stripe_key',          'value' => '',                       'group' => 'payment',  'is_secret' => false],
            ['key' => 'stripe_secret',       'value' => '',                       'group' => 'payment',  'is_secret' => true],
            ['key' => 'stripe_webhook_secret','value' => '',                      'group' => 'payment',  'is_secret' => true],
            ['key' => 'sms_provider',        'value' => 'twilio',                 'group' => 'sms',      'is_secret' => false],
            ['key' => 'sms_from',            'value' => '',                       'group' => 'sms',      'is_secret' => false],
            ['key' => 'sms_api_key',         'value' => '',                       'group' => 'sms',      'is_secret' => true],
        ];

        foreach ($settings as $s) {
            Setting::create(array_merge($s, ['created_at' => now(), 'updated_at' => now()]));
        }
    }
}
