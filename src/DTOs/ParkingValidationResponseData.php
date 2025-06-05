<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\DTOs;

use Carbon\CarbonInterface;
use InvalidArgumentException;
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

    /**
     * @throw InvalidArgumentException if the interaction status is success
     *
     * @return self DTO instance with success status
     */
    public static function buildFailure(
        ProviderInteractionStatus $interactionStatus,
        string $plate,
        CarbonInterface $requestTimestamp,
        ?CarbonInterface $verifcationTimestamp
    ): self {
        if ($interactionStatus->isSuccess()) {
            throw new InvalidArgumentException(
                'Interaction status is success, but the response indicates failure.'
            );
        }

        return new self(
            responseStatus: $interactionStatus,
            plate: $plate,
            requestTimestamp: $requestTimestamp,
            verificationTimestamp: $verifcationTimestamp ?? $requestTimestamp,
            isValid: false,
            parkingEndTime: null,
            purchasedParkings: null
        );
    }

    /**
     * Finds the parking range closest to the given timestamp and calculates any overflow
     *
     * @param  CarbonInterface  $currentTime  timestamp reference
     * @return array{
     *     parking: PurchasedParkingData|null,
     *     duration_minutes: int|null,
     *     overflow_minutes: int|null,
     *     is_expired: bool
     * }
     */
    public function findClosestParkingRange(CarbonInterface $currentTime): array
    {
        if ($this->purchasedParkings === null || $this->purchasedParkings === []) {
            return [
                'parking' => null,
                'duration_minutes' => null,
                'overflow_minutes' => null,
                'is_expired' => false,
            ];
        }

        $closestParking = null;
        $minDistance = PHP_INT_MAX;

        // find the closest purchased parking to verification date (excluding future parkings)
        foreach ($this->purchasedParkings as $parking) {
            // skip parkings that haven't started yet
            if ($currentTime->isBefore($parking->startDateTime)) {
                continue;
            }

            $distance = $this->calculateDistanceFromRange($currentTime, $parking);

            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closestParking = $parking;
            }
        }

        if ($closestParking === null) {
            return [
                'parking' => null,
                'duration_minutes' => null,
                'overflow_minutes' => null,
                'is_expired' => false,
            ];
        }

        $durationMinutes = (int) $closestParking->startDateTime->diffInMinutes($closestParking->endDateTime);

        $overflowMinutes = null;
        $isExpired = false;

        if ($currentTime->isAfter($closestParking->endDateTime)) {
            $overflowMinutes = (int) $closestParking->endDateTime->diffInMinutes($currentTime);
            $isExpired = true;
        } else {
            $overflowMinutes = 0;
        }

        return [
            'parking' => $closestParking,
            'duration_minutes' => $durationMinutes,
            'overflow_minutes' => $overflowMinutes,
            'is_expired' => $isExpired,
        ];
    }

    /**
     * Calculates the minimum distance between a timestamp and a parking range
     */
    private function calculateDistanceFromRange(CarbonInterface $timestamp, PurchasedParkingData $parking): int
    {
        // if timestamp is within the parking range, distance is 0
        if ($timestamp->between($parking->startDateTime, $parking->endDateTime)) {
            return 0;
        }

        // if timestamp is after the parking end, return the distance from end time
        return (int) round($parking->endDateTime->diffInMinutes($timestamp), 0);
    }
}
