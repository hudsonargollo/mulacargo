@use('Modules\Taxido\Models\Zone')
@use('Modules\Taxido\Models\FleetManager')
@use('Modules\Taxido\Models\Service')
@use('Modules\Taxido\Models\VehicleType')
@use('Modules\Taxido\Models\ServiceCategory')
@use('Modules\Taxido\Enums\ServicesEnum')
@php
    $vehicleTypes = VehicleType::where('status', true)->with('service_categories')->get(['id', 'name', 'service_id']);
    $services = Service::whereNull('deleted_at')?->where('status', true)->pluck('name', 'id');
    $serviceCategories = ServiceCategory::whereNull('deleted_at')?->where('status', true)->get();
    $fleetManagers = FleetManager::whereNull('deleted_at')
        ->where('status', true)
        ->get(['id', 'name', 'email', 'profile_image_id']);

    // Check if find driver service is selected (for both add and edit)
    $findDriverServiceId = Service::where('slug', 'finddriver')->first()->id ?? 0;
    $isFindDriverService = false;

    // For edit mode
    if (isset($driver) && isset($driver->service_id)) {
        $isFindDriverService = $driver->service_id == $findDriverServiceId;
    }
    // For add mode (check old input)
    if (old('service_id')) {
        $isFindDriverService = old('service_id') == $findDriverServiceId;
    }
@endphp
<div class="row">
    <div class="col-12">
        <div class="row g-xl-4 g-3">
            <div class="col-xl-10 col-xxl-8 mx-auto">
                <div class="contentbox">
                    <div class="inside">

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <div class="contentbox-title">
                            <h3>{{ isset($driver) ? __('taxido::static.drivers.edit') : __('taxido::static.drivers.add') }}
                            </h3>


                        </div>
                        <ul class="nav nav-tabs horizontal-tab custom-scroll" id="account" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" id="profile-tab" data-bs-toggle="tab" href="#profile"
                                    type="button" role="tab" aria-controls="profile" aria-selected="true">
                                    <i class="ri-shield-user-line"></i>
                                    {{ __('taxido::static.drivers.general') }}
                                    <i class="ri-error-warning-line danger errorIcon"></i>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="address-tab" data-bs-toggle="tab" href="#address" type="button"
                                    role="tab" aria-controls="address" aria-selected="true">
                                    <i class="ri-rotate-lock-line"></i>
                                    {{ __('taxido::static.drivers.address') }}
                                    <i class="ri-error-warning-line danger errorIcon"></i>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="vehicle-tab" data-bs-toggle="tab" href="#vehicle" type="button"
                                    role="tab" aria-controls="vehicle" aria-selected="true">
                                    <i class="ri-shield-user-line"></i>
                                    {{ __('taxido::static.drivers.vehicle') }}
                                    <i class="ri-error-warning-line danger errorIcon"></i>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="payout-tab" data-bs-toggle="tab" href="#payout" type="button"
                                    role="tab" aria-controls="payout" aria-selected="true">
                                    <i class="ri-rotate-lock-line"></i>
                                    {{ __('taxido::static.drivers.payout_details') }}
                                    <i class="ri-error-warning-line danger errorIcon"></i>
                                </a>
                            </li>
                            <li class="nav-item" role="presentation">
                                <a class="nav-link" id="additionalInfo-tab" data-bs-toggle="tab" href="#additionalInfo"
                                    type="button" role="tab" aria-controls="additionalInfo" aria-selected="true">
                                    <i class="ri-rotate-lock-line"></i>
                                    {{ __('taxido::static.drivers.additional_info') }}
                                    <i class="ri-error-warning-line danger errorIcon"></i>
                                </a>
                            </li>
                        </ul>
                        <div class="tab-content" id="accountContent">
                            <div class="tab-pane fade  {{ session('active_tab') != null ? '' : 'show active' }}"
                                id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="profile_image_id">{{ __('taxido::static.drivers.profile_image') }}<span>*</span></label>
                                    <div class="col-md-10">
                                        <div class="form-group">
                                            <x-image :name="'profile_image_id'" :data="isset($driver->profile_image)
                                                ? $driver?->profile_image
                                                : old('profile_image_id')" :text="' '"
                                                :multiple="false"></x-image>
                                            @error('profile_image_id')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="name">{{ __('taxido::static.drivers.full_name') }}
                                        <span> *</span> </label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" id="name" name="name"
                                            placeholder="{{ __('taxido::static.drivers.enter_full_name') }}"
                                            value="{{ isset($driver->name) ? $driver->name : old('name') }}">
                                        @error('name')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2" for="email">{{ __('taxido::static.drivers.email') }}
                                        <span>*</span>
                                    </label>
                                    <div class="col-md-10">
                                        @if (isset($driver) && isDemoModeEnabled())
                                            <input class="form-control" value="{{ __('static.demo_mode') }}"
                                                type="text" readonly>
                                        @else
                                            <input class="form-control" type="email" name="email"
                                                placeholder="{{ __('taxido::static.drivers.enter_email') }}"
                                                value="{{ isset($driver->email) ? $driver->email : old('email') }}">
                                        @endif
                                        @error('email')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="phone">{{ __('taxido::static.drivers.phone') }}<span>*</span></label>
                                    <div class="col-md-10">
                                        @if (isset($driver) && isDemoModeEnabled())
                                            <input class="form-control" value="{{ __('static.demo_mode') }}"
                                                type="text" readonly>
                                        @else
                                            <div class="input-group mb-3 phone-detail">
                                                <div class="col-sm-1">
                                                    <select class="select-2 form-control" id="select-country-code"
                                                        name="country_code">
                                                        @foreach (getCountryCodes() as $option)
                                                            <option class="option"
                                                                value="{{ $option->calling_code }}"
                                                                data-image="{{ asset('images/flags/' . $option->flag) }}"
                                                                @selected($option->calling_code == old('country_code', $driver->country_code ?? '1'))>
                                                                {{ $option->calling_code }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-sm-11">
                                                    <input class="form-control" type="number" name="phone"
                                                        value="{{ old('phone', $driver->phone ?? '') }}"
                                                        placeholder="{{ __('taxido::static.drivers.enter_phone') }}"
                                                        required>
                                                </div>
                                            </div>
                                        @endif
                                        @error('phone')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="referral_code">{{ __('taxido::static.referrals.referral_code') }}</label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" id="referral_code" name="referral_code"
                                            placeholder="{{ __('taxido::static.referrals.referral_code') }}"
                                            value="{{ old('referral_code', $driver->referral_code ?? '') }}">
                                        @error('referral_code')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                @if (request()->routeIs('admin.driver.create'))
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="password">{{ __('taxido::static.drivers.new_password') }}<span>
                                                *</span></label>
                                        <div class="col-md-10">
                                            <div class="position-relative">
                                                <input class="form-control" type="password" id="password"
                                                    name="password"
                                                    placeholder="{{ __('taxido::static.drivers.enter_password') }}">
                                                <i class="ri-eye-line toggle-password"></i>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="confirm_password">{{ __('taxido::static.drivers.confirm_password') }}<span>
                                                *</span></label>
                                        <div class="col-md-10">
                                            <div class="position-relative">
                                                <input class="form-control" type="password" name="confirm_password"
                                                    placeholder="{{ __('taxido::static.drivers.enter_confirm_password') }}"
                                                    required>
                                                <i class="ri-eye-line toggle-password"></i>
                                            </div>
                                            @error('confirm_password')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-2 mb-0"
                                            for="notify">{{ __('taxido::static.drivers.notification') }}</label>
                                        <div class="col-md-10">
                                            <div class="form-check p-0 w-auto">
                                                <input type="checkbox" name="notify" id="notify" value="1"
                                                    class="form-check-input me-2">
                                                <label
                                                    for="notify">{{ __('taxido::static.drivers.sentence') }}</label>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="footer">
                                    <button type="button"
                                        class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="address" role="tabpanel" aria-labelledby="address-tab">
                                <div class="form-group row">
                                    <label for="address[address]"
                                        class="col-md-2">{{ __('taxido::static.drivers.address') }}<span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control ui-widget autocomplete-google"
                                            id="address-input" name="address[address]"
                                            placeholder="{{ __('taxido::static.drivers.enter_address') }}"
                                            value="{{ old('address.address', @$driver->address->address) }}">
                                        @error('address.address')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="address[street_address]"
                                        class="col-md-2">{{ __('taxido::static.drivers.street_address') }}</label>
                                    <div class="col-md-10">
                                        <input type="text" class="form-control ui-widget" id="street_address_1"
                                            name="address[street_address]"
                                            placeholder="{{ __('taxido::static.drivers.enter_street_address') }}"
                                            value="{{ @$driver->address ? $driver->address?->street_address : old('address.street_address') }}">
                                    </div>
                                    @error('address.street_address')
                                        <span class="invalid-feedback d-block" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="address[area_locality]">{{ __('taxido::static.drivers.area_locality') }}
                                    </label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="address[area_locality]"
                                            placeholder="{{ __('taxido::static.drivers.enter_area_locality') }}"
                                            value="{{ @$driver?->address ? $driver?->address?->area_locality : old('address.area_locality') }}"
                                            id="area_locality">
                                        @error('address.area_locality')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="address[country_id]"
                                        class="col-md-2">{{ __('taxido::static.drivers.country') }}<span>
                                            *</span></label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="select-2 form-control select-country" id="country_id"
                                            name="address[country_id]"
                                            data-placeholder="{{ __('taxido::static.drivers.select_country') }}">
                                            <option class="option" value="" selected></option>
                                            @foreach (getCountries() as $key => $option)
                                                <option value="{{ $key }}" @selected(old('address.country_id', @$driver?->address?->country_id) == $key)>
                                                    {{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @error('address.country_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="address[state]">{{ __('taxido::static.drivers.state') }}
                                        <span>*</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="address[state]"
                                            placeholder="{{ __('taxido::static.drivers.enter_state') }}"
                                            value="{{ @$driver?->address ? $driver?->address?->state : old('address.state') }}">
                                        @error('address.state')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="address[city]">{{ __('taxido::static.drivers.city') }}
                                        <span> *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="address[city]"
                                            placeholder="{{ __('taxido::static.drivers.enter_city') }}"
                                            value="{{ @$driver?->address ? $driver?->address?->city : old('address.city') }}"
                                            id="city">
                                        @error('address.city')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="address[postal_code]">{{ __('taxido::static.drivers.postal_code') }}
                                        <span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="address[postal_code]"
                                            placeholder="{{ __('taxido::static.drivers.enter_postal_code') }}"
                                            value="{{ @$driver?->address ? $driver?->address?->postal_code : old('address.postal_code') }}"
                                            id="postal_code">
                                        @error('address.postal_code')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="footer">
                                    <button type="button"
                                        class="previousBtn bg-light-primary btn cancel">{{ __('static.previous') }}</button>
                                    <button type="button"
                                        class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
                                </div>

                            </div>
                            <div class="tab-pane fade" id="vehicle" role="tabpanel" aria-labelledby="vehicle-tab">
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="fleet_manager_id">{{ __('taxido::static.drivers.fleet_manager') }}</label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="form-select select-2" id="fleet_manager_id"
                                            name="fleet_manager_id"
                                            data-placeholder="{{ __('taxido::static.drivers.select_fleet_manager') }}">
                                            <option class="option" value=""></option>
                                            @foreach ($fleetManagers as $manager)
                                                <option value="{{ $manager->id }}" data-name="{{ $manager->name }}"
                                                    data-email="{{ $manager->email }}"
                                                    @if (isset($driver)) @selected(old('fleet_manager_id', $driver->fleet_manager_id) == $manager->id)
                                                    @else @selected(old('fleet_manager_id') == $manager->id) @endif>
                                                    {{ $manager->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('fleet_manager_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="service">{{ __('taxido::static.service_categories.service') }}
                                        <span>*</span></label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="form-select select-2" id="service_id" name="service_id"
                                            data-placeholder="{{ __('taxido::static.service_categories.select_service') }}">
                                            <option class="option" value="" selected></option>
                                            @foreach ($services as $index => $service)
                                                <option value="{{ $index }}"
                                                    @if (isset($driver)) @selected(old('service_id', $driver->service_id) == $index) @else @selected(old('service_id') == $index) @endif>
                                                    {{ $service }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('service_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row" id="service_category_container">
                                    <label class="col-md-2"
                                        for="serviceCategories">{{ __('taxido::static.vehicle_types.service_categories') }}<span>
                                            *</span></label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="form-control select-2" id="service_category_id"
                                            name="service_category_id"
                                            data-placeholder="{{ __('taxido::static.vehicle_types.select_service_categories') }}">
                                            <option value=""></option>
                                            @if (isset($driver) && $driver->service_category_id)
                                                @foreach ($serviceCategories as $serviceCategory)
                                                    <option value="{{ $serviceCategory->id }}"
                                                        @if ($driver->service_category_id == $serviceCategory->id || old('service_category_id') == $serviceCategory->id) selected @endif>
                                                        {{ $serviceCategory->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('service_category_id')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Vehicle Fields Container - Hidden for find driver service (both add and edit) -->
                                <div id="vehicle_fields_container"
                                    @if ($isFindDriverService) style="display: none;" @endif>

                                    <!-- Vehicle Type - Always visible -->
                                    <div class="form-group row" id="vehicle_type_field">
                                        <label class="col-md-2"
                                            for="vehicle_info[vehicle_type_id]">{{ __('taxido::static.drivers.vehicle') }}<span>*</span></label>
                                        <div class="col-md-10 select-label-error">
                                            <select class="form-control select-2 vehicle" id="vehicle_type_id"
                                                name="vehicle_info[vehicle_type_id]"
                                                data-placeholder="{{ __('taxido::static.drivers.select_vehicle') }}">
                                                <option value=""></option>
                                                @foreach ($vehicleTypes as $vehicle)
                                                    <option value="{{ $vehicle->id }}"
                                                        @if (old('vehicle_info.vehicle_type_id', @$driver?->vehicle_info?->vehicle_type_id) == $vehicle->id) selected @endif>
                                                        {{ $vehicle->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_info.vehicle_type_id')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Model - Hide for Find Driver -->
                                    <div class="form-group row vehicle_detail_field" id="vehicle_model_field">
                                        <label class="col-md-2"
                                            for="vehicle_info[model]">{{ __('taxido::static.drivers.model') }}
                                            <span> *</span></label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" name="vehicle_info[model]"
                                                placeholder="{{ __('taxido::static.drivers.enter_model') }}"
                                                value="{{ @$driver?->vehicle_info ? $driver?->vehicle_info?->model : old('vehicle_info.model') }}">
                                            @error('vehicle_info.model')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Plate Number - Hide for Find Driver -->
                                    <div class="form-group row vehicle_detail_field" id="vehicle_plate_field">
                                        <label class="col-md-2"
                                            for="vehicle_info[plate_number]">{{ __('taxido::static.drivers.plate_number') }}
                                            <span>*</span></label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text"
                                                name="vehicle_info[plate_number]"
                                                placeholder="{{ __('taxido::static.drivers.enter_plate_number') }}"
                                                value="{{ @$driver?->vehicle_info ? $driver?->vehicle_info?->plate_number : old('vehicle_info.plate_number') }}">
                                            @error('vehicle_info.plate_number')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Seat - Hide for Find Driver -->
                                    <div class="form-group row vehicle_detail_field" id="vehicle_seat_field">
                                        <label class="col-md-2"
                                            for="vehicle_info[seat]">{{ __('taxido::static.drivers.seat') }}
                                            <span> *</span></label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" min="1"
                                                max="{{ maximumSeat() }}" name="vehicle_info[seat]"
                                                placeholder="{{ __('taxido::static.drivers.enter_seat') }}"
                                                value="{{ @$driver?->vehicle_info ? $driver?->vehicle_info?->seat : old('vehicle_info.seat') }}">
                                            @error('vehicle_info.seat')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Color - Hide for Find Driver -->
                                    <div class="form-group row vehicle_detail_field" id="vehicle_color_field">
                                        <label for="vehicle_info[color]" class="col-md-2">
                                            {{ __('taxido::static.drivers.color') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10 select-label-error">
                                            <select class="select-2 form-control" id="vehicle_info[color]"
                                                name="vehicle_info[color]"
                                                data-placeholder="{{ __('taxido::static.drivers.enter_color') }}">
                                                @foreach (['White', 'Black', 'Red', 'Silver', 'Blue', 'Brown', 'Green', 'Yellow'] as $option)
                                                    <option class="option" value="{{ $option }}"
                                                        @selected($option == old('vehicle_info.color', $driver?->vehicle_info?->color ?? ''))>
                                                        {{ $option }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_info.color')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Find Driver Container - Show for find driver service only -->
                                <div id="find_driver_container"
                                    @if (!$isFindDriverService && isset($driver)) style="display:none" @endif>
                                    <!-- Experience Field -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="experience">
                                            {{ __('taxido::static.drivers.experience') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" min="0" max="100"
                                                name="experience" id="experience"
                                                placeholder="{{ __('taxido::static.drivers.experience_desc') }}"
                                                value="{{ old('experience', @$driver?->experience) }}">
                                            @error('experience')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Price Per Type - Multiple Select Dropdown -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="price_type">
                                            {{ __('taxido::static.drivers.price__type') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10 select-label-error">
                                            <select class="form-control select-2" id="price_type"
                                                name="price_type[]" multiple="multiple"
                                                data-placeholder="{{ __('taxido::static.drivers.select_price_types') }}">
                                                <option value="per_km_charge"
                                                    {{ in_array('per_km_charge', old('price_type', @$driver?->price_type ?? [])) ? 'selected' : '' }}>
                                                    {{ __('taxido::static.drivers.per_km_charge') }}
                                                </option>
                                                <option value="per_hour_charge"
                                                    {{ in_array('per_hour_charge', old('price_type', @$driver?->price_type ?? [])) ? 'selected' : '' }}>
                                                    {{ __('taxido::static.drivers.per_hour_charge') }}
                                                </option>
                                                <option value="per_day_charge"
                                                    {{ in_array('per_day_charge', old('price_type', @$driver?->price_type ?? [])) ? 'selected' : '' }}>
                                                    {{ __('taxido::static.drivers.per_day_charge') }}
                                                </option>
                                            </select>
                                            @error('price_type')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                            @error('price_type.*')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Gear Type - Single Select Dropdown -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="gear_type">
                                            {{ __('taxido::static.drivers.gear_type') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10 select-label-error">
                                            <select class="form-control select-2" id="gear_type" name="gear_type"
                                                data-placeholder="{{ __('taxido::static.drivers.select_gear_type') }}">
                                                <option value=""></option>
                                                <option value="automatic"
                                                    {{ old('gear_type', @$driver?->gear_type) == 'automatic' ? 'selected' : '' }}>
                                                    {{ __('taxido::static.drivers.automatic') }}
                                                </option>
                                                <option value="manual"
                                                    {{ old('gear_type', @$driver?->gear_type) == 'manual' ? 'selected' : '' }}>
                                                    {{ __('taxido::static.drivers.manual') }}
                                                </option>
                                            </select>
                                            @error('gear_type')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Per KM Charge -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="per_km_charge">
                                            {{ __('taxido::static.drivers.per_km_charge') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" step="0.01" min="0"
                                                name="per_km_charge" id="per_km_charge"
                                                placeholder="{{ __('taxido::static.drivers.enter_per_km_charge') }}"
                                                value="{{ old('per_km_charge', @$driver?->per_km_charge) }}">
                                            @error('per_km_charge')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Per Hour Charge -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="per_hour_charge">
                                            {{ __('taxido::static.drivers.per_hour_charge') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" step="0.01" min="0"
                                                name="per_hour_charge" id="per_hour_charge"
                                                placeholder="{{ __('taxido::static.drivers.enter_per_hour_charge') }}"
                                                value="{{ old('per_hour_charge', @$driver?->per_hour_charge) }}">
                                            @error('per_hour_charge')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Per Day Charge -->
                                    <div class="form-group row">
                                        <label class="col-md-2" for="per_day_charge">
                                            {{ __('taxido::static.drivers.per_day_charge') }}<span>*</span>
                                        </label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="number" step="0.01" min="0"
                                                name="per_day_charge" id="per_day_charge"
                                                placeholder="{{ __('taxido::static.drivers.enter_per_day_charge') }}"
                                                value="{{ old('per_day_charge', @$driver?->per_day_charge) }}">
                                            @error('per_day_charge')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Ambulance Fields -->
                                <div id="ambulance_fields_container" style="display:none">
                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="vehicle_info[name]">{{ __('taxido::static.drivers.ambulance_name') }}
                                            <span> *</span></label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" name="ambulance[name]"
                                                placeholder="{{ __('taxido::static.drivers.enter_ambulance_name') }}"
                                                value="{{ @$driver?->ambulance ? $driver?->ambulance?->name : old('vehicle_info.name') }}">
                                            @error('vehicle_info.name')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label class="col-md-2"
                                            for="vehicle_info[description]">{{ __('taxido::static.drivers.ambulance_description') }}
                                            <span> *</span></label>
                                        <div class="col-md-10">
                                            <input class="form-control" type="text" name="ambulance[description]"
                                                placeholder="{{ __('taxido::static.drivers.enter_ambulance_description') }}"
                                                value="{{ @$driver?->ambulance ? $driver?->ambulance?->description : old('vehicle_info.description') }}">
                                            @error('vehicle_info.description')
                                                <span class="invalid-feedback d-block" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="footer mt-4">
                                    <button type="button"
                                        class="previousBtn bg-light-primary btn cancel">{{ __('static.previous') }}</button>
                                    <button type="button"
                                        class="nextBtn btn btn-primary">{{ __('static.next') }}</button>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="payout" role="tabpanel" aria-labelledby="payout-tab">

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="bank_account_no">{{ __('taxido::static.drivers.bank_account_no') }}
                                        <span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text"
                                            name="payment_account[bank_account_no]"
                                            placeholder="{{ __('taxido::static.drivers.enter_bank_account') }}"
                                            value="{{ @$driver?->payment_account ? $driver?->payment_account?->bank_account_no : old('payment_account.bank_account_no') }}">
                                        @error('payment_account.bank_account_no')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="bank_name">{{ __('taxido::static.drivers.bank_name') }}
                                        <span> *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="payment_account[bank_name]"
                                            placeholder="{{ __('taxido::static.drivers.enter_bank_name') }}"
                                            value="{{ @$driver?->payment_account ? $driver?->payment_account?->bank_name : old('payment_account.bank_name') }}">
                                        @error('payment_account.bank_name')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="bank_holder_name">{{ __('taxido::static.drivers.holder_name') }} <span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text"
                                            name="payment_account[bank_holder_name]"
                                            placeholder="{{ __('taxido::static.drivers.enter_holder_name') }}"
                                            value="{{ @$driver?->payment_account ? $driver?->payment_account?->bank_holder_name : old('payment_account.bank_holder_name') }}">
                                        @error('payment_account.bank_holder_name')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2" for="swift">{{ __('taxido::static.drivers.swift') }}
                                        <span>
                                            *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text" name="payment_account[swift]"
                                            placeholder="{{ __('taxido::static.drivers.enter_swift_code') }}"
                                            value="{{ @$driver?->payment_account ? $driver?->payment_account?->swift : old('payment_account.swift') }}">
                                        @error('payment_account.swift')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2" for="routing_number">
                                        {{ __('taxido::static.drivers.routing_number') }}
                                        <span>*</span>
                                    </label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text"
                                            name="payment_account[routing_number]"
                                            placeholder="{{ __('taxido::static.drivers.enter_routing_number') }}"
                                            value="{{ @$driver?->payment_account?->routing_number ?? old('payment_account.routing_number') }}">
                                        @error('payment_account.routing_number')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="paypal_email">{{ __('taxido::static.drivers.paypal_email') }}
                                        <span> *</span></label>
                                    <div class="col-md-10">
                                        <input class="form-control" type="text"
                                            name="payment_account[paypal_email]"
                                            placeholder="{{ __('taxido::static.drivers.enter_paypal_email') }}"
                                            value="{{ $driver?->payment_account?->paypal_email ?? old('payment_account.enter_paypal_email') }}">
                                        @error('payment_account.paypal_email')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="form-group row">
                                    <label for="default" class="col-md-2">
                                        {{ __('taxido::static.drivers.default') }}<span>*</span>
                                    </label>
                                    <div class="col-md-10 select-label-error">
                                        <select class="select-2 form-control" id="default" name="default"
                                            data-placeholder="{{ __('taxido::static.drivers.select_default') }}">
                                            <option class="option" value="" selected></option>
                                            <option value="bank" @selected(old('default', @$driver?->payment_account?->default) == 'bank')>
                                                {{ __('taxido::static.drivers.bank') }}
                                            </option>
                                            <option value="paypal" @selected(old('default', @$driver?->payment_account?->default) == 'paypal')>
                                                {{ __('taxido::static.drivers.paypal') }}
                                            </option>
                                        </select>
                                        @error('status')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="footer">
                                    <button type="button"
                                        class="previousBtn bg-light-primary btn cancel">{{ __('static.previous') }}</button>
                                    <button class="nextBtn btn btn-primary"
                                        type="button">{{ __('static.next') }}</button>
                                </div>
                            </div>
                            <div class="tab-pane fade {{ session('active_tab') == 'additionalInfo-tab' ? 'show active' : '' }}"
                                id="additionalInfo" role="tabpanel" aria-labelledby="additionalInfo-tab">

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="location">{{ __('taxido::static.drivers.location') }}</label>
                                    <div class="col-md-10 position-relative">
                                        <input type="hidden" name="location" id="location"
                                            value='@json(isset($driver->location) ? $driver->location : [])'>
                                        <input id="map-search" type="text"
                                            placeholder="{{ __('taxido::static.drivers.search_location') }}"
                                            class="form-control map-search-box">

                                        <div id="map" class="google-map-container"></div>
                                        <p id="location-error" class="invalid-feedback d-block" role="alert"></p>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="is_online">{{ __('taxido::static.drivers.is_online') }}</label>
                                    <div class="col-md-10">
                                        <div class="switch-field form-control">
                                            <input value="1" type="radio" name="is_online"
                                                id="is_online_active" @checked(@$driver?->is_online === 1) />
                                            <label for="is_online_active">{{ __('taxido::static.active') }}</label>
                                            <input value="0" type="radio" name="is_online"
                                                id="is_online_inactive" @checked(@$driver?->is_online === 0 || @$driver?->is_online === null) />
                                            <label
                                                for="is_online_inactive">{{ __('taxido::static.deactive') }}</label>
                                        </div>
                                        @error('is_online')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="is_verified">{{ __('taxido::static.drivers.is_verified') }}</label>
                                    <div class="col-md-10">
                                        <div class="switch-field form-control">
                                            <input value="1" type="radio" name="is_verified"
                                                id="is_verified_active" @checked(@$driver?->is_verified === 1) />
                                            <label for="is_verified_active">{{ __('taxido::static.active') }}</label>
                                            <input value="0" type="radio" name="is_verified"
                                                id="is_verified_inactive" @checked(@$driver?->is_verified === 0 || @$driver?->is_verified === null) />
                                            <label
                                                for="is_verified_inactive">{{ __('taxido::static.deactive') }}</label>
                                        </div>
                                        @error('is_verified')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2"
                                        for="is_on_ride">{{ __('taxido::static.drivers.is_on_ride') }}</label>
                                    <div class="col-md-10">
                                        <div class="switch-field form-control">
                                            <input value="1" type="radio" name="is_on_ride"
                                                id="is_on_ride_active" @checked(@$driver?->is_on_ride === 1) />
                                            <label for="is_on_ride_active">{{ __('taxido::static.active') }}</label>
                                            <input value="0" type="radio" name="is_on_ride"
                                                id="is_on_ride_inactive" @checked(@$driver?->is_on_ride === 0 || @$driver?->is_on_ride === null) />
                                            <label
                                                for="is_on_ride_inactive">{{ __('taxido::static.deactive') }}</label>
                                        </div>
                                        @error('is_on_ride')
                                            <span class="invalid-feedback d-block" role="alert">
                                                <strong>{{ $message }}</strong>
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label class="col-md-2" for="status">{{ __('taxido::static.status') }}
                                    </label>
                                    <div class="col-md-10">
                                        <div class="switch-field form-control">
                                            <input value="1" type="radio" name="status" id="status_active"
                                                @checked(@$driver?->status === 1) />
                                            <label for="status_active">{{ __('taxido::static.active') }}</label>
                                            <input value="0" type="radio" name="status" id="status_deactive"
                                                @checked(@$driver?->status === 0 || @$driver?->status === null) />
                                            <label for="status_deactive">{{ __('taxido::static.deactive') }}</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-12">
                                        <div class="submit-btn">
                                            <button type="button"
                                                class="previousBtn bg-light-primary btn cancel">{{ __('static.previous') }}</button>
                                            <button type="submit" name="save"
                                                class="btn btn-solid spinner-btn submitBtn">
                                                <i
                                                    class="ri-save-line text-white lh-1"></i>{{ __('taxido::static.save') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places"></script>
    <script>
        (function($) {
            "use strict";

            const ambulanceServiceId = {{ \Modules\Taxido\Models\Service::where('slug', 'ambulance')->first()->id ?? 0 }};
            const findDriverServiceId = {{ \Modules\Taxido\Models\Service::where('slug', 'finddriver')->first()->id ?? 0 }};

            let map, marker;
            const defaultLocation = { lat: -1.3119705387570564, lng: 36.90288910995129 };
            const selectedVehicleId = "{{ old('vehicle_info.vehicle_type_id', @$driver?->vehicle_info?->vehicle_type_id ?? '') }}";
            const driverId = "{{ @$driver?->id ?? '0' }}";
             const savedCategoryId = "{{ old('service_category_id', @$driver?->service_category_id ?? '') }}";

            // DOM Elements
            const $serviceSelect       = $('#service_id');
            const $categorySelect      = $('#service_category_id');
            const $vehicleSelect       = $('#vehicle_type_id');

            const $vehicleContainer    = $('#vehicle_fields_container');
            const $findDriverContainer = $('#find_driver_container');
            const $ambulanceContainer  = $('#ambulance_fields_container');
            const $categoryContainer   = $('#service_category_container');
            const $vehicleDetailFields = $('.vehicle_detail_field'); // Model, Plate, Seat, Color

            // ==================== GOOGLE MAPS ====================
            function initGoogleFeatures() {
                if (typeof google !== 'undefined') {
                    initializeMap();
                    initializeAutocomplete();
                } else {
                    setTimeout(initGoogleFeatures, 200);
                }
            }

            function initializeMap() {
                let location = defaultLocation;
                const locVal = $('#location').val();
                if (locVal) {
                    try {
                        const parsed = JSON.parse(locVal);
                        if (parsed.lat && parsed.lng) location = parsed;
                        else if (Array.isArray(parsed) && parsed[0] && parsed[0].lat) location = parsed[0];
                    } catch(e) {}
                }

                map = new google.maps.Map(document.getElementById("map"), { zoom: 14, center: location });

                const searchBox = new google.maps.places.SearchBox(document.getElementById("map-search"));
                map.addListener("bounds_changed", () => searchBox.setBounds(map.getBounds()));

                searchBox.addListener("places_changed", () => {
                    const places = searchBox.getPlaces();
                    if (places.length === 0) return;
                    const place = places[0];
                    if (place.geometry?.location) {
                        updateMarker(place.geometry.location);
                        saveLocation(place.geometry.location);
                        updateDatabaseLocation(place.geometry.location);
                        map.setCenter(place.geometry.location);
                        map.setZoom(15);
                    }
                });

                map.addListener('click', (e) => {
                    updateMarker(e.latLng);
                    saveLocation(e.latLng);
                    updateDatabaseLocation(e.latLng);
                });

                if (location !== defaultLocation) updateMarker(location);

                const hasStoredCoords = (location !== defaultLocation);
                const addressText = $('#address-input').val();
                if (!hasStoredCoords && addressText) {
                    geocodeAddress(addressText);
                }
            }

            function geocodeAddress(address) {
                const geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: address }, function(results, status) {
                    if (status === 'OK' && results[0]) {
                        const pos = results[0].geometry.location;
                        map.setCenter(pos);
                        map.setZoom(15);
                        updateMarker(pos);
                        saveLocation(pos);
                    }
                });
            }

            function updateMarker(pos) {
                if (marker) marker.setPosition(pos);
                else {
                    marker = new google.maps.Marker({ position: pos, map: map, draggable: true });
                    marker.addListener('dragend', () => {
                        saveLocation(marker.getPosition());
                        updateDatabaseLocation(marker.getPosition());
                    });
                }
            }

            function saveLocation(latLng) {
                $('#location').val(JSON.stringify([{ lat: latLng.lat(), lng: latLng.lng() }]));
            }

            function updateDatabaseLocation(latLng) {
                if (driverId !== '0') {
                    $.ajax({
                        url: "{{ url('admin/driver/update-location') }}/" + driverId,
                        type: 'PUT',
                        data: { _token: "{{ csrf_token() }}", location: JSON.stringify([{ lat: latLng.lat(), lng: latLng.lng() }]) }
                    });
                }
            }

            function initializeAutocomplete() {
                const input = document.getElementById('address-input');
                if (!input) return;

                const autocomplete = new google.maps.places.Autocomplete(input, {
                    fields: ['geometry', 'address_components', 'formatted_address']
                });

                autocomplete.addListener('place_changed', () => {
                    const place = autocomplete.getPlace();
                    if (!place.geometry || !place.geometry.location) return;

                    const pos = place.geometry.location;

                    // Update the map & marker
                    if (map) {
                        map.setCenter(pos);
                        map.setZoom(15);
                        updateMarker(pos);
                        saveLocation(pos);
                        updateDatabaseLocation(pos);
                    }

                    // Auto-fill address component fields
                    let city = '', state = '', postalCode = '', country = '', streetAddress = '';
                    if (place.address_components) {
                        place.address_components.forEach(component => {
                            const types = component.types;
                            if (types.includes('street_number') || types.includes('route')) {
                                streetAddress += (streetAddress ? ' ' : '') + component.long_name;
                            }
                            if (types.includes('locality') || types.includes('postal_town')) {
                                city = component.long_name;
                            }
                            if (types.includes('administrative_area_level_1')) {
                                state = component.long_name;
                            }
                            if (types.includes('postal_code')) {
                                postalCode = component.long_name;
                            }
                        });
                    }

                    if (streetAddress) $('[name="address[street_address]"]').val(streetAddress);
                    if (city)          $('#city').val(city);
                    if (state)         $('[name="address[state]"]').val(state);
                    if (postalCode)    $('#postal_code').val(postalCode);
                });
            }

            // ==================== MAIN TOGGLE LOGIC ====================
            function toggleFields(serviceId) {
                const isFindDriver = Number(serviceId) === Number(findDriverServiceId);
                const isAmbulance  = Number(serviceId) === Number(ambulanceServiceId);

                if (isAmbulance) {
                    $vehicleContainer.hide();
                    $findDriverContainer.hide();
                    $ambulanceContainer.show();
                    $categoryContainer.hide();
                }
                else if (isFindDriver) {
                    // Find Driver Service
                    $vehicleContainer.show();
                    $vehicleDetailFields.hide();        // Hide Model, Plate, Seat, Color
                    $findDriverContainer.show();
                    $ambulanceContainer.hide();
                    $categoryContainer.show();
                }
                else {
                    // Default Cab / Normal Services
                    $vehicleContainer.show();
                    $vehicleDetailFields.show();        // Show Model, Plate, Seat, Color
                    $findDriverContainer.hide();
                    $ambulanceContainer.hide();
                    $categoryContainer.show();
                }
            }

            // Load functions using preloaded data to avoid API license checker
            const allServiceCategories = @json($serviceCategories);
            const allVehicleTypes = @json($vehicleTypes);

            function loadServiceCategories(serviceId) {
                if (!serviceId) return;
                $categorySelect.empty().append('<option value=""></option>');

                const filtered = allServiceCategories.filter(cat => Number(cat.service_id) === Number(serviceId));
                filtered.forEach(item => {
                    $categorySelect.append(new Option(item.name, item.id));
                });
            }

            function loadVehicles(serviceId, catId) {
                if (!serviceId || !catId) return;
                $vehicleSelect.empty().append('<option value=""></option>');

                const filtered = allVehicleTypes.filter(v => {
                    const matchesService = Number(v.service_id) === Number(serviceId);
                    const matchesCategory = v.service_categories && v.service_categories.some(c => Number(c.id) === Number(catId));
                    return matchesService && matchesCategory;
                });

                filtered.forEach(item => {
                    const opt = new Option(item.name, item.id);
                    if (String(item.id) === String(selectedVehicleId)) opt.selected = true;
                    $vehicleSelect.append(opt);
                });
            }

            // ==================== INITIALIZE ====================
            $(document).ready(function() {

                initGoogleFeatures();
                $('.select-2').select2({ allowClear: true });

                // Important: Initial toggle based on currently selected service
                toggleFields($serviceSelect.val());

                // If service is selected on page load (e.g. edit mode or validation error), ensure categories and vehicles are loaded
                if ($serviceSelect.val()) {
                    if (!$categorySelect.val() || !$categorySelect.find('option[value!=""]').length) {
                        loadServiceCategories($serviceSelect.val());
                    }
                    if ($categorySelect.val() && (!$vehicleSelect.val() || !$vehicleSelect.find('option[value!=""]').length)) {
                        loadVehicles($serviceSelect.val(), $categorySelect.val());
                    }
                }

                // Service Change Event
                $serviceSelect.on('change', function() {
                    toggleFields(this.value);

                    // Reset child dropdowns
                    $categorySelect.empty().append('<option value=""></option>');
                    $vehicleSelect.empty().append('<option value=""></option>');

                    if (this.value) loadServiceCategories(this.value);
                });

                $categorySelect.on('change', function() {
                    const sid = $serviceSelect.val();
                    if (sid && Number(sid) !== Number(ambulanceServiceId)) {
                        loadVehicles(sid, this.value);
                    }
                });

                // Validation (Dynamic)
                $('#driverForm').validate({
                    ignore: [],
                    rules: {
                        name: "required",
                        email: {required: true, email: true},
                        phone: {required: true, minlength:6, maxlength:15},
                        "address[address]": "required",
                        "address[country_id]": "required",
                        "address[state]": {required:true, notOnlyNumeric:true},
                        "address[city]": {required:true, notOnlyNumeric:true},
                        "address[postal_code]": "required",





                        "vehicle_info[vehicle_type_id]": { required: function() {
                            const s = $serviceSelect.val();
                            return s && Number(s) !== Number(findDriverServiceId) && Number(s) !== Number(ambulanceServiceId);
                        }},
                        "vehicle_info[model]": { required: function(){ return Number($serviceSelect.val()) !== Number(findDriverServiceId) && Number($serviceSelect.val()) !== Number(ambulanceServiceId); }},
                        "vehicle_info[plate_number]": { required: function(){ return Number($serviceSelect.val()) !== Number(findDriverServiceId) && Number($serviceSelect.val()) !== Number(ambulanceServiceId); }},
                        "vehicle_info[seat]": { required: function(){ return Number($serviceSelect.val()) !== Number(findDriverServiceId) && Number($serviceSelect.val()) !== Number(ambulanceServiceId); }},
                        "vehicle_info[color]": { required: function(){ return Number($serviceSelect.val()) !== Number(findDriverServiceId) && Number($serviceSelect.val()) !== Number(ambulanceServiceId); }},

                        experience: { required: function(){ return Number($serviceSelect.val()) === Number(findDriverServiceId); }},
                        gear_type: { required: function(){ return Number($serviceSelect.val()) === Number(findDriverServiceId); }},
                        "price_type[]": { required: function(){ return Number($serviceSelect.val()) === Number(findDriverServiceId); }},

                        per_km_charge:   { required: function(){ return $('#price_type').val()?.includes('per_km_charge'); }, number:true, min:0 },
                        per_hour_charge: { required: function(){ return $('#price_type').val()?.includes('per_hour_charge'); }, number:true, min:0 },
                        per_day_charge:  { required: function(){ return $('#price_type').val()?.includes('per_day_charge'); }, number:true, min:0 },

                        "ambulance[name]": { required: function(){ return Number($serviceSelect.val()) === Number(ambulanceServiceId); }},
                        "ambulance[description]": { required: function(){ return Number($serviceSelect.val()) === Number(ambulanceServiceId); }}



                }
                });

                $.validator.addMethod("notOnlyNumeric", function(v) {
                    return !/^\d+$/.test(v);
                }, "Cannot contain only numbers.");
            });

        })(jQuery);
    </script>
@endpush
