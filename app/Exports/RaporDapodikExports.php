<?php

namespace App\Exports;

use App\Http\Controllers\RaporDapodikController;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class RaporDapodikExports implements FromQuery
{
    use Exportable;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function query()
    {
        $fetch = RaporDapodikController::getRaporDapodik();
    }
}
