<?php

namespace Pixelant\PxaDealers\Domain\Repository;

use Pixelant\PxaDealers\Domain\Model\Demand;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AbstractDemandRepository
 * @package Pixelant\PxaDealers\Domain\Repository
 */
abstract class AbstractDemandRepository extends Repository implements DemandRepositoryInterface
{
    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @return void
     */
    protected function setOrdering(QueryInterface $query, Demand $demand)
    {
        switch ($demand->getOrderDirection()) {
            case 'asc':
                $direction = QueryInterface::ORDER_ASCENDING;
                break;
            case 'desc':
                $direction = QueryInterface::ORDER_DESCENDING;
                break;
            default:
                $direction = QueryInterface::ORDER_DESCENDING;
        }

        $query->setOrderings([$demand->getOrderBy() => $direction]);
    }

    /**
     * @param Demand $demand
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findDemanded(Demand $demand)
    {
        $query = $this->createQuery();

        $this->createConstraints($query, $demand);

        $this->setOrdering($query, $demand);

        return $query->execute();
    }


    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @return void
     */
    abstract protected function createConstraints(QueryInterface $query, Demand $demand);
}
