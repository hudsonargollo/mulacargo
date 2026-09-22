<?php

namespace Modules\Taxido\Repositories\Admin;

use Exception;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use App\Events\NewUserEvent;
use Illuminate\Http\Request;
use Modules\Taxido\Broadcasts\DocumentVerifyBroadcast;
use Modules\Taxido\Events\DriverLocationUpdated;
use Modules\Taxido\Models\Driver;
use Illuminate\Support\Facades\DB;
use Modules\Taxido\Models\VehicleType;
use Modules\Taxido\Services\DriverStateService;
use Spatie\Permission\Models\Role;
use Modules\Taxido\Enums\RoleEnum;
use Modules\Taxido\Models\Service;
use App\Exceptions\ExceptionHandler;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Taxido\Models\DriverDocument;
use Modules\Taxido\Exports\DriversExport;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Support\Facades\Log;


class DriverRepository extends BaseRepository
{
    protected $role;

    function model()
    {
        $this->role = new Role();
        return Driver::class;
    }

    public function index($driverTable)
    {
        if (request()['action']) {
            return redirect()->back();
        }
        $title = request()->has('is_verified')
            ? __('taxido::static.drivers.verified_drivers')
            : __('taxido::static.drivers.unverified_drivers');

        return view('taxido::admin.driver.index', ['tableConfig' => $driverTable, 'title' => $title]);
    }

    public function getUnverifiedDrivers($driverTable)
    {
        if (request()['action']) {
            return redirect()->back();
        }
        $title = __('taxido::static.drivers.unverified_drivers');
        return view('taxido::admin.driver.index', ['tableConfig' => $driverTable, 'title' => $title]);
    }

    public function store($request)
    {
        Log::info($request->all());
        DB::beginTransaction();
        try {

            $location = !empty($request->location) ? json_decode($request->location, true) : null;
            $referralCode = $request->referral_code;
            if (empty($referralCode)) {
                $referralCode = getReferralCodeByName($request->name ?? 'Driver');
            }

            $driver = $this->model->create([
                'name' => $request->name,
                'email' => $request->email,
                'country_code' => $request->country_code,
                'phone' => (string) $request->phone,
                'status' => $request->status,
                'is_online' => $request->is_online,
                'location' => $location,
                'password' => Hash::make($request->password),
                'profile_image_id' => $request->profile_image_id,
                'service_id' => $request->service_id,
                'service_category_id' => $request->service_category_id,
                'fleet_manager_id' => $request->fleet_manager_id,
                'is_verified' => $request->is_verified,
                'experience' => $request->experience,
                'price_type' => $request->price_type,
                'gear_type' => $request->gear_type,
                'per_km_charge' => $request->per_km_charge,
                'per_hour_charge' => $request->per_hour_charge,
                'per_day_charge' => $request->per_day_charge,
                'referral_code' => $referralCode,
            ]);

            $role = $this->role->findOrCreate(RoleEnum::DRIVER, 'web');
            $driver->assignRole($role);
            if (!empty($request->address)) {
                $driver->address()->create($request->address);
            }

            if (!empty($request->vehicle_info)) {
                $driver->vehicle_info()->create($request->vehicle_info);
            }

            if (!empty($request->payment_account)) {
                $driver->payment_account()->create($request->payment_account);
            }

            $ambulanceServiceId = $this->getAmbulanceServiceId();
            if (!empty($request->ambulance) && $request->service_id == $ambulanceServiceId) {
                $driver->ambulance()->create($request->ambulance);
            }

            if ($request->notify) {
                event(new NewUserEvent($driver, $request->password));
            }

            $this->syncDriverState($driver, $request);
            $driver->profile_image;

            DB::commit();
            return to_route('admin.driver.index')->with('success', __('taxido::static.drivers.create_successfully'));

        } catch (Exception $e) {
            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        Log::info('inside update');
        DB::beginTransaction();
        try {

            $request = Arr::except($request, ['password']);
            if (isset($request['phone'])) {
                $request['phone'] = (string) $request['phone'];
            }

            if (isset($request['location'])) {
                $request['location'] = json_decode($request['location'], true);
            }

            if (empty($request['referral_code'])) {
                unset($request['referral_code']);
            }

            $driver = $this->model->findOrFail($id);
            $driver->update($request);

            if (isset($request['profile_image_id'])) {
                $driver->profile_image()->associate($request['profile_image_id']);
            }

            $ambulanceServiceId = $this->getAmbulanceServiceId();
            if (isset($request['vehicle_info']) && $request['service_id'] != $ambulanceServiceId) {
                $driver->vehicle_info()->updateOrCreate([], $request['vehicle_info'] ?? []);
            }

            if (isset($request['address'])) {
                $driver->address()->updateOrCreate([], $request['address'] ?? []);
            }

            if (isset($request['payment_account'])) {
                $driver->payment_account()->updateOrCreate([], $request['payment_account'] ?? []);
            }

            if (isset($request['ambulance']) && $request['service_id'] == $ambulanceServiceId) {
                $driver->ambulance()->updateOrCreate([], $request['ambulance']);
            } elseif ($request['service_id'] != $ambulanceServiceId && $driver->ambulance) {
                $driver->ambulance()->delete();
            }

            DB::commit();
            $driver = $driver->refresh();
            $this->syncDriverState($driver, $request);
            Log::info($driver);
            return to_route('admin.driver.index')->with('success', __('taxido::static.drivers.update_successfully'));

        } catch (Exception $e) {

            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    private function getAmbulanceServiceId()
    {
        return Service::where('type', 'ambulance')->value('id') ?? 0;
    }

    protected function syncDriverState($driver, $request): void
    {
        try {

            if ($driver) {
                $location = $driver->location ?? ($request->location ? json_decode($request?->location, true) : null);
                $lat = 0;
                $lng = 0;
                if ($location && isset($location[0]['lat']) && isset($location[0]['lng'])) {
                    $lat = $location[0]['lat'];
                    $lng = $location[0]['lng'];
                    $realtimeData['lat'] = $lat;
                    $realtimeData['lng'] = $lng;
                }

                $realtimeData['rating'] = (float) $driver->reviews?->avg('rating');
                $realtimeData['rating_count'] = (int) $driver->reviews?->count();
                $driverState = app(DriverStateService::class);
                $driverInfo = $driverState->formatDriverForFleet($driver);
                $driverInfo = array_merge($driverInfo, [
                    'is_online' => $driver->is_online ? '1' : '0',
                    'is_verified' => $driver->is_verified ? '1' : '0',
                    'is_on_ride' => $driver->is_on_ride ? '1' : '0',
                    'lat' => (float) $lat,
                    'lng' => (float) $lng,
                ]);

                $driverState->updateDriverLocation(
                    $driver->id,
                    (float) $lat,
                    (float) $lng,
                    $driverInfo
                );

                event(new DriverLocationUpdated($driver->id, ['lat' => $lat, 'lng' => $lng], $realtimeData));
            }

        } catch (Exception $e) {

            throw new ExceptionHandler("Failed to sync driver realtime data: {$e->getMessage()}", $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $driver = $this->model->findOrFail($id);
            $driver->update(['status' => $status]);
            if ($status != 1) {
                $driver->tokens()->update([
                    'expires_at' => Carbon::now(),
                ]);
            }

            return json_encode(["resp" => $driver]);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function updateLocation($id, $locationData)
    {
        try {
            $driver = $this->model->findOrFail($id);
            $location = json_decode($locationData, true);
            $driver->update(['location' => $location]);

            $this->syncDriverState($driver, new Request(['location' => $locationData]));

            return json_encode(["resp" => $driver]);
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        Log::info('inside destroy');
        try {

            $driver = $this->model->findOrFail($id);
            $driver->destroy($id);
            // $this->realtimeDelete('driverTrack', (string) $id);

            return redirect()->back()->with('success', __('taxido::static.drivers.delete_successfully'));
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function restore($id)
    {
        try {

            $driver = $this->model->onlyTrashed()->findOrFail($id);
            $driver->restore();

            return redirect()->back()->with('success', __('taxido::static.drivers.restore_successfully'));

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function forceDelete($id)
    {
        try {

            $driver = $this->model->onlyTrashed()->findOrFail($id);
            $driver->forceDelete();

            return redirect()->back()->with('success', __('taxido::static.drivers.permanent_delete_successfully'));
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function verify($id, $status)
    {
        DB::beginTransaction();

        try {

            $driver = $this->model->findOrFail($id);
            $driver->update(['is_verified' => $status]);
            if ($status) {
                DriverDocument::where('driver_id', $id)->update(['status' => 'approved']);
            } else {
                DriverDocument::where('driver_id', $id)->update(['status' => 'pending']);
            }

            DB::commit();
            $driver = $driver->fresh();
            event(new DocumentVerifyBroadcast($driver, $status));

            return json_encode(["resp" => $driver]);

        } catch (Exception $e) {
            DB::rollBack();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function export(Request $request)
    {
        try {

            $allowedFormats = ['xlsx', 'xls', 'csv'];
            $format = $request->input('format', 'xlsx');
            if (!in_array($format, $allowedFormats)) {
                $format = 'xlsx';
            }
            $date = now()->format('Y-m-d');
            $fileName = "drivers_list_{$date}.{$format}";

            return Excel::download(new DriversExport, $fileName);

        } catch (Exception $e) {
            throw new Exception("Export failed: " . $e->getMessage(), $e->getCode());
        }
    }


    private function getFindDriverServiceId()
    {
        return Service::where('type', 'finddriver')->value('id') ?? 0;
    }
}
