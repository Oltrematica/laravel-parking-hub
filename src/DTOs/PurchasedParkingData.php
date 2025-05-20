<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\DTOs;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Data;

class PurchasedParkingData extends Data
{
    /**
     * @param  CarbonInterface  $startDateTime  the start date and time of the parking
     * @param  CarbonInterface  $endDateTime  the end date and time of the parking
     */
    public function __construct(
        public CarbonInterface $startDateTime,
        public CarbonInterface $endDateTime,
    ) {}
}
