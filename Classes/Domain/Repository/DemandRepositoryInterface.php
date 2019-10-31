<?php

namespace Pixelant\PxaDealers\Domain\Repository;

use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Interface DemandRepositoryInterface
 * @package Pixelant\PxaDealers\Domain\Repository
 */
interface DemandRepositoryInterface
{
    public function findDemanded(Demand $demand): QueryResultInterface;
}
