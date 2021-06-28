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

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\DTO\Demand;
use Pixelant\PxaDealers\Domain\Model\DTO\Search;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ComparisonInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Qom\ConstraintInterface;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class DealerRepository extends AbstractDemandRepository
{
    protected const COUNTRY_TABLE = 'static_countries';

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

        $limitToCountries = $this->getLimitToCountries('uid');
        if (count($limitToCountries) > 0) {
            $constraints = $query->logicalAnd($constraints, $query->in('country', $limitToCountries));
        }

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

            if ($this->settings['search']['joinSearchStringRegex']) {
                $joinSearchStringPattern = $this->settings['search']['joinSearchStringRegex'];

                for ($i = 0; $i - 1 < count($searchWords); $i++) {
                    if (
                        preg_match($joinSearchStringPattern, $searchWords[$i]) === 1
                        && preg_match($joinSearchStringPattern, $searchWords[$i + 1]) === 1
                    ) {
                        $searchWords[$i] = $searchWords[$i] . $searchWords[$i + 1];
                        unset($searchWords[$i + 1]);
                    }
                }
            }
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
        $storagePageIds = $query->getQuerySettings()->getStoragePageIds();
        return $storagePageIds;
    }

    /**
     * Returns a unique values from a specific field from the countries of the dealers.
     *
     * By default, this function fetches the unique country uids for all the dealers.
     *
     * @param string $field The field from the static_countries table to return.
     * @param bool $useLimitedCountries Limit results to countries in setting search.limitToCountries (if set).
     * @return array
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function getUniqueCountryFieldValues(string $field = 'uid', bool $useLimitedCountries = true): array
    {
        $dataMapper = GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class);

        $dealerTable = $dataMapper->convertClassNameToTableName(Dealer::class);

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($dealerTable);

        $fullFieldName = 'c.' . $field;

        $queryBuilder
            ->select($fullFieldName)
            ->from($dealerTable, 'd')
            ->join(
                'd',
                self::COUNTRY_TABLE,
                'c',
                $queryBuilder->expr()->eq(
                    'd.country',
                    $queryBuilder->quoteIdentifier('c.uid')
                )
            )
            ->where($queryBuilder->expr()->in('d.pid', $this->getStoragePageIds()));

        if ($useLimitedCountries) {
            $limitToCountries = GeneralUtility::trimExplode(
                ',',
                strtoupper((string)$this->settings['search']['limitToCountries']),
                true
            );

            if (count($limitToCountries) > 0) {
                array_walk(
                    $limitToCountries,
                    function (&$value) use ($queryBuilder) {
                        $value = $queryBuilder->createNamedParameter($value);
                    }
                );

                $queryBuilder->andWhere($queryBuilder->expr()->in(
                    'c.cn_iso_2',
                    $limitToCountries
                ));
            }
        }

        $fieldValues = $queryBuilder
            ->groupBy($fullFieldName)
            ->execute()
            ->fetchFirstColumn();

        return $fieldValues ?: [];
    }


    /**
     * The an array of countries any search is limited to.
     *
     * Combines setting search.limitToCountries with available dealer countries.
     *
     * @param string $returnField The field from the static_countries table to return.
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public function getLimitToCountries(string $returnField = 'cn_iso_2'): array
    {
        $limitToCountries = GeneralUtility::trimExplode(
            ',',
            strtoupper((string)$this->settings['search']['limitToCountries']),
            true
        );

        if (count($limitToCountries) > 0) {
            $limitToCountries = array_intersect(
                $limitToCountries,
                $this->getUniqueCountryFieldValues('cn_iso_2')
            );

            if (count($limitToCountries) > 0 && $returnField !== 'cn_iso_2') {
                /** @var QueryBuilder $countryQuery */
                $countryQuery = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable(self::COUNTRY_TABLE);

                array_walk(
                    $limitToCountries,
                    function (&$value) use ($countryQuery) {
                        $value = $countryQuery->createNamedParameter($value);
                    }
                );

                $limitToCountries = $countryQuery
                    ->select($returnField)
                    ->from(self::COUNTRY_TABLE)
                    ->where($countryQuery->expr()->in('cn_iso_2', $limitToCountries))
                    ->execute()
                    ->fetchFirstColumn();
            }
        }

        return $limitToCountries;
    }

    /**
     * @param QueryInterface $query
     * @param Demand $demand
     * @param bool $secondaryFields If true, use secondary fields
     * @return void
     */
    protected function createConstraints(QueryInterface $query, Demand $demand, bool $secondaryFields = false): void
    {
        $limitToCountries = $this->getLimitToCountries('uid');

        // If search by radius just create a query
        if (!$secondaryFields && $demand->getSearch() !== null && $demand->getSearch()->isSearchInRadius()) {
            // distance in kilometers = 6371, miles = 3959
            $multiplier = '6371';
            $storage = $query->getQuerySettings()->getStoragePageIds();
            $selectLiteral = sprintf(
                '(%s * acos(' .
                'cos(radians(\'%s\'))' .
                ' * cos(radians(lat))' .
                ' * cos(radians(lng) - radians(\'%s\'))' .
                ' + sin(radians(\'%s\'))' .
                ' * sin(radians(lat))' .
                ')) as distance',
                $multiplier,
                (float)$demand->getSearch()->getLat(),
                (float)$demand->getSearch()->getLng(),
                (float)$demand->getSearch()->getLat()
            );

            /** @var QueryBuilder $queryBuilder */
            $queryBuilder= GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_pxadealers_domain_model_dealer');
            $queryBuilder->select('*')
                ->addSelectLiteral($selectLiteral)
                ->from('tx_pxadealers_domain_model_dealer')
                ->where(
                    $queryBuilder->expr()->in(
                        'pid',
                        $queryBuilder->createNamedParameter(
                            $storage,
                            \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY
                        )
                    )
                )
                ->having(
                    $queryBuilder->expr()->lt(
                        'distance',
                        $queryBuilder->createNamedParameter($demand->getSearch()->getRadius(), \PDO::PARAM_INT)
                    )
                )
                ->orderBy('distance')
                ->setMaxResults(25);

            if (count($limitToCountries) > 0) {
                $queryBuilder->andWhere(
                    $queryBuilder->expr()->in('country', $limitToCountries)
                );
            }

            // TODO: Replace this with less crazy code.
            $sql = $queryBuilder->getSQL();
            $parameters = $queryBuilder->getParameters();
            foreach ($parameters as $key => $parameter) {
                switch ($queryBuilder->getParameterType($key)) {
                    case 1:
                        $stringParams[':' . $key] = (int)$parameter;
                        break;
                    case 101:
                        $stringParams[':' . $key] = implode(',', $parameter);
                        break;
                    default:
                        $stringParams[':' . $key] = $queryBuilder->quote($parameter);
                        break;
                }
            }
            $statement = strtr($sql, $stringParams);

            $query->statement($statement);
        } else {
            $constraintsAnd = [];
            $constraintsOr = [];
            $constraints = [];

            // set country restriction
            if (!empty($demand->getCountries())) {
                $constraintsAnd[] = $query->in('country', $demand->getCountries());
            }

            if (count($limitToCountries) > 0) {
                $constraintsAnd[] = $query->in('country', $limitToCountries);
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
