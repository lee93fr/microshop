<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ImageSearchController extends Controller
{
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:200',
        ]);

        $key = config('services.google_cse.key');
        $cx  = config('services.google_cse.cx');

        if (!$key || !$cx) {
            return response()->json([
                'error' => "Recherche d'image non configurée : ajoute GOOGLE_CSE_API_KEY et GOOGLE_CSE_ID dans .env.",
            ], 503);
        }

        $response = Http::timeout(10)->get('https://www.googleapis.com/customsearch/v1', [
            'key'        => $key,
            'cx'         => $cx,
            'q'          => $request->q,
            'searchType' => 'image',
            'num'        => 10,
            'safe'       => 'active',
            'imgSize'    => 'medium',
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Erreur Google : ' . ($response->json('error.message') ?? ('HTTP ' . $response->status())),
            ], 502);
        }

        $items = collect($response->json('items') ?? [])->map(fn ($item) => [
            'title'     => $item['title'] ?? '',
            'link'      => $item['link'] ?? '',
            'thumbnail' => $item['image']['thumbnailLink'] ?? ($item['link'] ?? ''),
            'width'     => $item['image']['width'] ?? null,
            'height'    => $item['image']['height'] ?? null,
            'context'   => $item['image']['contextLink'] ?? '',
            'mime'      => $item['mime'] ?? '',
        ])->values();

        return response()->json(['items' => $items]);
    }
}
