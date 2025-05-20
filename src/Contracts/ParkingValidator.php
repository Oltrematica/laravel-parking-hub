<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Contracts;

use Carbon\CarbonInterface;
use Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData;

/**
 * Interface ParkingValidatorInterface
 *
 * Defines the contract for a parking validation service provider.
 * Each specific parking provider (EasyPark, MyCicero, etc.)
 * will implement this interface.
 */
interface ParkingValidator
{
    /**
     * Checks the parking status for a given license plate at a specific verification time.
     *
     * This method is responsible for:
     * 1. Communicating with the specific parking provider's API.
     * 2. Handling authentication and API-specific request/response formats.
     * 3. Mapping the provider's response to the standardized ParkingValidationResponseData DTO.
     * 4. Setting the 'requestTimestamp' in the DTO to the moment this check begins processing.
     *
     * @param  string  $plateNumber  the license plate number to check
     * @param  CarbonInterface  $verificationDateTime  the date and time for which the parking validity is checked
     */
    public function checkPlate(
        string $plateNumber,
        CarbonInterface $verificationDateTime
    ): ParkingValidationResponseData;
}
