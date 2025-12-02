<?php

namespace App\Http\Controllers;

use App\Models\PointOfSale;
use App\Services\XmlExportService;
use App\Services\StatisticsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ExportController extends Controller
{
    protected XmlExportService $xmlExportService;
    protected StatisticsService $statisticsService;

    public function __construct(XmlExportService $xmlExportService, StatisticsService $statisticsService)
    {
        $this->xmlExportService = $xmlExportService;
        $this->statisticsService = $statisticsService;
    }

    /**
     * Export all validated PDVs to XML.
     */
    public function exportXml(Request $request): Response
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $xml = $this->xmlExportService->exportToXml();

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="pdv_export_' . now()->format('Y-m-d_His') . '.xml"',
        ]);
    }

    /**
     * Export PDVs by organization to XML.
     */
    public function exportByOrganization(Request $request, int $organizationId): Response
    {
        $user = Auth::user();

        // Check access
        if (!$user->isMoovStaff() && $user->organization_id !== $organizationId) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $xml = $this->xmlExportService->exportByOrganization($organizationId);

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="pdv_organization_' . $organizationId . '_' . now()->format('Y-m-d_His') . '.xml"',
        ]);
    }

    /**
     * Export PDVs by region to XML.
     */
    public function exportByRegion(Request $request, string $region): Response
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        $validRegions = ['MARITIME', 'PLATEAUX', 'CENTRALE', 'KARA', 'SAVANES'];
        if (!in_array(strtoupper($region), $validRegions)) {
            return response()->json(['error' => 'Région invalide'], 422);
        }

        $xml = $this->xmlExportService->exportByRegion(strtoupper($region));

        return response($xml, 200, [
            'Content-Type' => 'application/xml',
            'Content-Disposition' => 'attachment; filename="pdv_' . strtolower($region) . '_' . now()->format('Y-m-d_His') . '.xml"',
        ]);
    }

    /**
     * Export PDVs to CSV.
     */
    public function exportCsv(Request $request): Response
    {
        $user = Auth::user();

        $query = PointOfSale::validated()->with(['organization', 'creator']);

        // Filter for dealers
        if (!$user->isMoovStaff() && $user->organization_id) {
            $query->where('organization_id', $user->organization_id);
        }

        $pdvs = $query->get();

        $csv = $this->generateCsv($pdvs);

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="pdv_export_' . now()->format('Y-m-d_His') . '.csv"',
        ]);
    }

    /**
     * Generate CSV content from PDVs collection.
     */
    protected function generateCsv($pdvs): string
    {
        $headers = [
            'Reference', 'Dealer', 'Nom Point', 'Prenom', 'Nom',
            'Region', 'Prefecture', 'Commune', 'Ville', 'Quartier',
            'Latitude', 'Longitude', 'Tel Proprietaire', 'Status',
            'Date Creation', 'Date Validation'
        ];

        $output = implode(';', $headers) . "\n";

        foreach ($pdvs as $pdv) {
            $row = [
                $pdv->reference_number,
                $pdv->dealer_name,
                $pdv->nom_point,
                $pdv->firstname,
                $pdv->lastname,
                $pdv->region,
                $pdv->prefecture,
                $pdv->commune,
                $pdv->ville,
                $pdv->quartier,
                $pdv->latitude,
                $pdv->longitude,
                $pdv->numero_proprietaire,
                $pdv->status,
                $pdv->created_at?->format('Y-m-d H:i:s'),
                $pdv->validated_at?->format('Y-m-d H:i:s'),
            ];
            $output .= implode(';', array_map(fn($v) => '"' . str_replace('"', '""', $v ?? '') . '"', $row)) . "\n";
        }

        return "\xEF\xBB\xBF" . $output; // UTF-8 BOM for Excel
    }

    /**
     * Get global statistics.
     */
    public function statistics(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            // Return only organization stats for dealers
            if ($user->organization_id) {
                return response()->json([
                    'data' => $this->statisticsService->getOrganizationStats($user->organization_id)
                ]);
            }
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        return response()->json([
            'data' => [
                'global' => $this->statisticsService->getGlobalStats(),
                'by_region' => $this->statisticsService->getStatsByRegion(),
                'by_organization' => $this->statisticsService->getStatsByOrganization(),
                'trends' => $this->statisticsService->getTrends(30),
                'monthly_evolution' => $this->statisticsService->getMonthlyEvolution(12),
            ]
        ]);
    }

    /**
     * Get heat map data.
     */
    public function heatMapData(): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isMoovStaff()) {
            return response()->json(['error' => 'Action non autorisée'], 403);
        }

        return response()->json([
            'data' => $this->statisticsService->getHeatMapData()
        ]);
    }

    /**
     * Get commercial performance stats.
     */
    public function commercialStats(Request $request): JsonResponse
    {
        $user = Auth::user();

        $organizationId = null;
        if (!$user->isMoovStaff()) {
            $organizationId = $user->organization_id;
        } elseif ($request->has('organization_id')) {
            $organizationId = $request->organization_id;
        }

        return response()->json([
            'data' => $this->statisticsService->getCommercialStats($organizationId)
        ]);
    }
}
