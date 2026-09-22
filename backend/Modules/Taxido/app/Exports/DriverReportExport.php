<?php

namespace Modules\Taxido\Exports;

use Modules\Taxido\Models\Driver;
use App\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Modules\Taxido\Enums\RoleEnum;
use Modules\Taxido\Enums\RideStatusEnum;
use Modules\Taxido\Tables\DriverTable;

class DriverReportExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        if (isDemoModeEnabled()) {
            throw new ExceptionHandler("This action is disabled in demo mode", 400);
        }

        $driverTable = new DriverTable(request()->merge([
            'export' => true,
        ]));

        return $driverTable->getData();
    }

    public function map($driver): array
    {
        return [
            $driver->name,
            $driver->email,
            $driver->getRatingCountAttribute(),
            getDefaultCurrency()?->symbol . $driver->total_driver_commission,
            getTotalDriverRidesByStatus(RideStatusEnum::STARTED, $driver->id),
            getTotalDriverRidesByStatus(RideStatusEnum::COMPLETED, $driver->id),
            getTotalDriverRidesByStatus(RideStatusEnum::SCHEDULED, $driver->id),
            getTotalDriverRidesByStatus(RideStatusEnum::CANCELLED, $driver->id),
        ];
    }

    /**
     * Get the headings for the export file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'Name',
            'Email',
            'Rating',
            'Total Earnings',
            'Active Rides',
            'Completed Rides',
            'Scheduled Rides',
            'Cancelled Rides',
        ];
    }
}
