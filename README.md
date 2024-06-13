# Evenly Distribute Service

Universal service for even distribution of anything

## Features

* Sort by distinct by field nonUniqueEntityId
* Distribute evenly by the values defined in pool Id.

* - (strips each element of pool id and builds an array with unique local pool id)

## Usage

```php

// See EvenlyDistributeServiceTest::testExampleWorksFine

$input = [
        ['poolId' => 'A', 'uniqueEntityId' => '123', 'nonUniqueEntityId' => 1, 'entity' => ['id' => 123]],
        ['poolId' => 'B', 'uniqueEntityId' => '456', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 456]],
        ['poolId' => 'B', 'uniqueEntityId' => '678', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 678]],
];

$output = EvenlyDistributeService::distributeByPools($input);

$excepted = [
    'A' => [
        ['poolId' => 'A', 'uniqueEntityId' => '123', 'nonUniqueEntityId' => 1, 'entity' => ['id' => 123]]
    ],
    'B' => [
        ['poolId' => 'B', 'uniqueEntityId' => '456', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 456]]
    ],
];

echo $excepted === $output;

```

## Testing

```shell

# Run phpunit
vendor/bin/phpunit tests

# PhpStan
composer run phpstan

```
