<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sprint 2 — HU-16: directorio de servicios para mascotas con mapa.
 */
class BusinessController extends Controller
{
    public function index(Request $request): View
    {
        $categories = config('community.directory');
        $category = $request->query('categoria');

        if ($category !== null && ! array_key_exists($category, $categories)) {
            $category = null;
        }

        $businesses = Business::active()->orderBy('name')->get();

        return view('directory.index', [
            'categories' => $categories,
            'selected' => $category,
            'businesses' => $businesses,
            'mapBusinesses' => $businesses->map->toMapArray()->values(),
            'center' => config('pets.default_center'),
        ]);
    }
}
