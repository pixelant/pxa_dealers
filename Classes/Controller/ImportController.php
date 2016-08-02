<?php
namespace PXA\PxaDealers\Controller;

use TYPO3\CMS\Core\Utility\GeneralUtility;

$utilityFolderPath = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('pxa_dealers') . 'Classes/Utility/';

// Include libs
require_once $utilityFolderPath . 'ForceUTF8/Encoding.php';
require_once $utilityFolderPath . 'PHPExcel/Classes/PHPExcel.php';

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Andriy Oprysko <andriy@pixelant.se>, Pixelant
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


/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class ImportController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

	private $allowedFileExtensions = ['xlsx', 'csv'];

	/**
	 *  countryRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 * @inject
	 */
	protected $countryRepository;

	/**
	 *  countryZoneRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository
	 * @inject
	 */
	protected $countryZoneRepository;

	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 * @inject
	 */
	protected $dealersRepository;

	/**
	 *  categoriesRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoriesRepository;

	/**
	 * @var \int pageId
	 */
	protected $pageId = null;

    public function initializeAction() {
        $this->pageId = (int) GeneralUtility::_GET("id");
    }

	/**
	 * Index action
	 */
	public function indexAction()
	{
		
		$systemLanguages = $this->getSystemLanguages();

		$extensionConfiguration = unserialize(
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_dealers']
		);

		$countries = [];

		if( !empty($extensionConfiguration['countriesList']) ) {

			$countryRows = array_map(function($item){
				if( !empty($item) ) {
					return trim($item);
				}
			}, array_filter(array_unique(explode(',', $extensionConfiguration['countriesList']))));

			foreach ($countryRows as $countryRow) {
				$countryColumns = array_map(function($item) {
					if( !empty($item) ) {
						return trim($item);
					}
				}, array_filter(explode('=>', $countryRow)));
				$countries[] = [
					'value' => $countryColumns[0],
					'name' => $countryColumns[1]
				];
			}
			
		}

		asort($countries);

		$this->view->assign("systemLanguages", $systemLanguages);
		$this->view->assign("countries", $countries);
	}

	public function importAction()
	{

		$args = $this->request->getArguments();

		// If no args - return
		if( empty($args['file']) ) {
			// TODO: make a better message
			die("Please specify the file");
		}

		if( empty($this->pageId)) {
			die("Please select a storage page");
		}

		// Get file type
		$imageFileType = pathinfo($args['file']['name'], PATHINFO_EXTENSION);

		// Check filetype
		if( !in_array($imageFileType, $this->allowedFileExtensions) ) {
			die( "Wrong file type" );
		}

		// Get the data

		if($imageFileType == 'csv') {
			$dataArray = $this->parseCsv($args['file']['tmp_name']);
		}

		if($imageFileType == 'xlsx') {

			$dataArray = $this->parseExcel( $args['file']['tmp_name'] );

		}

		$this->importDealers($dataArray, $args['defaultCountry'], $args['languageUid'], $args['prependPhoneZero']);

		// Show warning

		// Go through the data and create ....

	}

	protected function parseExcel($filepath, $type = "")
	{

		if( empty($type) ) {
			$objReader = \PHPExcel_IOFactory::createReaderForFile($filepath);
		} else {
			$objReader = \PHPExcel_IOFactory::createReader($type);
		}

		$objReader->setReadDataOnly(true);
		$objPHPExcel = $objReader->load($filepath);
		$objWorksheet = $objPHPExcel->getActiveSheet();

		return $objWorksheet->toArray();
		
	}

	/**
	 * @param $filepath
	 * @param string $delimiter
	 * @return array
	 */
	protected function parseCsv($filepath, $delimiter = ";")
	{

		$data = array();
		$fp = fopen($filepath, 'rb');
		while(!feof($fp)) {
			$row = fgetcsv($fp, 0, $delimiter);
			if( !empty($row) ) {
				$data[] = $row;
			}
		}
		fclose($fp);

		return $data;

	}

	/**
	 * @param $data
	 * @param $defaultCountry
	 * @param $languageUid
	 */
	protected function importDealers($data, $defaultCountry, $languageUid, $prependPhoneZero)
	{


		// remove headers
		array_shift($data);

		foreach ($data as $dataItem) {

			// Little validation/correction
			foreach($dataItem as &$field) {
				if($field == "NULL") {
					$field = "";
				}
			}

			// Fix UTF8
			$dataItem = array_map(function($field) {
				return $this->fixUTF8($field);
			}, $dataItem);

			$new_dealer_rec = $this->objectManager->get("PXA\PxaDealers\Domain\Model\Dealers");

			// Name
			$new_dealer_rec->setName( trim($dataItem[0]) );

			// Address
			$new_dealer_rec->setAdrress( trim($dataItem[1]) );

			// City
			$new_dealer_rec->setCity( trim($dataItem[2]) );

			// Zip
			$new_dealer_rec->setZipcode( trim($dataItem[3]) );

			$defaultCountryObject = null;
			if( !empty($defaultCountry) ) {
				$defaultCountryObject = $this->countryRepository->findByIsoCodeA3($defaultCountry)->getFirst();
			}

			// Default country (if set)
			if( is_object($defaultCountryObject) ) {
				$new_dealer_rec->setCountry( $defaultCountryObject );
			} else {
				if( !empty($dataItem[7]) ) {
					$countryIso3 = trim( $dataItem[7] );
					$countryIso3Mapping = array(
						"ITL" => "ITA"
					);
					$countryCollection = $this->countryRepository->findByisoCodeA3( $countryIso3 );
					if($countryCollection->count() == 1) {
						$new_dealer_rec->setCountry( $countryCollection->getFirst() );
					} else {
						$countryMappingIndex = $countryIso3;
						if( isset($countryIso3Mapping[$countryMappingIndex]) ) {
							$countryCollection = $this->countryRepository->findByisoCodeA3( $countryIso3Mapping[$countryMappingIndex] );
							if($countryCollection->count() == 1) {
								$new_dealer_rec->setCountry( $countryCollection->getFirst() );
							}
						}
					}
				}
			}

			// Country zone
			$countryZoneCode = trim( $dataItem[4] );
			$countryZoneCollection = $this->countryZoneRepository->findByIsoCode($countryZoneCode);

			if( $countryZoneCollection->count() == 1 ) {

				$countryZone = $countryZoneCollection->getFirst();

				$new_dealer_rec->setCountryZone($countryZone);

				// Country
				if( empty($defaultCountry) && empty($dataItem[7]) ) {
					$countryCollection = $this->countryRepository->findByIsoCodeNumber( $countryZone->getCountryIsoCodeNumber() );
					if($countryCollection->count() == 1) {
						$new_dealer_rec->setCountry( $countryCollection->getFirst() );
					}
				}
			}

			// Phone
			$phone = trim($dataItem[5]);

			if($prependPhoneZero) {
				if( $phone[0] != '0') {
					$phone = "0" . $phone;
				}
			}

			$new_dealer_rec->setTelephone( $phone );

			$extensionConfiguration = unserialize(
				$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['pxa_dealers']
			);

			if( !empty($extensionConfiguration['easyPickCategoryUid']) && $dataItem[6] == '1') {
				$easyPickCategory = $this->categoriesRepository->findByUid( $extensionConfiguration['easyPickCategoryUid'] );

				if( is_object($easyPickCategory) ) {

					$new_dealer_rec->addCategory( $easyPickCategory );

				}

			}

//			// Lat and lng
//			$position = array_map( 'trim', explode(',', $dataItem[6]) );

//			$lat = isset($position[0]) ? $position[0] : "";
//			$lng = isset($position[1]) ? $position[1] : "";

			$lat = "";
			$lng = "";

			$new_dealer_rec->setLat($lat);
			$new_dealer_rec->setLng($lng);

			// Position is set flag
			if( !empty($lat) && !empty($lng) ) {
				$new_dealer_rec->setLatLngIsSet(1);
			}

			// Page id
			$new_dealer_rec->setPid($this->pageId);

			// HARDCODED for now (should be moved to additional fields)
			$new_dealer_rec->setSysLanguageUid( $languageUid );

			// Set deleted
			$new_dealer_rec->setDeleted(1);

			// Set just added
			$new_dealer_rec->setJustAdded(1);

			// Set storage
			$defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
			$defaultQuerySettings->setRespectStoragePage(false);
			$defaultQuerySettings->setStoragePageIds(array($this->pageId));
			$this->dealersRepository->setDefaultQuerySettings($defaultQuerySettings);

			$this->dealersRepository->add($new_dealer_rec);

		}

		$this->objectManager->get("TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager")->persistAll();

		$this->turnOnNewRecords($this->pageId);

	}

	protected function getSystemLanguages($prependDefault = true)
	{

		$systemLanguages = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid, title',
			'sys_language',
			'hidden = 0'
		);

		if($prependDefault) {
			array_unshift($systemLanguages, ['uid' => 0, 'title' => 'Default']);
		}

		return $systemLanguages;

	}

	protected function fixUTF8($data)
	{
		$dataFixed = \ForceUTF8\Encoding::fixUTF8($data);
		return \ForceUTF8\Encoding::toUTF8($dataFixed);
	}

	protected function turnOnNewRecords($pid)
	{
		// Delete all old recirds
		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'tx_pxadealers_domain_model_dealers',
			'pid = ' . intval($pid) . ' AND just_added != 1'
		);

		// Turn on new records
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery(
			'tx_pxadealers_domain_model_dealers',
			'pid = ' . intval($pid),
			array('deleted' => 0, 'just_added' => 0)
		);

	}


	public function getDealersInfoAjaxAction()
	{

		if( $this->pageId == NULL ) {
			return 0;
		}

		$defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
		$defaultQuerySettings->setRespectSysLanguage(false);
		$defaultQuerySettings->setStoragePageIds(array($this->pageId));
		$this->dealersRepository->setDefaultQuerySettings($defaultQuerySettings);
		$dealersCount = $this->dealersRepository->findAll()->count();

		$result = [];
		$result['dealersCount'] = $dealersCount;

		return json_encode($result);

	}

}
?>