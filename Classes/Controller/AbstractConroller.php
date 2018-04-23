<?php

namespace Pixelant\PxaDealers\Controller;

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\Demand;
use Pixelant\PxaDealers\Domain\Model\Search;
use Pixelant\PxaDealers\Utility\GoogleApiUtility;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractConroller
 * @package Pixelant\PxaDealers\Controller
 */
abstract class AbstractConroller extends ActionController
{
    /**
     *  dealer repository
     *
     * @var \Pixelant\PxaDealers\Domain\Repository\DealerRepository
     * @inject
     */
    protected $dealerRepository = null;

    /**
     * Render map data
     *
     * @param Search|null $search
     */
    public function renderMap(Search $search = null)
    {
        $dealers = [];

        $demand = Demand::getInstance($this->settings['demand']);

        if ($search !== null) {
            $search->setSearchFields(GeneralUtility::trimExplode(
                ',',
                $this->settings['search']['searchFields'],
                true
            ));
            $demand->setSeach($search);

            if ($search->isSearchInRadius() && !empty($this->settings['map']['googleServerApiKey'])) {
                if (empty($search->getLat()) || empty($search->getLng())) {
                    // Get from address
                    list($lat, $lng) = $this->getAddressInfo($search->getSearchTermOriginal());
                } else {
                    // Use user position
                    $lat = $search->getLat();
                    $lng = $search->getLng();
                }

                if ($lat && $lng) {
                    $search->setLat($lat);
                    $search->setLng($lng);
                    $search->setRadius(intval($this->settings['search']['radius']) ?: 100);

                    $this->view->assign(
                        'searchCenter',
                        [
                            'lat' => $lat,
                            'lng' => $lng
                        ]
                    );
                } else {
                    $search->setSearchInRadius(false);
                }
            }
        }

        $allCategoriesUids = [];
        $allCountriesUids = [];

        $demandDealers = $this->dealerRepository->findDemanded($demand);

        /** @var Dealer $dealer */
        foreach ($demandDealers as $dealer) {
            $dealers[$dealer->getUid()] = $dealer->toArray();
            $allCategoriesUids = array_merge($allCategoriesUids, $dealer->getCategoriesAsUidsArray());

            if (!in_array($dealer->getCountryUid(), $allCountriesUids, true)) {
                $allCountriesUids[] = $dealer->getCountryUid();
            }
        }

        $this->view->assignMultiple([
            'dealers' => $dealers,
            'allCategoriesUids' => implode(',', array_unique($allCategoriesUids)),
            'allCountriesUids' => implode(',', $allCountriesUids),
            'labelsJs' => $this->getFrontendLabels(),
            'searchCenter' => [
                'lat' => $lat,
                'lng' => $lng
            ]
        ]);
    }

    /**
     * Add labels for JS
     *
     * @return array
     */
    protected function getFrontendLabels()
    {
        /** @var LocalizationFactory $languageFactory */
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);

        $langKey = MainUtility::getTSFE()->config['config']['language'];
        $labels = $languageFactory->getParsedData(
            'EXT:pxa_dealers/Resources/Private/Language/locallang.xlf',
            $langKey ? $langKey : 'en'
        );

        if (!empty($labels[$langKey])) {
            $labels = $labels[$langKey];
        } else {
            $labels = $labels['default'];
        }

        $labelsJs = [];
        foreach (array_keys($labels) as $key) {
            if (GeneralUtility::isFirstPartOfStr($key, 'js.')) {
                $labelsJs[$key] = LocalizationUtility::translate($key, $this->extensionName);
            }
        }

        return $labelsJs;
    }

    /**
     * get address cords
     *
     * @param string $address
     * @return array
     */
    protected function getAddressInfo($address)
    {
        $response = GoogleApiUtility::getGeocoding(
            $address,
            $this->settings['map']['googleServerApiKey']
        );

        if ($response['status'] === 'OK') {
            return array(
                $response['results']['0']['geometry']['location']['lat'],
                $response['results']['0']['geometry']['location']['lng']
            );
        }

        return [null, null];
    }
}
