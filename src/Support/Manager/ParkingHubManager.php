<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Support\Manager;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Manager;
use InvalidArgumentException;
use Oltrematica\ParkingHub\Contracts\ParkingValidator;

class ParkingHubManager extends Manager
{
    public function getDefaultDriver(): string
    {
        $defaultDriver = Config::string('parking-hub.default_driver'); // throws if not set

        if (blank($defaultDriver)) {
            throw new InvalidArgumentException('Default parking hub driver not specified in parking-hub.php config.');
        }

        return $defaultDriver;
    }

    /**
     * Create a new driver instance.
     * This method is called by the parent Manager class.
     * It overrides the parent's createDriver to provide generic driver instantiation
     * based on the 'class' key in the driver's configuration.
     *
     * @param  string  $driver  The name of the driver (e.g., 'easypark', 'mycicero').
     *
     * @throws InvalidArgumentException If the driver is not configured, its class is not specified or not found, or does not implement ParkingValidatorInterface.
     * @throws BindingResolutionException If the driver class cannot be resolved from the container.
     */
    protected function createDriver($driver): ParkingValidator
    {
        $driverConfig = Config::array("parking-hub.drivers.{$driver}");
        if (empty($driverConfig)) {
            throw new InvalidArgumentException("Driver [{$driver}] is not configured.");
        }

        /** @var string $driverClass */
        $driverClass = $driverConfig['class'] ?? null;
        if (empty($driverClass)) {
            throw new InvalidArgumentException("Driver [{$driver}] does not have a class defined.");
        }

        if (! class_exists($driverClass)) {
            throw new InvalidArgumentException("Driver [{$driver}] class [{$driverClass}] does not exist.");
        }

        // check if the class implements the ParkingValidator contract
        if (! is_subclass_of($driverClass, ParkingValidator::class)) {
            throw new InvalidArgumentException("Driver [{$driver}] class [{$driverClass}] does not implement the ParkingValidator contract.");
        }

        /** @var ParkingValidator $parkingValidatorObj */
        $parkingValidatorObj = $this->container->make($driverClass, ['config' => $driverConfig]);

        return $parkingValidatorObj;

    }
}
