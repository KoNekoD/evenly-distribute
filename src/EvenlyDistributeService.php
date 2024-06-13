<?php

declare(strict_types=1);

namespace KoNekoD\EvenlyDistribute;

class EvenlyDistributeService
{
    /**
     * @template uniqueId as int|string
     * @template nonUniqueId as int|string
     * @template poolId as int|string
     * @template T
     *
     * @param array<int,array{poolId: poolId,uniqueEntityId: uniqueId,nonUniqueEntityId: nonUniqueId,entity: T}> $input Input with non-unique entities
     *
     * @return array<poolId, array<int, array{uniqueEntityId: uniqueId,nonUniqueEntityId: nonUniqueId,entity: T}>>
     */
    public static function distributeByPools(
        array $input,
        int $perPoolMin = 5,
        int $perPoolMax = 5000
    ): array {
        if ($input === []) {
            return [];
        }

        /** @var nonUniqueId[] $nonUniqueIds */
        $nonUniqueIds = [];
        /** @var poolId[] $pools */
        $pools = [];
        foreach ($input as $entity) {
            $nonUniqueIds[$entity['nonUniqueEntityId']] = true;
            $pools[$entity['poolId']] = true;
        }

        $nonUniqueIdsCount = count($nonUniqueIds);
        $poolsCount = count($pools);

        $perPoolAverage = (int)ceil($nonUniqueIdsCount / $poolsCount);
        if ($perPoolAverage < $perPoolMin) {
            $perPoolAverage = $perPoolMin;
        }

        if ($perPoolAverage > $perPoolMax) {
            $perPoolAverage = $perPoolMax;
        }

        /** @var array<poolId, array<int, array{
         *     uniqueEntityId: uniqueId,
         *     nonUniqueEntityId: nonUniqueId,
         *     entity: T
         * }>> $result
         */
        $result = [];

        /** @var nonUniqueId[] $nonUniqueIds */
        $nonUniqueIds = [];

        // Pool-related sorting
        /** @var array{
         *     poolId: poolId,
         *     uniqueEntityId: uniqueId,
         *     nonUniqueEntityId: nonUniqueId,
         *     entity: T,
         * } $item
         */
        foreach ($input as $item) {
            /** @var poolId $poolId */
            $poolId = $item['poolId'];
            if (!isset($result[$poolId])) {
                $result[$poolId] = [];
            }

            // If limit is reached, stop adding
            /** @var array<poolId, array<int, array{
             *     uniqueEntityId: uniqueId,
             *     nonUniqueEntityId: nonUniqueId,
             *     entity: T
             * }>> $result
             */
            if (count($result[$poolId]) >= $perPoolAverage) {
                continue;
            }

            $itemAlreadyExists = false;
            $itemAlreadyExistsButWeStealHim = false;

            // Check if item already saved in another pool
            /**
             * @var array<poolId, array<int, array{
             *     uniqueEntityId: uniqueId,
             *     nonUniqueEntityId: nonUniqueId,
             *     entity: T
             * }>> $result
             * @var poolId $resultItemPoolId
             * @var array<int, array{
             *     uniqueEntityId: uniqueId,
             *     nonUniqueEntityId: nonUniqueId,
             *     entity: T
             * }> $resultItemEntities
             */
            foreach ($result as $resultItemPoolId => $resultItemEntities) {
                if ($resultItemPoolId === $poolId) {
                    continue;
                }

                // Check if item already saved
                /**
                 * @var array<int, array{
                 *     uniqueEntityId: uniqueId,
                 *     nonUniqueEntityId: nonUniqueId,
                 *     entity: T
                 * }> $resultItemEntities
                 * @var int $i
                 * @var array{
                 *     uniqueEntityId: uniqueId,
                 *     nonUniqueEntityId: nonUniqueId,
                 *     entity: T
                 * } $resultItemEntity
                 */
                foreach ($resultItemEntities as $i => $resultItemEntity) {
                    // Entity already saved in another pool
                    $resultItemId = $resultItemEntity['nonUniqueEntityId'];
                    $itemId = $item['nonUniqueEntityId'];
                    if ($resultItemId === $itemId) {

                        $anotherPoolCount = count($resultItemEntities);

                        $toMergePoolCount = count($result[$poolId]);

                        /**
                         * Business rule: If my comrade has more work, I'll
                         * take some of his work.
                         */
                        if ($anotherPoolCount > $toMergePoolCount) {
                            /** @var array{
                             *     uniqueEntityId: uniqueId,
                             *     nonUniqueEntityId: nonUniqueId,
                             *     entity: T
                             * } $resultItemEntity
                             */
                            $nonUniqIdToRem =
                                $resultItemEntity['nonUniqueEntityId'];
                            unset($nonUniqueIds[$nonUniqIdToRem]);

                            /** @var array<poolId, array<int, array{
                             *     uniqueEntityId: uniqueId,
                             *     nonUniqueEntityId: nonUniqueId,
                             *     entity: T
                             * }>> $result
                             */
                            unset($result[$resultItemPoolId][$i]);
                            $itemAlreadyExistsButWeStealHim = true;
                            break;
                        }

                        /**
                         * We don't need to add it here, it will be added at
                         * the end, it's just the act of "stealing" a item
                         * from another pool
                         *
                         * Also, if there is no reason to steal then....
                         * heh, let's skip him and not add him to our pool.
                         */
                        $itemAlreadyExists = true;
                        break;
                    }
                }

                if ($itemAlreadyExists || $itemAlreadyExistsButWeStealHim) {
                    break;
                }
            }

            if ($itemAlreadyExistsButWeStealHim || !$itemAlreadyExists) {
                $alreadyAddedByNonUniqueId = in_array(
                    $item['nonUniqueEntityId'],
                    $nonUniqueIds
                );
                if (!$alreadyAddedByNonUniqueId) {
                    /** @var array<poolId, array<int, array{
                     *     uniqueEntityId: uniqueId,
                     *     nonUniqueEntityId: nonUniqueId,
                     *     entity: T
                     * }>> $result
                     */
                    $result[$poolId][] = $item;
                    $nonUniqId = $item['nonUniqueEntityId'];
                    $nonUniqueIds[$nonUniqId] = $nonUniqId;
                }
            }
        }

        return $result;
    }
}
