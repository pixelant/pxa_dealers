<?php
namespace PXA\PxaDealers\Task;

class ImportTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask  {

	/**
	 *  dealersRepository
	 *
	 * @var \PXA\PxaDealers\Domain\Repository\DealersRepository
	 */
	protected $dealersRepository;

	/**
	 *  countryRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 */
	protected $countryRepository;

	/**
	 *  countryZoneRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository
	 */
	protected $countryZoneRepository;

	/**
	 *  objectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 *  registry
	 *
	 * @var TYPO3\CMS\Core\Registry
	 */
	protected $registry;

	/**
	 *  folderPath
	 *
	 * @var string
	 */
	protected $folderPath;

	/**
	 *  connector
	 *
	 * @var \connector
	 */
	protected $connector;

	/**
	 *  defaultParams
	 *
	 * @var \array
	 */
	protected $defaultParams;

	public function __construct() {
		parent::__construct();
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Extbase\Object\ObjectManager");
		$this->registry = $this->objectManager->get("TYPO3\CMS\Core\Registry");
	}

	/**
	 * Function execute from the Scheduler
	 *
	 * @return boolean TRUE on successful execution, FALSE on error
	 */
	public function execute() {

		if ( empty($this->folder_path) ) {
			return false;
		} else {
			$this->folderPath = $this->folder_path;
		}

		// Init repositories
		$this->dealersRepository = $this->objectManager->get("PXA\PxaDealers\Domain\Repository\DealersRepository");
		$this->countryRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository");
		$this->countryZoneRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository");

		// Init default file import params
		$this->defaultParams = array(
				'delimiter' => ';',
				'text_qualifier' => '',
				'encoding' => 'utf-8',
				'skip_rows' => 0,
		);

		$default_storage_pid = 1;

		$current_config = $this->createCurrentConfig();

		if(!$current_config) {

			$message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
					'Import folder wasn\'t modified since last import run',
					'Nothing to import', // the header is optional
					\TYPO3\CMS\Core\Messaging\FlashMessage::INFO, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
					TRUE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
			);

			\TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);

			return true;
		}

		// Create connector service
		if( \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::findService('connector', 'csv') ) {
			$this->connector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService('connector', 'csv');
		} else {
			$message = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\\CMS\\Core\\Messaging\\FlashMessage',
					'Please install svconnector_csv extension',
					'No csv connector found', // the header is optional
					\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR, // the severity is optional as well and defaults to \TYPO3\CMS\Core\Messaging\FlashMessage::OK
					TRUE // optional, whether the message should be stored in the session or only in the \TYPO3\CMS\Core\Messaging\FlashMessageQueue object (default is FALSE)
			);

			\TYPO3\CMS\Core\Messaging\FlashMessageQueue::addMessage($message);

			return false;
		}

		// Check if numeric
		if(!is_numeric($this->records_storage_pid)) {
			$this->records_storage_pid = $default_storage_pid;
		}

		$isOk = array();

		$folder = new \DirectoryIterator( PATH_site . $this->folderPath );
		foreach ($folder as $fileinfo) {
			if ( !$fileinfo->isDot() && $fileinfo->getExtension() == 'csv') {
				$isOk[] = $this->importFile($fileinfo->getPathname(), $this->defaultParams);
			}
		}

		// Update sys_registry record if everything is ok
		if( !in_array(false, $isOk) ) {
			$this->registry->set("tx_pxadealers", "import_last_run_config", $current_config );
		}

		return true;

	}

	/**
	 * Returns some additional information task progress, shown in
	 * the scheduler's task overview list.
	 *
	 * @return  string  Information to display
	 */
	public function getAdditionalInformation() {

		$dataPieces = [];

		// Import to
		$dataPieces[] = $this->records_storage_pid;

		// Country
		if( !empty($this->country) ) {

			$country = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Extbase\Object\ObjectManager")
					->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository")
					->findByUid($this->country);

			if( is_object($country) ) {
				$dataPieces[] = $country->getIsoCodeA3();
			}

		}

		// Import from + modification date
		$dataPieces[] = $this->folder_path . " ( " . date("d.m.y H:i:s", $this->folderMDate(PATH_site . $this->folder_path)) . " ) ";

		// return
		if( !empty($dataPieces) ) {
			return implode(", ", $dataPieces);
		}

		return false;
	}

	/**
	 * Creates current import config based on last import config,
	 * file modification date and storage pid
	 *
	 * @return  array  Current config
	 */
	public function createCurrentConfig() {

		$last_run_config = $this->registry->get("tx_pxadealers", "import_last_run_config", array());
		$current_folder_mod_date = $this->folderMDate(PATH_site . $this->folderPath);

		if(!empty($last_run_config)) {

			$current_config = $last_run_config;

			if( isset( $last_run_config[$this->records_storage_pid] )
					&& ( $last_run_config[$this->records_storage_pid]['folder_modification_time'] == $current_folder_mod_date ) ) {
				// Check whether to ignore that folder data hasn't changed since the last import
				if( $this->force_import != 1) {
					return false;
				}
			} else {

				$current_config[$this->records_storage_pid] = array(
						"folder_modification_time" => $current_folder_mod_date,
				);

			}
		} else {

			$current_config = array(
					$this->records_storage_pid => array(
							"folder_modification_time" => $current_folder_mod_date,
					)
			);

		}

		return $current_config;
	}

	public function folderMDate($folder_path) {

		$modDates = [];

		$folder = new \DirectoryIterator( $folder_path );
		foreach ($folder as $fileinfo) {
			if (!$fileinfo->isDot()) {
				$modDates[] = $fileinfo->getMTime();
			}
		}

		if( empty($modDates) ) {
			return false;
		}

		$latest = max($modDates);

		return $latest;

	}

	public function importFile($fullFilePath, $params) {

		$params['filename'] = $fullFilePath;

		$data = $this->connector->fetchArray($params);

		if(!empty($data)) {

			foreach ($data as $dataItem) {
				// Little validation/correction
				foreach($dataItem as &$field) {
					if($field == "NULL") {
						$field = "";
					}
				}

				$new_dealer_rec = $this->objectManager->get("PXA\PxaDealers\Domain\Model\Dealers");

				// Name
				$new_dealer_rec->setName( htmlspecialchars(trim($dataItem[0])) );

				// Address
				$new_dealer_rec->setAdrress( htmlspecialchars(trim($dataItem[1])) );

				// City
				$new_dealer_rec->setCity( htmlspecialchars(trim($dataItem[2])) );

				// Zip
				$new_dealer_rec->setZipcode( trim($dataItem[3]) );

				// Default country (if set)
				if( !empty($this->country) ) {
					$new_dealer_rec->setCountry( $this->countryRepository->findByUid($this->country) );
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
					if( empty($this->country) && empty($dataItem[7]) ) {
						$countryCollection = $this->countryRepository->findByIsoCodeNumber( $countryZone->getCountryIsoCodeNumber() );
						if($countryCollection->count() == 1) {
							$new_dealer_rec->setCountry( $countryCollection->getFirst() );
						}
					}
				}

				// Phone
				$new_dealer_rec->setTelephone( htmlspecialchars(trim($dataItem[5])) );

				// Lat and lng
				$position = array_map( 'trim', explode(',', $dataItem[6]) );

				$lat = isset($position[0]) ? $position[0] : "";
				$lng = isset($position[1]) ? $position[1] : "";

				$new_dealer_rec->setLat($lat);
				$new_dealer_rec->setLng($lng);

				// Position is set flag
				if( !empty($lat) && !empty($lng) ) {
					$new_dealer_rec->setLatLngIsSet(1);
				}

				// Page id
				$new_dealer_rec->setPid($this->records_storage_pid);

				// HARDCODED for now (should be moved to additional fields)
				$new_dealer_rec->setSysLanguageUid( $this->record_syslang_uid );

				// Set storage
				$defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
				$defaultQuerySettings->setRespectStoragePage(false);
				$defaultQuerySettings->setStoragePageIds(array($this->records_storage_pid));
				$this->dealersRepository->setDefaultQuerySettings($defaultQuerySettings);

				$existedDealers = $this->dealersRepository->findByNameAndPosition($new_dealer_rec->getName(),
						$new_dealer_rec->getLat(),
						$new_dealer_rec->getLng(),
						$this->records_storage_pid
				);

				$trimmedAddress = trim($dataItem[1]);

				if($existedDealers->count() <= 0 && !empty($trimmedAddress) ) {
					// Add to repo
					$this->dealersRepository->add($new_dealer_rec);
				}

			}

			$this->objectManager->get("TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager")->persistAll();

			return true;

		} else {
			// TODO Issue an error
			// no data in file or file is broken
			return false;
		}

	}

}

?>
