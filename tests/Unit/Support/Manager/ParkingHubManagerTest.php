<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;
use Oltrematica\ParkingHub\Contracts\ParkingValidator;
use Oltrematica\ParkingHub\Support\Manager\ParkingHubManager;

beforeEach(function (): void {
    $this->app = app(); // Ensure the app is available for the manager
    $this->manager = new ParkingHubManager($this->app);
});

describe('ParkingHubManager - getDefaultDriver', function (): void {
    it('returns the default driver when configured', function (): void {
        // Arrange
        Config::set('parking-hub.default_driver', 'testdriver');

        // Act
        $defaultDriver = $this->manager->getDefaultDriver();

        // Assert
        expect($defaultDriver)->toBe('testdriver');
    });

    it('throws an exception if default driver is not set in config', function (): void {
        // Arrange
        Config::set('parking-hub.default_driver', null);

        // Act & Assert
        expect(fn () => $this->manager->getDefaultDriver())
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws an exception if default driver is blank in config', function (): void {
        // Arrange
        Config::set('parking-hub.default_driver', '');

        // Act & Assert
        expect(fn () => $this->manager->getDefaultDriver())
            ->toThrow(InvalidArgumentException::class, 'Default parking hub driver not specified in parking-hub.php config.');
    });
});

describe('ParkingHubManager - createDriver', function (): void {
    it('creates and returns a driver instance for a valid configuration', function (): void {
        // Arrange
        $driverName = 'valid_driver';
        $driverClass = new class([]) implements ParkingValidator
        {
            public function checkPlate(string $plateNumber, ?Carbon\CarbonInterface $verificationDateTime): Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData {}
        };
        Config::set("parking-hub.drivers.{$driverName}", ['class' => $driverClass::class]);

        // Act
        $driverInstance = $this->manager->driver($driverName);

        // Assert
        expect($driverInstance)->toBeInstanceOf($driverClass::class);
    });

    it('throws an exception if driver is not configured', function (): void {
        // Arrange
        $driverName = 'unconfigured_driver';
        Config::set("parking-hub.drivers.{$driverName}", null); // Ensure it's not configured or empty

        // Act & Assert
        expect(fn () => $this->manager->driver($driverName))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws an exception if driver class is not defined', function (): void {
        // Arrange
        $driverName = 'no_class_driver';
        Config::set("parking-hub.drivers.{$driverName}", ['config_key' => 'some_value']); // No 'class' key

        // Act & Assert
        expect(fn () => $this->manager->driver($driverName))
            ->toThrow(InvalidArgumentException::class, "Driver [{$driverName}] does not have a class defined.");
    });

    it('throws an exception if driver class does not exist', function (): void {
        // Arrange
        $driverName = 'non_existent_class_driver';
        $nonExistentClass = 'Vendor\NonExistent\DriverClass';
        Config::set("parking-hub.drivers.{$driverName}", ['class' => $nonExistentClass]);

        // Act & Assert
        expect(fn () => $this->manager->driver($driverName))
            ->toThrow(InvalidArgumentException::class, "Driver [{$driverName}] class [{$nonExistentClass}] does not exist.");
    });

    it('throws an exception if driver class does not implement ParkingValidator', function (): void {
        // Arrange
        $driverName = 'invalid_interface_driver';
        // Create a dummy class that does not implement ParkingValidator
        $invalidDriverClass = new class([]) {};
        // Ensure the class is loaded for class_exists to find it
        $invalidDriverClassName = $invalidDriverClass::class;

        Config::set("parking-hub.drivers.{$driverName}", ['class' => $invalidDriverClassName]);

        // Act & Assert
        expect(fn () => $this->manager->driver($driverName))
            ->toThrow(InvalidArgumentException::class, "Driver [{$driverName}] class [{$invalidDriverClassName}] does not implement the ParkingValidator contract.");
    });

    it('passes the driver configuration to the driver constructor', function (): void {
        // Arrange
        $driverName = 'configured_driver';
        $configToPass = [
            'class' => TestConfigurableDriver::class,
            'key' => 'value',
            'another_key' => 'another_value',
        ];
        Config::set("parking-hub.drivers.{$driverName}", $configToPass);

        // Act
        $driverInstance = $this->manager->driver($driverName);

        // Assert
        expect($driverInstance)->toBeInstanceOf(TestConfigurableDriver::class)
            ->and($driverInstance->getConfig())->toBe($configToPass);
    });
});

// Dummy class for testing config passing
if (! class_exists('TestConfigurableDriver')) {
    class TestConfigurableDriver implements ParkingValidator
    {
        public function __construct(private readonly array $config) {}

        public function checkPlate(string $plateNumber, ?Carbon\CarbonInterface $verificationDateTime): Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData
        {
            // Dummy implementation
            return new Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData(
                providerInteractionStatus: Oltrematica\ParkingHub\Enums\ProviderInteractionStatus::SUCCESS_OK,
                requestTimestamp: now(),
                rawResponse: null
            );
        }

        public function getConfig(): array
        {
            return $this->config;
        }
    }
}
