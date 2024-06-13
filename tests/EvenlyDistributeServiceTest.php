<?php

declare(strict_types=1);

namespace App\Tests;

use KoNekoD\EvenlyDistribute\EvenlyDistributeService;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class EvenlyDistributeServiceTest
    extends TestCase
{
    use MatchesSnapshots;

    public function testEmptyInput(): void
    {
        /** @var array<int,array{poolId: int|string,uniqueEntityId: int|string,nonUniqueEntityId: int|string,entity: object}> $input */
        $input = [];

        $result = EvenlyDistributeService::distributeByPools($input);

        $this->assertMatchesJsonSnapshot($result);
    }

    public function testOk(): void
    {
        $inputArray = [];

        $autoIncrementNonUniqueId = 1;
        mt_srand(11122242333);

        $pools = ['A', 'B', 'C'];
        foreach (Helpers::GetManyUlids() as $ulid) {
            $pool = $pools[mt_rand(0, count($pools) - 1)];

            $inputArray[] = [
                'poolId' => $pool,
                'uniqueEntityId' => $ulid,
                'nonUniqueEntityId' => $autoIncrementNonUniqueId,
                'entity' => (object)['uniqueEntityId' => $ulid, 'nonUniqueEntityId' => $autoIncrementNonUniqueId],
            ];

            if (0 === mt_rand(0, 3)) {
                $autoIncrementNonUniqueId++;
            }
        }

        $this->assertMatchesJsonSnapshot($inputArray);

        $result = EvenlyDistributeService::distributeByPools($inputArray);

        $this->assertMatchesJsonSnapshot($result);

        $nonUniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['nonUniqueEntityId'], $nonUniqResultIds);
                $nonUniqResultIds[] = $poolItem['nonUniqueEntityId'];
            }
        }

        $uniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['uniqueEntityId'], $uniqResultIds);
                $uniqResultIds[] = $poolItem['uniqueEntityId'];
            }
        }

        $this->assertTrue(count($result) === count($pools));

        $counts = [];
        foreach ($result as $pool => $poolItems) {
            $counts[$pool] = count($poolItems);
        }

        $this->assertMatchesJsonSnapshot($counts);
    }

    public function testWithLimits(): void
    {
        $inputArray = [];

        $autoIncrementNonUniqueId = 1;
        mt_srand(11121162333);

        $pools = ['A', 'B', 'C'];
        foreach (Helpers::GetManyUlids() as $ulid) {
            $pool = $pools[mt_rand(0, count($pools) - 1)];

            $inputArray[] = [
                'poolId' => $pool,
                'uniqueEntityId' => $ulid,
                'nonUniqueEntityId' => $autoIncrementNonUniqueId,
                'entity' => (object)['uniqueEntityId' => $ulid, 'nonUniqueEntityId' => $autoIncrementNonUniqueId],
            ];

            if (0 === mt_rand(0, 2)) {
                $autoIncrementNonUniqueId++;
            }
        }

        $this->assertMatchesJsonSnapshot($inputArray);

        $result = EvenlyDistributeService::distributeByPools($inputArray, 10, 20);
        $this->assertMatchesJsonSnapshot($result);

        $nonUniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['nonUniqueEntityId'], $nonUniqResultIds);
                $nonUniqResultIds[] = $poolItem['nonUniqueEntityId'];
            }
        }

        $uniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['uniqueEntityId'], $uniqResultIds);
                $uniqResultIds[] = $poolItem['uniqueEntityId'];
            }
        }

        $this->assertTrue(count($result) === count($pools));

        $counts = [];
        foreach ($result as $pool => $poolItems) {
            $counts[$pool] = count($poolItems);
        }

        $this->assertMatchesJsonSnapshot($counts);
    }

    public function testWithBigLimits(): void
    {
        $inputArray = [];

        $autoIncrementNonUniqueId = 1;
        mt_srand(11121162333);

        $pools = ['A', 'B', 'C'];
        foreach (Helpers::GetManyUlids() as $ulid) {
            $pool = $pools[mt_rand(0, count($pools) - 1)];

            $inputArray[] = [
                'poolId' => $pool,
                'uniqueEntityId' => $ulid,
                'nonUniqueEntityId' => $autoIncrementNonUniqueId,
                'entity' => (object)['uniqueEntityId' => $ulid, 'nonUniqueEntityId' => $autoIncrementNonUniqueId],
            ];

            if (0 === mt_rand(0, 2)) {
                $autoIncrementNonUniqueId++;
            }
        }

        $this->assertMatchesJsonSnapshot($inputArray);

        $result = EvenlyDistributeService::distributeByPools($inputArray, 100, 200);
        $this->assertMatchesJsonSnapshot($result);

        $nonUniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['nonUniqueEntityId'], $nonUniqResultIds);
                $nonUniqResultIds[] = $poolItem['nonUniqueEntityId'];
            }
        }

        $uniqResultIds = [];
        foreach ($result as $poolItems) {
            foreach ($poolItems as $poolItem) {
                $this->assertNotContains($poolItem['uniqueEntityId'], $uniqResultIds);
                $uniqResultIds[] = $poolItem['uniqueEntityId'];
            }
        }

        $this->assertTrue(count($result) === count($pools));

        $counts = [];
        foreach ($result as $pool => $poolItems) {
            $counts[$pool] = count($poolItems);
        }

        $this->assertMatchesJsonSnapshot($counts);
    }

    public function testExampleWorksFine(): void
    {
        $input = [
            ['poolId' => 'A', 'uniqueEntityId' => '123', 'nonUniqueEntityId' => 1, 'entity' => ['id' => 123]],
            ['poolId' => 'B', 'uniqueEntityId' => '456', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 456]],
            ['poolId' => 'B', 'uniqueEntityId' => '678', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 678]],
        ];

        $output = EvenlyDistributeService::distributeByPools($input);

        $excepted = [
            'A' => [
                ['poolId' => 'A', 'uniqueEntityId' => '123', 'nonUniqueEntityId' => 1, 'entity' => ['id' => 123]],
            ],
            'B' => [
                ['poolId' => 'B', 'uniqueEntityId' => '456', 'nonUniqueEntityId' => 2, 'entity' => ['id' => 456]],
            ],
        ];

        $this->assertSame($excepted, $output);
    }
}
