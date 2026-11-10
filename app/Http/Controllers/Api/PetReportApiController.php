<?php

namespace App\Http\Controllers\Api;

use App\Contracts\ReportMatcher;
use App\Http\Controllers\Controller;
use App\Http\Requests\NearbyReportsRequest;
use App\Http\Requests\SimilarReportsRequest;
use App\Models\PetReport;
use App\Services\Geo\NearbyFinder;
use Illuminate\Http\JsonResponse;

/**
 * Endpoints JSON públicos de los reportes (sin login).
 */
class PetReportApiController extends Controller
{
    public function __construct(
        private readonly NearbyFinder $nearby,
        private readonly ReportMatcher $matcher,
    ) {
    }

    /**
     * HU3: "radar" de mascotas cercanas. El frontend lo consulta con la
     * ubicación del visitante para avisarle si hay casos activos cerca.
     */
    public function nearby(NearbyReportsRequest $request): JsonResponse
    {
        $reports = $this->nearby
            ->within(PetReport::query()->active(), $request->latitude(), $request->longitude(), $request->radiusKm())
            ->map(fn (PetReport $report) => collect($report->toArray())->only([
                'id', 'type', 'species', 'breed', 'color', 'size', 'pet_name', 'status',
                'description', 'location_reference', 'photo_path', 'latitude', 'longitude',
                'created_at', 'distance_km',
            ])->put('photo_url', $report->photo_url));

        return response()->json([
            'count' => $reports->count(),
            'reports' => $reports,
        ]);
    }

    /**
     * Sprint 4 — HU-05: mientras se llena el formulario (antes de publicar)
     * se buscan reportes del tipo contrario que se parezcan.
     */
    public function similar(SimilarReportsRequest $request): JsonResponse
    {
        $reports = $this->matcher->for($request->draftReport())->map(fn (PetReport $report) => [
            'id' => $report->id,
            'url' => $report->public_url,
            'name' => $report->display_name,
            'species' => $report->species_label,
            'emoji' => $report->species_emoji,
            'photo_url' => $report->photo_url,
            'location' => $report->location_reference,
            'distance_km' => round($report->distance_km, 1),
            'score' => $report->match_score,
            'reasons' => $report->match_reasons,
            'published' => $report->created_at->locale('es')->diffForHumans(),
        ]);

        return response()->json(['count' => $reports->count(), 'reports' => $reports]);
    }
}
