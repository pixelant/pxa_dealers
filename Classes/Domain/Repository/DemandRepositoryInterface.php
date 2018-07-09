<?php

namespace Pixelant\PxaDealers\Domain\Repository;

use Pixelant\PxaDealers\Domain\Model\Demand;

/**
 * Interface DemandRepositoryInterface
 * @package Pixelant\PxaDealers\Domain\Repository
 */
interface DemandRepositoryInterface
{
    public function findDemanded(Demand $demand);
}