<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Facades;

use Illuminate\Support\Facades\Facade;
use Oltrematica\ParkingHub\Support\Manager\ParkingHubManager;

/**
 * @method static \Oltrematica\ParkingHub\Contracts\ParkingValidator driver(string|null $driver = null)
 * @method static \Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData checkPlate(string $plateNumber, \Carbon\CarbonInterface $verificationDateTime)
 *
 * @see ParkingHubManager
 */
class ParkingHub extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return ParkingHubManager::class;
    }
}
