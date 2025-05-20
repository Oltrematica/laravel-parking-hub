<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub\Enums;

enum ProviderInteractionStatus: string
{
    case SUCCESS_OK = 'SUCCESS_OK';
    case SUCCESS_PLATE_NOT_FOUND = 'SUCCESS_PLATE_NOT_FOUND';
    case ERROR_PROVIDER_UNAVAILABLE = 'ERROR_PROVIDER_UNAVAILABLE';
    case ERROR_PROVIDER_AUTHENTICATION = 'ERROR_PROVIDER_AUTHENTICATION';
    case ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER = 'ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER';
    case ERROR_PROVIDER_BAD_REQUEST = 'ERROR_PROVIDER_BAD_REQUEST';
    case ERROR_PROVIDER_CONFIGURATION = 'ERROR_PROVIDER_CONFIGURATION';
    case ERROR_CONNECTION_TIMEOUT = 'ERROR_CONNECTION_TIMEOUT';
    case ERROR_INVALID_RESPONSE = 'ERROR_INVALID_RESPONSE';
    case ERROR_PROVIDER_UNKNOWN = 'ERROR_PROVIDER_UNKNOWN';

    public function getDescription(): string
    {
        return match ($this) {
            self::SUCCESS_OK => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.SUCCESS_OK'),
            self::SUCCESS_PLATE_NOT_FOUND => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.SUCCESS_PLATE_NOT_FOUND'),
            self::ERROR_PROVIDER_UNAVAILABLE => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_PROVIDER_UNAVAILABLE'),
            self::ERROR_PROVIDER_AUTHENTICATION => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_PROVIDER_AUTHENTICATION'),
            self::ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER'),
            self::ERROR_PROVIDER_BAD_REQUEST => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_PROVIDER_BAD_REQUEST'),
            self::ERROR_PROVIDER_CONFIGURATION => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_PROVIDER_CONFIGURATION'),
            self::ERROR_CONNECTION_TIMEOUT => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_CONNECTION_TIMEOUT'),
            self::ERROR_INVALID_RESPONSE => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_INVALID_RESPONSE'),
            self::ERROR_PROVIDER_UNKNOWN => __('oltrematica-parking-hub::parking-hub.provider-interaction-status.ERROR_PROVIDER_UNKNOWN'),
        };
    }

    public function isSuccess(): bool
    {
        return match ($this) {
            self::SUCCESS_OK, self::SUCCESS_PLATE_NOT_FOUND => true,
            default => false,
        };
    }

    public function isError(): bool
    {
        return match ($this) {
            self::ERROR_PROVIDER_UNAVAILABLE,
            self::ERROR_PROVIDER_AUTHENTICATION,
            self::ERROR_INVALID_PLATE_FORMAT_FOR_PROVIDER,
            self::ERROR_PROVIDER_BAD_REQUEST,
            self::ERROR_PROVIDER_CONFIGURATION,
            self::ERROR_CONNECTION_TIMEOUT,
            self::ERROR_INVALID_RESPONSE,
            self::ERROR_PROVIDER_UNKNOWN => true,
            default => false,
        };
    }
}
