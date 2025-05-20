<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\DTOs;

use Carbon\CarbonInterface;
use Oltrematica\ParkingHub\Enums\ProviderInteractionStatus;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Data;

class ParkingValidationResponseData extends Data
{
    /**
     * @param  ProviderInteractionStatus  $responseStatus  the status of the response from the provider
     * @param  string  $plate  the plate number of the vehicle
     * @param  CarbonInterface  $requestTimestamp  the timestamp of the request
     * @param  CarbonInterface  $verificationTimestamp  the specific date and time for which parking validity was checked.
     * @param  bool  $isValid  true if the parking is valid, false otherwise
     * @param  CarbonInterface|null  $parkingEndTime  the end time of the parking
     * @param  list<PurchasedParkingData>|null  $purchasedParkings  an array of purchased parking data
     */
    public function __construct(
        public ProviderInteractionStatus $responseStatus,
        public string $plate,
        public CarbonInterface $requestTimestamp,
        public CarbonInterface $verificationTimestamp,
        public bool $isValid,
        public ?CarbonInterface $parkingEndTime,
        #[DataCollectionOf(PurchasedParkingData::class)]
        public ?array $purchasedParkings,
    ) {}
}
