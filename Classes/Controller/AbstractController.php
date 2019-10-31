<?php
declare(strict_types=1);

namespace Pixelant\PxaDealers\Controller;

use Pixelant\PxaDealers\Domain\Repository\DealerRepository;
use Pixelant\PxaDealers\Utility\GoogleApiUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

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
