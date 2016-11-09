<?php


namespace Pixelant\PxaDealers\Domain\Repository;


use Pixelant\PxaDealers\Domain\Model\Demand;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Class AbstractDemandRepository
 * @package Pixelant\PxaDealers\Domain\Repository
 */
abstract class AbstractDemandRepository extends Repository {

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @return array
     */
    abstract protected function createConstraints(QueryInterface $query, Demand $demand);

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @return void
     */
    abstract protected function setOrdering(QueryInterface $query, Demand $demand);

    /**
     * @param Demand $demand
     * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     */
    public function findDemanded(Demand $demand) {
        $query = $this->createQuery();

        if($constraints = $this->createConstraints($query, $demand)) {
            $query->matching($query->logicalAnd($constraints));
        }

        $this->setOrdering($query, $demand);

        return $query->execute();
    }
}