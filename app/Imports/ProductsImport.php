<?php

namespace App\Imports;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

class ProductsImport implements OnEachRow, WithHeadingRow, WithValidation
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $category = Category::firstOrCreate(
            ['slug' => Str::slug($data['categorie'] ?? 'autres')],
            ['name' => $data['categorie'] ?? 'Autres'],
        );

        $fields = [
            'name'            => $data['nom'],
            'description'     => $data['description'] ?? null,
            'category_id'     => $category->id,
            'purchase_price'  => (float) str_replace(',', '.', $data['prix_achat'] ?? 0),
            'sale_price'      => (float) str_replace(',', '.', $data['prix_vente'] ?? 0),
            'suggested_price' => !empty($data['prix_conseille']) ? (float) str_replace(',', '.', $data['prix_conseille']) : null,
            'unit'            => $data['unite'] ?? 'bouteille',
            'volume_ml'       => !empty($data['volume_ml']) ? (int) $data['volume_ml'] : null,
            'alcohol_degree'  => !empty($data['degre'])     ? (float) $data['degre']   : null,
            'is_active'       => true,
        ];

        // Priorité SKU pour l'identification, puis nom
        if (!empty($data['sku'])) {
            $fields['sku'] = $data['sku'];
            Product::updateOrCreate(
                ['sku' => $data['sku']],
                array_merge($fields, ['slug' => Str::slug($data['nom'])]),
            );
        } else {
            Product::updateOrCreate(
                ['name' => $data['nom']],
                array_merge($fields, ['slug' => Str::slug($data['nom'])]),
            );
        }
    }

    public function rules(): array
    {
        return [
            'nom'        => 'required|string',
            'prix_vente' => 'required|numeric',
        ];
    }
}
