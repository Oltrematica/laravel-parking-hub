<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Parking Driver
    |--------------------------------------------------------------------------
    |
    | This option controls the default parking driver that will be used
    | when no driver is specified when calling the ParkingHub facade.
    |
    */
    'default_driver' => env('PARKING_HUB_DEFAULT_DRIVER', 'easypark'), // Example

    /*
    |--------------------------------------------------------------------------
    | Parking Drivers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the drivers for interfacing with various
    | parking service providers. Each driver needs its own configuration,
    | including the 'class' that implements ParkingValidatorInterface.
    |
    */
    'drivers' => [

        'easypark' => [
            'class' => YourVendor\LaravelEasyPark\EasyParkValidator::class, // Implements ParkingValidator
            'api_url' => env('EASYPARK_API_URL', 'https://cityname.parkinghub.net/restresources'),
            'username' => env('EASYPARK_USERNAME'),
            'password' => env('EASYPARK_PASSWORD'),
            // other configuration options specific to EasyPark
            // e.g., 'timeout', 'retry', etc.
            // 'http' => [
            //     'timeout' => env('EASYPARK_HTTP_TIMEOUT', 5),
            //     'retry' => env('EASYPARK_HTTP_RETRY', 3),
            //     'retry_sleep' => env('EASYPARK_HTTP_RETRY_SLEEP', 1000),
            // ],
        ],

        'mycicero' => [
            'class' => YourVendor\LaravelMyCicero\MyCiceroValidator::class, // Replace with actual class
            'username' => env('MYCICERO_USERNAME'),
            'password' => env('MYCICERO_PASSWORD'),
            'soap' => [
                'endpoint' => env('MYCICERO_SOAP_ENDPOINT',
                    'https://cldweb.autobus.it/proxy.imomo/api/wsisosta_verifica'),
                'namespace' => env('MYCICERO_SOAP_NAMESPACE', 'http://pluservice.net/ISosta'),
                'debug' => env('MYCICERO_SOAP_DEBUG', 0),
                'check_plate_action' => env('MYCICERO_SOAP_CHECK_PLATE_ACTION',
                    'http://pluservice.net/ISosta/IVerificaService/VerificaSosteV2'),
            ],
        ],

        'parkeon' => [
            'class' => Oltrematica\ParkingHub\Validators\ParkeonValidator::class, // Replace with actual class
            'username' => env('PARKEON_USERNAME'),
            'password' => env('PARKEON_PASSWORD'),
            'endpoint' => env('PARKEON_ENDPOINT', 'https://parkeon.services/jlab/rest/1/pbs/getTransactions'),
            // other configuration options specific to Parkeon
            // e.g., 'timeout', 'retry', etc.
            // 'http' => [
            //     'timeout' => env('PARKEON_HTTP_TIMEOUT', 5),
            //     'retry' => env('PARKEON_HTTP_RETRY', 3),
            //     'retry_sleep' => env('PARKEON_HTTP_RETRY_SLEEP', 1000),
            // ],
        ],

        // Add other parking providers here
        // 'provider_name' => [
        //     'class' => \YourVendor\YourPackage\YourValidator::class,
        // ],
    ],

];
