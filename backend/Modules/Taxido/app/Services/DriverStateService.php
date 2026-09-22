<?php

namespace Modules\Taxido\Services;

use Illuminate\Support\Facades\Redis;
use Modules\Taxido\Broadcasts\FleetDriversLocationBroadcast;
use Modules\Taxido\Models\Driver;
use Illuminate\Support\Facades\Log;


class DriverStateService
{
    private const KEY_ONLINE_DRIVERS = 'driver_locations';
    private const KEY_DRIVER_METADATA = 'driver_metadata:';
    private const KEY_FLEET_DRIVERS = 'fleet_drivers:';


    public function updateDriverLocation(int $driverId, float $lat, float $lng, array $metadata = []): void
    {
        Log::info('insde  driver state service');
        Redis::geoadd(self::KEY_ONLINE_DRIVERS, $lng, $lat, (string) $driverId);
        Redis::set(self::KEY_DRIVER_METADATA . $driverId, json_encode($metadata));
        Redis::expire(self::KEY_DRIVER_METADATA . $driverId, 600);
        if (isset($metadata['fleet_manager_id']) && $metadata['fleet_manager_id'] > 0) {
            Redis::sadd(self::KEY_FLEET_DRIVERS . $metadata['fleet_manager_id'], (string) $driverId);
            Redis::expire(self::KEY_FLEET_DRIVERS . $metadata['fleet_manager_id'], 3600);
            $this->broadcastFleetUpdate((int) $metadata['fleet_manager_id']);
        }

    }

    /**
     * Get all drivers of a fleet with their latest metadata and location.
     */
    public function getFleetDriversData(int $fleetManagerId): array
    {
        $driverIds = Redis::smembers(self::KEY_FLEET_DRIVERS . $fleetManagerId);
        $relationships = [
            'profile_image',
            'vehicle_info.vehicle.vehicle_map_icon',
            'ambulance',
            'zones',
            'reviews',
            'onRides' => function($q) {
                $q->with([
                    'rider.profile_image',
                    'service',
                    'service_category',
                    'vehicle_type.vehicle_image',
                    'ride_status'
                ]);
            }
        ];

        if (empty($driverIds)) {
            return Driver::where('fleet_manager_id', $fleetManagerId)
                ->where('status', true)
                ->with($relationships)
                ->get()
                ->map(fn($d) => $this->formatDriverForFleet($d))
                ->toArray();
        }

        $fleetDrivers = [];
        foreach ($driverIds as $driverId) {
            $metadataStr = Redis::get(self::KEY_DRIVER_METADATA . $driverId);
            if (!$metadataStr) {
                $driver = Driver::with($relationships)->find($driverId);
                if ($driver && $driver->is_online) {
                    $fleetDrivers[] = $this->formatDriverForFleet($driver);
                } else {
                    Redis::srem(self::KEY_FLEET_DRIVERS . $fleetManagerId, $driverId);
                }
                continue;
            }

            $metadata = json_decode($metadataStr, true);
            $pos = Redis::geopos(self::KEY_ONLINE_DRIVERS, $driverId);
            if ($pos && isset($pos[0])) {
                $metadata['lat'] = (float) $pos[0][1];
                $metadata['lng'] = (float) $pos[0][0];
            } else {
                $driver = Driver::find($driverId);
                $location = $driver->location[0] ?? null;
                $metadata['lat'] = (float) ($location['lat'] ?? 0);
                $metadata['lng'] = (float) ($location['lng'] ?? 0);
            }

            $fleetDrivers[] = $metadata;
        }

        return $fleetDrivers;
    }

    /**
     * Helper to format driver model for fleet broadcasting.
     */
    public function formatDriverForFleet($driver): array
    {
        $location = $driver->location[0] ?? null;
        $activeRide = $driver->onRides?->first();

        $data = [
            'id' => (int) $driver->id,
            'driver_id' => (string) $driver->id,
            'driver_name' => $driver->name,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'country_code' => $driver->country_code,
            'profile_image_url' => $driver->profile_image?->original_url,
            'is_online' => $driver->is_online ? "1" : "0",
            'is_on_ride' => $driver->is_on_ride ? "1" : "0",
            'is_verified' => $driver->is_verified ? 1 : 0,
            'status' => $driver->status ? "1" : "0",
            'fleet_manager_id' => (string) $driver->fleet_manager_id,
            'lat' => (float) ($location['lat'] ?? 0),
            'lng' => (float) ($location['lng'] ?? 0),
            'bearing' => (float) ($location['bearing'] ?? 0),
            'model' => $driver->vehicle_info?->model,
            'vehicle_name' => $driver->ambulance?->name ?? $driver->vehicle_info?->vehicle?->name,
            'vehicle_map_icon_url' => $driver->vehicle_info?->vehicle?->vehicle_map_icon?->original_url ?? '',
            'vehicle_type_id' => $driver->vehicle_info?->vehicle?->id ?? 0,
            'zone_id' => $driver->zones?->first()?->id ?? '',
            'rating' => (float) $driver->reviews?->avg('rating'),
            'rating_count' => (int) $driver->reviews?->count(),
            'plate_number' => $driver->vehicle_info?->plate_number,
            'color' => $driver->vehicle_info?->color,
            'seat' => $driver->vehicle_info?->seat,
            'updated_at' => $driver->updated_at?->toDateTimeString(),
        ];

        if ($driver->is_on_ride && $activeRide) {
            $data['ride_number'] = $activeRide->ride_number;
            $data['ride_id'] = $activeRide->id;
            $data['rider_name'] = $activeRide->rider?->name;
            $data['rider_email'] = $activeRide->rider?->email;
            $data['rider_image'] = $activeRide->rider?->profile_image?->original_url;
            $data['service_name'] = $activeRide->service?->name;
            $data['service_category_name'] = $activeRide->service_category?->name;
            $data['vehicle_image'] = $activeRide->vehicle_type?->vehicle_image?->original_url;
            $data['payment_status'] = $activeRide->payment_status;
            $data['payment_method'] = $activeRide->payment_method;
            $data['distance'] = $activeRide->distance;
            $data['distance_unit'] = $activeRide->distance_unit;
            $data['ride_status'] = $activeRide->ride_status?->name;
        }

        return $data;
    }

    public function broadcastFleetUpdate(int $fleetManagerId): void
    {
        $fleetData = $this->getFleetDriversData($fleetManagerId);
        broadcast(new FleetDriversLocationBroadcast($fleetManagerId, $fleetData));
    }

    public function setDriverOffline(int $driverId): void
    {
        $metadataStr = Redis::get(self::KEY_DRIVER_METADATA . $driverId);
        $fleetManagerId = null;

        if ($metadataStr) {
            $metadata = json_decode($metadataStr, true);
            $fleetManagerId = $metadata['fleet_manager_id'] ?? null;
        }

        Redis::zrem(self::KEY_ONLINE_DRIVERS, (string) $driverId);
        Redis::del(self::KEY_DRIVER_METADATA . $driverId);

        if ($fleetManagerId) {
            Redis::srem(self::KEY_FLEET_DRIVERS . $fleetManagerId, (string) $driverId);
            $this->broadcastFleetUpdate((int) $fleetManagerId);
        }

    }

    public function findNearestDrivers(float $lat, float $lng, float $radiusKm = 10, array $filters = []): array
    {
        $driverIds = Redis::georadius(self::KEY_ONLINE_DRIVERS, $lng, $lat, $radiusKm, 'km', ['ASC']);
        if (empty($driverIds)) {
            return [];
        }

        $filteredDrivers = [];
        foreach ($driverIds as $driverId) {
            $metadataStr = Redis::get(self::KEY_DRIVER_METADATA . $driverId);
            if (!$metadataStr)
                continue;
            $metadata = json_decode($metadataStr, true);
            if (!$metadata)
                continue;

            $match = true;
            foreach ($filters as $key => $value) {
                if ($key === 'id' && is_array($value)) {
                    if (!in_array($driverId, $value)) {
                        $match = false;
                        break;
                    }
                } elseif (($metadata[$key] ?? null) != $value) {
                    $match = false;
                    break;
                }
            }

            if ($match) {
                $filteredDrivers[] = array_merge($metadata, ['id' => $driverId]);
            }
        }

        return $filteredDrivers;
    }
}
