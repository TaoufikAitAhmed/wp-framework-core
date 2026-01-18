<?php

return [
    'vendor_a/package_a' => [
        'providers' => 'FooProvider',
        'aliases' => [
            'Foo' => 'Foo\\Facade',
        ],
    ],
    'vendor_a/package_b' => [
        'providers' => [
            0 => 'BarProvider',
        ],
    ],
];
