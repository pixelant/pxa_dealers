<?php

namespace Pixelant\PxaDealers\Domain\Repository;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Andriy <andriy@pixelant.se>, Pixelant
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use Pixelant\PxaDealers\Domain\Model\DTO\Search;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\OrInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DealerRepository extends AbstractDemandRepository
{
    /**
     * @var array
     */
    protected $settings = [];

    public function __construct(ObjectManagerInterface $objectManager)
    {
        parent::__construct($objectManager);

        $configurationManager = $objectManager->get(ConfigurationManager::class);
        $this->settings = $configurationManager->getConfiguration(
            ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS
        );
    }

    /**
     * @param Search $search
     * @return array
     */
    public function suggestResult(Search $search): array
    {
        $query = $this->createQuery();
        $sword = $search->getSearchTermLowercase();

        if ($search->getPid()) {
            $query->getQuerySettings()->setStoragePageIds(
                GeneralUtility::intExplode(',', $search->getPid(), true)
            );
        }

        $constraints = $this->getSearchConstraintsForFields($sword, $search->getSearchFields(), $query);

        if ($constraints === null) {
            return [];
        }

        $dealers = $query->matching($constraints)->execute();

        if ($dealers->count() === 0) {
            return [];
        }

        $suggestionList = [];

        /** @var Dealer $dealer */
        foreach ($dealers as $dealer) {
            $suggestion = $dealer->getZipcode()
                . ' ' . $dealer->getCity()
                . ', ' . $dealer->getCountry()->getShortNameEn();

            // Return empty if we're suggesting based on an exact suggestion string
            if (strcasecmp($suggestion, $sword) === 0) {
                return [];
            }

            $suggestionList[] = $suggestion;
        }

        return array_intersect_key(
            $suggestionList,
            array_unique(array_map('strtolower', $suggestionList), SORT_STRING)
        );
    }

    /**
     *
     *
     * @param string $sword
     * @param array $fields
     * @param QueryInterface $query
     * @return ConstraintInterface|null
     */
    protected function getSearchConstraintsForFields(string $sword, array $fields, QueryInterface $query)
    {
        if (count($fields) === 0) {
            return null;
        }

        if ($this->settings['search']['splitSearchString']) {
            $searchWords = preg_split(
                $this->settings['search']['splitSearchStringRegex'],
                $sword,
                0,
                PREG_SPLIT_NO_EMPTY
            );
        } else {
            $searchWords = [$sword];
        }

        $constraints = [];

        foreach ($searchWords as $searchWord) {
            foreach ($fields as $field) {
                switch ($field) {
                    case 'zipcode':
                        $constraints[] = $this->getZipcodeSuggestConstraint($searchWord, $query);
                        break;
                    default:
                        $constraints[] = $this->getDefaultSuggestConstraint($searchWord, $field, $query);
                        break;
                }
            }
        }

        return $query->logicalOr($constraints);
    }

    /**
     * Adds a search constraint on field and search word.
     *
     * @param string $sword
     * @param string $field
     * @param QueryInterface $query
     * @return ComparisonInterface
     */
    protected function getDefaultSuggestConstraint(string $sword, string $field, QueryInterface $query)
    {
        return $query->like($field, '%' . $sword . '%');
    }

    /**
     * Adds a search constraint for zip codes
     *
     * @param string $zipcode
     * @param QueryInterface $query
     * @return ComparisonInterface
     */
    protected function getZipcodeSuggestConstraint(string $zipcode, QueryInterface $query)
    {
        $field = 'zipcode';

        if ((int)$this->settings['search']['zipcodeInexactness'] > 0) {
            $zipcode = substr($zipcode, 0, $this->settings['search']['zipcodeInexactness'] * -1);
        }

        return $query->like(
            $field,
            str_pad(
                $zipcode,
                mb_strlen($zipcode) + (int)$this->settings['search']['zipcodeInexactness'],
                '_',
                STR_PAD_RIGHT
            )
        );
    }

    /**
     * Check for storage
     *
     * @return array
     */
    public function getStoragePageIds(): array
    {
        $query = $this->createQuery();
        return $query->getQuerySettings()->getStoragePageIds();
    }

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @param bool $secondaryFields If true, use secondary fields
     * @return void
     */
    protected function createConstraints(QueryInterface $query, Demand $demand, bool $secondaryFields = false): void
    {
        // If search by radius just create a query
        if (!$secondaryFields && $demand->getSearch() !== null && $demand->getSearch()->isSearchInRadius()) {
            $storage = $query->getQuerySettings()->getStoragePageIds();

            $statement = sprintf(
                'SELECT *, ( 6371 * acos( cos( radians(\'%s\') ) * cos( radians( lat ) ) * cos( radians( lng ) - radians(\'%s\') ) + sin( radians(\'%s\') ) * sin( radians( lat ) ) ) ) AS distance FROM tx_pxadealers_domain_model_dealer %s %s HAVING distance < \'%s\' ORDER BY distance',
                (float)$demand->getSearch()->getLat(),
                (float)$demand->getSearch()->getLng(),
                (float)$demand->getSearch()->getLat(),
                'WHERE ' . (empty($storage) ? '1=1' : ('pid IN(' . implode(',', $storage) . ')')),
                MainUtility::getTSFE()->cObj->enableFields('tx_pxadealers_domain_model_dealer'),
                (int)$demand->getSearch()->getRadius()
            );
            $query->statement($statement);
        } else {
            $constraintsAnd = [];
            $constraintsOr = [];
            $constraints = [];

            // set country restriction
            if (!empty($demand->getCountries())) {
                $constraintsAnd[] = $query->in('country', $demand->getCountries());
            }

            // set categories restriction
            if (!empty($demand->getCategories())) {
                $constraintsAnd[] = $query->contains('categories', $demand->getCategories());
            }

            if ($demand->getSearch() !== null) {
                $constraintsAnd[] = $this->getSearchConstraintsForFields(
                    $demand->getSearch()->getSearchTermLowercase(),
                    $demand->getSearch()->getSearchFields(),
                    $query
                );
            }

            if (!empty($constraintsAnd)) {
                $constraints = array_merge($constraints, $constraintsAnd);
            }

            if (!empty($constraintsOr)) {
                $constraints[] = $query->logicalOr($constraintsOr);
            }

            if (count($constraints) > 1) {
                $query->matching(
                    $query->logicalAnd($constraints)
                );
            } elseif (count($constraints) === 1) {
                $query->matching($constraints[0]);
            }
        }
    }

    /**
     * Set orderings
     *
     * @param QueryInterface $query
     * @param Demand $demand
     */
    protected function setOrdering(QueryInterface $query, Demand $demand): void
    {
        // Set orderings only in case of default search
        if ($demand->getSearch() === null || !$demand->getSearch()->isSearchInRadius()) {
            parent::setOrdering($query, $demand);
        }
    }
}
