<?php

namespace Modules\Taxido\Http\Requests\Admin;

use Illuminate\Validation\Rule;
use Modules\Taxido\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */


    public function rules()
    {
        $driver = $this->route('driver');
        $driverId = is_object($driver) ? $driver->id : ($driver ?? $this->id);
        $rules = [
            'profile_image_id' => ['nullable','exists:media,id,deleted_at,NULL'],
            'name' => ['required','max:255'],
            'referral_code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('users', 'referral_code')
                    ->ignore($driverId, 'id')
                    ->whereNull('deleted_at')
            ],
            'email' => [
                config('app.demo') ? 'nullable' : 'required',
                'email',
                Rule::unique('users', 'email')
                    ->ignore($driverId, 'id')
                    ->whereNull('deleted_at')
            ],
            'phone' => [
                config('app.demo') ? 'nullable' : 'required',
                'max:255',
                Rule::unique('users', 'phone')
                    ->ignore($driverId, 'id')
                    ->whereNull('deleted_at')
                    ->where(function ($query) {
                        $query->where('country_code', request('country_code'));
                    }),
            ],
            'address.address' => ['required'],
            'address.country_id' => ['required','exists:countries,id'],
            'address.state' => ['required'],
            'address.city' => ['required'],
            'address.postal_code' => ['required'],
            'payment_account.bank_account_no' => ['required'],
            'payment_account.bank_name' => ['required'],
            'payment_account.bank_holder_name' => ['required'],
            'payment_account.swift' => ['required'],
            'payment_account.routing_number' => ['required'],
           'experience' => ['nullable', 'required_if:service_id,==,' . Service::where('slug', 'finddriver')->value('id')],
            'service_id' => ['required','exists:services,id,deleted_at,NULL'],
            'service_category_id' => ['required','exists:service_categories,id,deleted_at,NULL'],
            'vehicle_type_id' => ['required_if:vehicle_type_id,!=,find-driver','exists:vehicle_types,id,deleted_at,NULL'],
            'gear_type' => [ 'nullable','required_if:gear_type,find-driver', 'in:automatic,manual'],
        ];

        if (!$this->isAmbulanceService() && !$this->isFindDriverService()) {
            $rules['vehicle_info.vehicle_type_id'] = ['required','exists:vehicle_types,id,deleted_at,NULL'];
            $rules['vehicle_info.model'] = ['required'];
            $rules['vehicle_info.plate_number'] = ['required'];
            $rules['vehicle_info.seat'] = ['required'];
            $rules['vehicle_info.color'] = ['required'];
            // $rules['service_category_id'] = ['required','exists:service_categories,id,deleted_at,NULL'];
        }

        return $rules;
    }

    protected function isAmbulanceService()
    {
        $ambulanceServiceId = Service::where('slug', 'ambulance')?->value('id');
        return $this->input('service_id') == $ambulanceServiceId;
    }  

    protected function isFindDriverService()
    {
        $findDriverServiceId = \Modules\Taxido\Models\Service::where('slug', 'finddriver')->value('id');
        return $this->input('service_id') == $findDriverServiceId;
    }

}
