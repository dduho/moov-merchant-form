<?php

namespace App\Services;

use App\Models\PointOfSale;
use App\Models\SystemSetting;
use Illuminate\Support\Collection;

class ProximityAlertService
{
    /**
     * Earth's radius in meters (used for distance calculations).
     */
    public const EARTH_RADIUS_METERS = 6371000;

    /**
     * Check for nearby PDVs within the configured distance.
     *
     * @param float $latitude
     * @param float $longitude
     * @param int|null $excludePdvId PDV ID to exclude from results
     * @return array
     */
    public function checkProximity(float $latitude, float $longitude, ?int $excludePdvId = null): array
    {
        $alertDistance = SystemSetting::getProximityAlertDistance();
        
        $nearbyPdvs = $this->findNearbyPdvs($latitude, $longitude, $alertDistance, $excludePdvId);
        
        return [
            'has_nearby' => $nearbyPdvs->isNotEmpty(),
            'alert_distance' => $alertDistance,
            'nearby_count' => $nearbyPdvs->count(),
            'nearby_pdvs' => $nearbyPdvs->map(function ($pdv) use ($latitude, $longitude) {
                return [
                    'id' => $pdv->id,
                    'reference_number' => $pdv->reference_number,
                    'nom_point' => $pdv->nom_point,
                    'dealer_name' => $pdv->dealer_name,
                    'distance' => round($this->calculateDistance(
                        $latitude, $longitude,
                        $pdv->latitude, $pdv->longitude
                    ), 2),
                    'latitude' => $pdv->latitude,
                    'longitude' => $pdv->longitude,
                ];
            })->values()->toArray(),
        ];
    }

    /**
     * Find PDVs within a given distance of a point.
     *
     * @param float $latitude
     * @param float $longitude
     * @param float $distanceMeters
     * @param int|null $excludePdvId
     * @return Collection
     */
    protected function findNearbyPdvs(float $latitude, float $longitude, float $distanceMeters, ?int $excludePdvId = null): Collection
    {
        // Get all validated PDVs
        $query = PointOfSale::validated();
        
        if ($excludePdvId) {
            $query->where('id', '!=', $excludePdvId);
        }
        
        $pdvs = $query->get();
        
        // Filter by distance
        return $pdvs->filter(function ($pdv) use ($latitude, $longitude, $distanceMeters) {
            $distance = $this->calculateDistance(
                $latitude, $longitude,
                $pdv->latitude, $pdv->longitude
            );
            return $distance <= $distanceMeters;
        });
    }

    /**
     * Calculate distance between two points using Haversine formula.
     *
     * @param float $lat1
     * @param float $lon1
     * @param float $lat2
     * @param float $lon2
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos($latFrom) * cos($latTo) *
             sin($lonDelta / 2) * sin($lonDelta / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Get all PDVs with their proximity status.
     *
     * @return Collection
     */
    public function getAllPdvsWithProximity(): Collection
    {
        $pdvs = PointOfSale::validated()->get();
        $alertDistance = SystemSetting::getProximityAlertDistance();

        return $pdvs->map(function ($pdv) use ($pdvs, $alertDistance) {
            $nearbyCount = $pdvs->filter(function ($otherPdv) use ($pdv, $alertDistance) {
                if ($otherPdv->id === $pdv->id) {
                    return false;
                }
                $distance = $this->calculateDistance(
                    $pdv->latitude, $pdv->longitude,
                    $otherPdv->latitude, $otherPdv->longitude
                );
                return $distance <= $alertDistance;
            })->count();

            return [
                'pdv' => $pdv,
                'nearby_count' => $nearbyCount,
                'has_proximity_alert' => $nearbyCount > 0,
            ];
        });
    }
}
