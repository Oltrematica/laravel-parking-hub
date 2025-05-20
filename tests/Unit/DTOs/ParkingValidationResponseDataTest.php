<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Tests\Unit\DTOs;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;
use Oltrematica\ParkingHub\DTOs\ParkingValidationResponseData;
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
