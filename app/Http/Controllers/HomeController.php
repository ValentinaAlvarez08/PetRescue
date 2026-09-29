<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\PetReport;
use Illuminate\View\View;

/**
 * Sprint 2 — HU-15: página principal de la comunidad.
 */
class HomeController extends Controller
{
    public function index(): View
    {
        $countsByCategory = Business::active()
            ->selectRaw('category, count(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('home', [
            'latestReports' => PetReport::active()->latest()->take(4)->get(),
            'stats' => [
                'active' => PetReport::active()->count(),
                'reunited' => PetReport::where('status', 'reunido')->count(),
                'businesses' => Business::active()->count(),
            ],
            'directory' => config('community.directory'),
            'countsByCategory' => $countsByCategory,
            'topics' => config('community.topics'),
        ]);
    }
}
