<?php

namespace Modules\Taxido\Tables;

use App\Models\Currency;
use Illuminate\Http\Request;
use Modules\Taxido\Models\Ride;

use Modules\Taxido\Enums\RideStatusEnum;
use Modules\Taxido\Enums\RoleEnum;
use Modules\Taxido\Enums\ServicesEnum;


class RideTable
{
    protected $ride;
    protected $request;

    protected $sortableColumns = [
        'total',
        'rider.name',
        'created_at',
        'ride_number',
        'driver.name',
        'ride_status',
        'service.name',
        'payment_status',
        'service_category.name',
    ];

    public function __construct(Request $request)
    {
        $this->ride = Ride::query();
        $this->request = $request;
    }

    public function getRides($applyRoleFilter = true)
    {
        $rides = $this->ride->newQuery();
        
        if ($this->request->has('driver') && !in_array('all', (array)$this->request->driver)) {
            $rides->whereIn('rides.driver_id', (array)$this->request->driver);
        }

        if ($this->request->has('user') && !in_array('all', (array)$this->request->user)) {
            $rides->whereIn('rides.rider_id', (array)$this->request->user);
        }

        if ($this->request->has('ride_status') && !in_array('all', (array)$this->request->ride_status)) {
            $rides->whereIn('rides.ride_status_id', (array)$this->request->ride_status);
        }

        if ($this->request->has('payment_method') && !in_array('all', (array)$this->request->payment_method)) {
            $rides->whereIn('rides.payment_method', (array)$this->request->payment_method);
        }

        if ($this->request->has('payment_status') && !in_array('all', (array)$this->request->payment_status)) {
            $rides->whereIn('rides.payment_status', (array)$this->request->payment_status);
        }

        if ($this->request->has('service') && !in_array('all', (array)$this->request->service)) {
            $rides->whereIn('rides.service_id', (array)$this->request->service);
        }

        if ($this->request->has('service_category') && !in_array('all', (array)$this->request->service_category)) {
            $rides->whereIn('rides.service_category_id', (array)$this->request->service_category);
        }

        if ($this->request->has('vehicle_type') && !in_array('all', (array)$this->request->vehicle_type)) {
            $rides->whereIn('rides.vehicle_type_id', (array)$this->request->vehicle_type);
        }

        $roleName = getCurrentRoleName();

        if ($applyRoleFilter) {
            if ($roleName === RoleEnum::DRIVER) {
                $rides->where('rides.driver_id', getCurrentUserId());
            } elseif ($roleName === RoleEnum::FLEET_MANAGER) {
                $fleetManagerId = getCurrentUserId();
                $rides->whereHas('driver', fn($q) => $q->where('fleet_manager_id', $fleetManagerId));
            } elseif ($roleName === RoleEnum::DISPATCHER) {
                $rides->whereHas('zones', fn($q) =>
                    $q->whereHas('dispatchers', fn($q2) =>
                        $q2->where('dispatcher_id', getCurrentUserId())
                    )
                );
            }
        }

        return $rides;
    }

    public function getData()
    {
        $rides = $this->getRides();

        $rides->whereNull('rides.deleted_at');

        if ($this->request?->status) {
            $rides = $rides->where('rides.ride_status_id', getRideStatusIdByName($this->request->status));
        }

        if ($this->request->has('filter') && $this->request->get('filter') !== 'all') {
            $rides->where('rides.service_id', getServiceIdBySlug($this->request->filter));
        }

        if ($this->request->has('s')) {
            $search = $this->request->s;

            $rides->withTrashed()
                ->leftJoin('users as rider_users', 'rides.rider_id', '=', 'rider_users.id')
                ->leftJoin('users as driver_users', 'rides.driver_id', '=', 'driver_users.id')
                ->leftJoin('services', 'rides.service_id', '=', 'services.id')
                ->leftJoin('service_categories', 'rides.service_category_id', '=', 'service_categories.id')
                ->select('rides.*')
                ->where(function ($q) use ($search) {
                    $q->where('rides.ride_number', 'LIKE', "%{$search}%")
                      ->orWhere('rides.total', 'LIKE', "%{$search}%")
                      ->orWhere('rider_users.name', 'LIKE', "%{$search}%")
                      ->orWhere('driver_users.name', 'LIKE', "%{$search}%")
                      ->orWhere('services.name', 'LIKE', "%{$search}%")
                      ->orWhere('service_categories.name', 'LIKE', "%{$search}%");
                });
        }

        $rides = $this->sorting($rides);
        
        if ($this->request->export) {
            return $rides->get();
        }

        return $rides->paginate($this->request->paginate ?? 15);
    }

    public function getRideCountByStatus($status)
    {
        return $this->getRides()
            ->where('rides.ride_status_id', getRideStatusIdByName($status))
            ->whereNull('rides.deleted_at')
            ->count();
    }

    public function generate()
    {
        $rides = $this->getData();
        $currencyCode   = session('currency', getDefaultCurrencyCode());
        $currencySymbol = Currency::where('code', $currencyCode)->value('symbol') ?? getDefaultCurrencySymbol();

        $rides->each(function ($ride) use ($currencySymbol, $currencyCode) {
            $convertedTotal        = currencyConvert($currencyCode, $ride->total);
            $ride->formatted_total = ($ride->currency_symbol ?? $currencySymbol) . ((float) $convertedTotal);
            $ride->date            = formatDateBySetting($ride->created_at);
            $ride->rider_name      = $ride->rider['name'] ?? null;
            $ride->rider_email     = isDemoModeEnabled() ? __('taxido::static.demo_mode') : ($ride->rider['email'] ?? null);
            $ride->rider_profile   = $ride->rider['profile_image_id'] ?? null;
            $ride->driver_name     = $ride->driver?->name ?? 'N/A';
            $ride->driver_email    = isDemoModeEnabled() ? __('taxido::static.demo_mode') : ($ride->driver?->email ?? null);
            $ride->driver_profile  = $ride->driver?->profile_image_id ?? 'N/A';
            $ride->service         = $ride->service?->name;
            $ride->ride_numb       = "#{$ride->ride_number}";
            $ride->service_category = $ride->service_category?->name ?? 'N/A';

            // Translated labels – keyed correctly for the locale-aware colorClasses maps below
            $ride->status         = $this->getTranslatedStatus($ride?->ride_status?->name);
            $ride->payment_status = $this->getTranslatedStatus($ride->payment_status);
            $ride->payment_method = ucfirst($ride->payment_method);
        });

        $baseQuery = $this->getData();

        // Build locale-aware color maps.
        // Keys MUST match the translated label stored in $ride->status / $ride->payment_status
        // so that the table component's lookup ($colorClasses[$fieldValue]) always resolves.
        $rideStatusColorMap    = $this->buildRideStatusColorMap();
        $paymentStatusColorMap = $this->buildPaymentStatusColorMap();

        $tableConfig = [
            'columns'       => [
                ['title' => __('taxido::static.rides.ride_number'),      'field' => 'ride_numb',       'sortable' => true,  'sortField' => 'ride_number',          'type' => 'badge', 'badge_type' => 'light'],
                ['title' => __('taxido::static.rides.rider'),             'field' => 'rider_name',      'route' => 'admin.rider.show', 'email' => 'rider_email', 'profile_image' => 'rider_profile', 'sortable' => true, 'profile_id' => 'rider_id', 'sortField' => 'rider.name'],
                ['title' => __('taxido::static.rides.driver'),            'field' => 'driver_name',     'route' => 'admin.driver.show', 'email' => 'driver_email', 'profile_image' => 'driver_profile', 'sortable' => true, 'profile_id' => 'driver_id', 'sortField' => 'driver.name'],
                ['title' => __('taxido::static.rides.service'),           'field' => 'service',          'sortable' => true,  'sortField' => 'service.name'],
                ['title' => __('taxido::static.rides.service_category'),  'field' => 'service_category', 'sortable' => true,  'sortField' => 'service_category.name'],
                ['title' => __('taxido::static.rides.payment_status'),   'field' => 'payment_status',  'sortable' => true,  'sortField' => 'payment_status', 'type' => 'badge', 'colorClasses' => $paymentStatusColorMap],
                ['title' => __('taxido::static.rides.ride_status'),      'field' => 'status',           'sortable' => true,  'sortField' => 'ride_status',   'type' => 'badge', 'colorClasses' => $rideStatusColorMap],
                ['title' => __('taxido::static.rides.total'),             'field' => 'formatted_total',  'sortable' => true,  'sortField' => 'total'],
                ['title' => __('taxido::static.rides.created_at'),        'field' => 'date',             'sortable' => true,  'sortField' => 'created_at'],
                ['title' => __('taxido::static.rides.action'),            'type'  => 'action',           'permission' => ['ride.index'], 'sortable' => false],
            ],
            'data'          => $rides,
            'actions'       => [],
            'filters'       => [
                ['title' => 'All',                          'slug' => 'all',                   'count' => (clone $baseQuery)->count()],
                ['title' => ucfirst(ServicesEnum::CAB),     'slug' => ServicesEnum::CAB,     'count' => (clone $baseQuery)->where('rides.service_id', getServiceIdBySlug(ServicesEnum::CAB))->count()],
                ['title' => ucfirst(ServicesEnum::PARCEL),  'slug' => ServicesEnum::PARCEL,  'count' => (clone $baseQuery)->where('rides.service_id', getServiceIdBySlug(ServicesEnum::PARCEL))->count()],
                ['title' => ucfirst(ServicesEnum::FREIGHT), 'slug' => ServicesEnum::FREIGHT, 'count' => (clone $baseQuery)->where('rides.service_id', getServiceIdBySlug(ServicesEnum::FREIGHT))->count()],
                // ['title' => ucfirst(ServicesEnum::AMBULANCE), 'slug' => ServicesEnum::AMBULANCE, 'count' => (clone $baseQuery)->where('rides.service_id', getServiceIdBySlug(ServicesEnum::AMBULANCE))->count()],
            ],
            'bulkactions'   => [
                ['whenFilter' => ['all']],
            ],
            'actionButtons' => [
                ['icon' => 'ri-eye-line', 'permission' => 'ride.index', 'role' => 'admin', 'route' => 'admin.ride.details', 'field' => 'id', 'class' => 'dark-icon-box', 'tooltip' => 'Ride details'],
            ],
            'total'         => $rides->total(),
        ];

        return $tableConfig;
    }

    /**
     * Build a ride-status → badge-class map keyed by the TRANSLATED label
     * so the table component's ($colorClasses[$fieldValue]) lookup always resolves
     * regardless of the active locale.
     */
    protected function buildRideStatusColorMap(): array
    {
        return [
            $this->getTranslatedStatus(RideStatusEnum::REQUESTED)  => 'requested',
            $this->getTranslatedStatus(RideStatusEnum::PENDING)     => 'pending',
            $this->getTranslatedStatus(RideStatusEnum::SCHEDULED)   => 'scheduled',
            $this->getTranslatedStatus(RideStatusEnum::ACCEPTED)    => 'accepted',
            $this->getTranslatedStatus(RideStatusEnum::REJECTED)    => 'rejected',
            $this->getTranslatedStatus(RideStatusEnum::ARRIVED)     => 'arrived',
            $this->getTranslatedStatus(RideStatusEnum::STARTED)     => 'requested',
            $this->getTranslatedStatus(RideStatusEnum::CANCELLED)   => 'cancelled',
            $this->getTranslatedStatus(RideStatusEnum::COMPLETED)   => 'completed',
        ];
    }

    /**
     * Build a payment-status → badge-class map keyed by the TRANSLATED label.
     * Covers the statuses stored in rides.payment_status column.
     */
    protected function buildPaymentStatusColorMap(): array
    {
        return [
            $this->getTranslatedStatus('completed')  => 'completed',
            $this->getTranslatedStatus('pending')    => 'pending',
            $this->getTranslatedStatus('processing') => 'positive',
            $this->getTranslatedStatus('failed')     => 'failed',
            $this->getTranslatedStatus('cancelled')  => 'critical',
        ];
    }

    /**
     * Translate a ride/payment status value using the active locale.
     * Uses RideStatusEnum constants for matching to ensure type safety.
     */
    protected function getTranslatedStatus(?string $status): string
    {
        if (empty($status)) {
            return '';
        }

        $key = strtolower(trim($status));

        switch ($key) {
            case RideStatusEnum::PENDING:
                return __('taxido::static.statuses.pending');

            case RideStatusEnum::REQUESTED:
                return __('taxido::static.statuses.requested');

            case RideStatusEnum::SCHEDULED:
                return __('taxido::static.statuses.scheduled');

            case RideStatusEnum::ACCEPTED:
                return __('taxido::static.statuses.accepted');

            case RideStatusEnum::REJECTED:
                return __('taxido::static.statuses.rejected');

            case RideStatusEnum::ARRIVED:
                return __('taxido::static.statuses.arrived');

            case RideStatusEnum::STARTED:
                return __('taxido::static.statuses.started');

            case RideStatusEnum::CANCELLED:
                return __('taxido::static.statuses.cancelled');

            case RideStatusEnum::COMPLETED:
                return __('taxido::static.statuses.completed');

            case 'processing':
                return __('taxido::static.statuses.processing');

            case 'failed':
                return __('taxido::static.statuses.failed');

            default:
                return ucfirst($status);
        }
    }

    protected function sorting($rides)
    {
        if (!$this->request->has('orderby') || !$this->request->has('order')) {
            return $rides->orderBy('rides.created_at', 'desc');
        }

        $orderby = $this->request->get('orderby');
        $order   = strtolower($this->request->get('order')) === 'asc' ? 'asc' : 'desc';

        if (!in_array($orderby, $this->sortableColumns)) {
            return $rides->orderBy('rides.created_at', 'desc');
        }

        if (str_contains($orderby, '.')) {
            [$relation, $column] = explode('.', $orderby);

            switch ($relation) {
                case 'rider':
                    return $rides->leftJoin('users as rider_users', 'rides.rider_id', '=', 'rider_users.id')
                                 ->select('rides.*')
                                 ->orderBy("rider_users.$column", $order);
                case 'driver':
                    return $rides->leftJoin('users as driver_users', 'rides.driver_id', '=', 'driver_users.id')
                                 ->select('rides.*')
                                 ->orderBy("driver_users.$column", $order);
                case 'service':
                    return $rides->leftJoin('services', 'rides.service_id', '=', 'services.id')
                                 ->select('rides.*')
                                 ->orderBy("services.$column", $order);
                case 'service_category':
                    return $rides->leftJoin('service_categories', 'rides.service_category_id', '=', 'service_categories.id')
                                 ->select('rides.*')
                                 ->orderBy("service_categories.$column", $order);

            }
        }

        return $rides->orderBy("rides.$orderby", $order);
    }
}
