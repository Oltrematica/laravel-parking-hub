<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Tests\Unit\DTOs;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData;
use Oltrematica\ParkingHub\DTOs\PurchasedParkingData;
use Oltrematica\ParkingHub\Enums\ProviderInteractionStatus;

describe('ParkingValidationResponseData - buildFailure', function (): void {
    it('throws InvalidArgumentException if interaction status is success', function (ProviderInteractionStatus $successStatus): void {
        $plate = 'AA123BB';
        $requestTimestamp = Carbon::now();

        expect(fn (): ParkingValidationResponseData => ParkingValidationResponseData::buildFailure($successStatus, $plate, $requestTimestamp, null))
            ->toThrow(InvalidArgumentException::class, 'Interaction status is success, but the response indicates failure.');
    })->with([
        'SUCCESS_OK' => ProviderInteractionStatus::SUCCESS_OK,
        'SUCCESS_PLATE_NOT_FOUND' => ProviderInteractionStatus::SUCCESS_PLATE_NOT_FOUND,
    ]);

    it('builds a failure DTO correctly when interaction status is an error', function (ProviderInteractionStatus $errorStatus): void {
        $plate = 'AA123BB';
        $requestTimestamp = Carbon::parse('2023-01-01 10:00:00');
        $verificationTimestamp = Carbon::parse('2023-01-01 10:05:00');

        $dto = ParkingValidationResponseData::buildFailure($errorStatus, $plate, $requestTimestamp, $verificationTimestamp);

        expect($dto)->toBeInstanceOf(ParkingValidationResponseData::class)
            ->and($dto->responseStatus)->toBe($errorStatus)
            ->and($dto->plate)->toBe($plate)
            ->and($dto->requestTimestamp)->toBeInstanceOf(CarbonInterface::class)
            ->and($dto->requestTimestamp->equalTo($requestTimestamp))->toBeTrue()
            ->and($dto->verificationTimestamp)->toBeInstanceOf(CarbonInterface::class)
            ->and($dto->verificationTimestamp->equalTo($verificationTimestamp))->toBeTrue()
            ->and($dto->isValid)->toBeFalse()
            ->and($dto->parkingEndTime)->toBeNull()
            ->and($dto->purchasedParkings)->toBeNull();
    })->with([
        'ERROR_PROVIDER_UNAVAILABLE' => ProviderInteractionStatus::ERROR_PROVIDER_UNAVAILABLE,
        'ERROR_PROVIDER_AUTHENTICATION' => ProviderInteractionStatus::ERROR_PROVIDER_AUTHENTICATION,
        'ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER' => ProviderInteractionStatus::ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER,
        'ERROR_PROVIDER_BAD_REQUEST' => ProviderInteractionStatus::ERROR_PROVIDER_BAD_REQUEST,
        'ERROR_PROVIDER_CONFIGURATION' => ProviderInteractionStatus::ERROR_PROVIDER_CONFIGURATION,
        'ERROR_CONNECTION_TIMEOUT' => ProviderInteractionStatus::ERROR_CONNECTION_TIMEOUT,
        'ERROR_INVALID_RESPONSE' => ProviderInteractionStatus::ERROR_INVALID_RESPONSE,
        'ERROR_PROVIDER_UNKNOWN' => ProviderInteractionStatus::ERROR_PROVIDER_UNKNOWN,
    ]);

    it('builds a failure DTO with verificationTimestamp defaulting to requestTimestamp if null', function (): void {
        $plate = 'CC456DD';
        $requestTimestamp = Carbon::parse('2023-02-01 12:00:00');
        $errorStatus = ProviderInteractionStatus::ERROR_PROVIDER_UNAVAILABLE;

        $dto = ParkingValidationResponseData::buildFailure($errorStatus, $plate, $requestTimestamp, null);

        expect($dto->verificationTimestamp)->toBeInstanceOf(CarbonInterface::class)
            ->and($dto->verificationTimestamp->equalTo($requestTimestamp))->toBeTrue();
    });
});

describe('ParkingValidationResponseData - findClosestParkingRange', function (): void {
    it('returns null values when no purchased parkings exist', function (): void {
        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: false,
            parkingEndTime: null,
            purchasedParkings: null
        );

        $result = $dto->findClosestParkingRange(Carbon::now());

        expect($result)->toBe([
            'parking' => null,
            'duration_minutes' => null,
            'overflow_minutes' => null,
            'is_expired' => false,
        ]);
    });

    it('returns null values when purchased parkings array is empty', function (): void {
        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: false,
            parkingEndTime: null,
            purchasedParkings: []
        );

        $result = $dto->findClosestParkingRange(Carbon::now());

        expect($result)->toBe([
            'parking' => null,
            'duration_minutes' => null,
            'overflow_minutes' => null,
            'is_expired' => false,
        ]);
    });

    it('finds active parking when current time is within parking range', function (): void {
        $startTime = Carbon::parse('2025-06-05 10:00:00');
        $endTime = Carbon::parse('2025-06-05 12:00:00');
        $currentTime = Carbon::parse('2025-06-05 11:00:00');

        $parking = new PurchasedParkingData($startTime, $endTime);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: true,
            parkingEndTime: $endTime,
            purchasedParkings: [$parking]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        expect($result['parking'])->toBe($parking)
            ->and($result['duration_minutes'])->toBe(120) // 2 hours
            ->and($result['overflow_minutes'])->toBe(0)
            ->and($result['is_expired'])->toBeFalse();
    });

    it('finds expired parking and calculates overflow minutes', function (): void {
        $startTime = Carbon::parse('2025-06-05 10:00:00');
        $endTime = Carbon::parse('2025-06-05 12:00:00');
        $currentTime = Carbon::parse('2025-06-05 12:30:00'); // 30 minutes after end

        $parking = new PurchasedParkingData($startTime, $endTime);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: false,
            parkingEndTime: $endTime,
            purchasedParkings: [$parking]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        expect($result['parking'])->toBe($parking)
            ->and($result['duration_minutes'])->toBe(120) // 2 hours
            ->and($result['overflow_minutes'])->toBe(30) // 30 minutes overflow
            ->and($result['is_expired'])->toBeTrue();
    });

    it('excludes future parkings and returns null when no valid parkings exist', function (): void {
        $futureStartTime = Carbon::parse('2025-06-05 14:00:00');
        $futureEndTime = Carbon::parse('2025-06-05 16:00:00');
        $currentTime = Carbon::parse('2025-06-05 10:00:00'); // before parking starts

        $parking = new PurchasedParkingData($futureStartTime, $futureEndTime);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: false,
            parkingEndTime: null,
            purchasedParkings: [$parking]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        expect($result)->toBe([
            'parking' => null,
            'duration_minutes' => null,
            'overflow_minutes' => null,
            'is_expired' => false,
        ]);
    });

    it('finds closest parking among multiple parkings', function (): void {
        $parking1Start = Carbon::parse('2025-06-05 08:00:00');
        $parking1End = Carbon::parse('2025-06-05 10:00:00');

        $parking2Start = Carbon::parse('2025-06-05 11:00:00');
        $parking2End = Carbon::parse('2025-06-05 13:00:00');

        $parking3Start = Carbon::parse('2025-06-05 14:00:00');
        $parking3End = Carbon::parse('2025-06-05 16:00:00');

        $currentTime = Carbon::parse('2025-06-05 13:30:00'); // 30 minutes after parking2 ends

        $parking1 = new PurchasedParkingData($parking1Start, $parking1End);
        $parking2 = new PurchasedParkingData($parking2Start, $parking2End);
        $parking3 = new PurchasedParkingData($parking3Start, $parking3End);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: false,
            parkingEndTime: null,
            purchasedParkings: [$parking1, $parking2, $parking3]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        // Should find parking2 as it's the closest (30 minutes ago)
        expect($result['parking'])->toBe($parking2)
            ->and($result['duration_minutes'])->toBe(120) // 2 hours
            ->and($result['overflow_minutes'])->toBe(30) // 30 minutes overflow
            ->and($result['is_expired'])->toBeTrue();
    });

    it('handles edge case with parking ending exactly at current time', function (): void {
        $startTime = Carbon::parse('2025-06-05 10:00:00');
        $endTime = Carbon::parse('2025-06-05 12:00:00');
        $currentTime = Carbon::parse('2025-06-05 12:00:00'); // exactly at end time

        $parking = new PurchasedParkingData($startTime, $endTime);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: true,
            parkingEndTime: $endTime,
            purchasedParkings: [$parking]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        expect($result['parking'])->toBe($parking)
            ->and($result['duration_minutes'])->toBe(120)
            ->and($result['overflow_minutes'])->toBe(0) // exactly at end, so no overflow
            ->and($result['is_expired'])->toBeFalse();
    });

    it('handles parking starting exactly at current time', function (): void {
        $startTime = Carbon::parse('2025-06-05 10:00:00');
        $endTime = Carbon::parse('2025-06-05 12:00:00');
        $currentTime = Carbon::parse('2025-06-05 10:00:00'); // exactly at start time

        $parking = new PurchasedParkingData($startTime, $endTime);

        $dto = new ParkingValidationResponseData(
            responseStatus: ProviderInteractionStatus::SUCCESS_OK,
            plate: 'AA123BB',
            requestTimestamp: Carbon::now(),
            verificationTimestamp: Carbon::now(),
            isValid: true,
            parkingEndTime: $endTime,
            purchasedParkings: [$parking]
        );

        $result = $dto->findClosestParkingRange($currentTime);

        expect($result['parking'])->toBe($parking)
            ->and($result['duration_minutes'])->toBe(120)
            ->and($result['overflow_minutes'])->toBe(0)
            ->and($result['is_expired'])->toBeFalse();
    });
});
