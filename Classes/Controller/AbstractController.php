<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Controller;

use Pixelant\PxaDealers\Domain\Model\Dealer;
use Pixelant\PxaDealers\Domain\Model\Demand;
use Pixelant\PxaDealers\Domain\Model\Search;
use Pixelant\PxaDealers\Domain\Repository\DealerRepository;
use Pixelant\PxaDealers\Utility\GoogleApiUtility;
use Pixelant\PxaDealers\Utility\MainUtility;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class AbstractController
 * @package Pixelant\PxaDealers\Controller
 */
abstract class AbstractController extends ActionController
{
    /**
     *  dealer repository
     *
     * @var DealerRepository
     */
    protected $dealerRepository = null;

    /**
     * @param DealerRepository $dealerRepository
     */
    public function injectDealerRepository(DealerRepository $dealerRepository)
    {
        $this->dealerRepository = $dealerRepository;
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
