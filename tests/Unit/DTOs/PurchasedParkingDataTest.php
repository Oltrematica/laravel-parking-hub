<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Tests\Unit\DTOs;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Oltrematica\ParkingHub\DTOs\PurchasedParkingData;

describe('PurchasedParkingData', function (): void {
    it('can be instantiated with correct properties', function (): void {
        $startTime = Carbon::parse('2023-01-01 08:00:00');
        $endTime = Carbon::parse('2023-01-01 18:00:00');

        $dto = new PurchasedParkingData(
            startDateTime: $startTime,
            endDateTime: $endTime
        );

        expect($dto)->toBeInstanceOf(PurchasedParkingData::class)
            ->and($dto->startDateTime)->toBeInstanceOf(CarbonInterface::class)
            ->and($dto->startDateTime->equalTo($startTime))->toBeTrue()
            ->and($dto->endDateTime)->toBeInstanceOf(CarbonInterface::class)
            ->and($dto->endDateTime->equalTo($endTime))->toBeTrue();
    });
});

