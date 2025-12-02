<?php

namespace App\Services;

use App\Models\PointOfSale;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Get global statistics.
     *
     * @return array
     */
    public function getGlobalStats(): array
    {
        return [
            'total_pdv' => PointOfSale::count(),
            'validated_pdv' => PointOfSale::validated()->count(),
            'pending_pdv' => PointOfSale::pending()->count(),
            'rejected_pdv' => PointOfSale::rejected()->count(),
            'total_organizations' => Organization::count(),
            'active_organizations' => Organization::active()->count(),
        ];
    }

    /**
     * Get statistics by region.
     *
     * @return Collection
     */
    public function getStatsByRegion(): Collection
    {
        return PointOfSale::select('region', DB::raw('count(*) as total'))
            ->selectRaw("SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get statistics by prefecture for a given region.
     *
     * @param string $region
     * @return Collection
     */
    public function getStatsByPrefecture(string $region): Collection
    {
        return PointOfSale::select('prefecture', DB::raw('count(*) as total'))
            ->selectRaw("SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->where('region', $region)
            ->groupBy('prefecture')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get statistics by organization (dealer).
     *
     * @return Collection
     */
    public function getStatsByOrganization(): Collection
    {
        return Organization::select('organizations.*')
            ->selectRaw('(SELECT COUNT(*) FROM point_of_sales WHERE organization_id = organizations.id) as total_pdv')
            ->selectRaw("(SELECT COUNT(*) FROM point_of_sales WHERE organization_id = organizations.id AND status = 'validated') as validated_pdv")
            ->selectRaw("(SELECT COUNT(*) FROM point_of_sales WHERE organization_id = organizations.id AND status = 'pending') as pending_pdv")
            ->orderBy('total_pdv', 'desc')
            ->get();
    }

    /**
     * Get statistics for a specific organization.
     *
     * @param int $organizationId
     * @return array
     */
    public function getOrganizationStats(int $organizationId): array
    {
        $pdvs = PointOfSale::where('organization_id', $organizationId);
        
        return [
            'total_pdv' => $pdvs->count(),
            'validated_pdv' => (clone $pdvs)->validated()->count(),
            'pending_pdv' => (clone $pdvs)->pending()->count(),
            'rejected_pdv' => (clone $pdvs)->rejected()->count(),
            'by_region' => $this->getOrganizationStatsByRegion($organizationId),
        ];
    }

    /**
     * Get organization statistics by region.
     *
     * @param int $organizationId
     * @return Collection
     */
    protected function getOrganizationStatsByRegion(int $organizationId): Collection
    {
        return PointOfSale::select('region', DB::raw('count(*) as total'))
            ->where('organization_id', $organizationId)
            ->groupBy('region')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get trend data for the last N days.
     *
     * @param int $days
     * @return Collection
     */
    public function getTrends(int $days = 30): Collection
    {
        return PointOfSale::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as total'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    /**
     * Get commercial performance statistics.
     *
     * @param int|null $organizationId Filter by organization
     * @return Collection
     */
    public function getCommercialStats(?int $organizationId = null): Collection
    {
        $query = User::select('users.*')
            ->selectRaw('(SELECT COUNT(*) FROM point_of_sales WHERE created_by = users.id) as total_pdv')
            ->selectRaw("(SELECT COUNT(*) FROM point_of_sales WHERE created_by = users.id AND status = 'validated') as validated_pdv")
            ->selectRaw("(SELECT COUNT(*) FROM point_of_sales WHERE created_by = users.id AND status = 'pending') as pending_pdv")
            ->whereHas('roles', function ($q) {
                $q->where('slug', 'commercial');
            });

        if ($organizationId) {
            $query->where('organization_id', $organizationId);
        }

        return $query->orderBy('total_pdv', 'desc')->get();
    }

    /**
     * Get heat map data for PDV locations.
     *
     * @return Collection
     */
    public function getHeatMapData(): Collection
    {
        return PointOfSale::validated()
            ->select('latitude', 'longitude', 'nom_point', 'dealer_name', 'region')
            ->get()
            ->map(function ($pdv) {
                return [
                    'lat' => $pdv->latitude,
                    'lng' => $pdv->longitude,
                    'name' => $pdv->nom_point,
                    'dealer' => $pdv->dealer_name,
                    'region' => $pdv->region,
                ];
            });
    }

    /**
     * Get monthly evolution statistics.
     *
     * @param int $months
     * @return Collection
     */
    public function getMonthlyEvolution(int $months = 12): Collection
    {
        return PointOfSale::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('count(*) as total')
        )
            ->selectRaw("SUM(CASE WHEN status = 'validated' THEN 1 ELSE 0 END) as validated")
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy(DB::raw('YEAR(created_at)'), DB::raw('MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();
    }
}
