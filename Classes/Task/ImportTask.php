<?php
namespace PXA\PxaDealers\Task;

class ImportTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask implements \TYPO3\CMS\Scheduler\ProgressProviderInterface {

  /**
   *  garbageRepository
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
   *  filepath
   *
   * @var string
   */
  protected $filepath;
  
  public function __construct() {
    parent::__construct();
    $this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Extbase\Object\ObjectManager");
    $this->registry = $this->objectManager->get("TYPO3\CMS\Core\Registry");
	$this->filepath = "fileadmin/source.csv";
  }

  /**
   * Function execute from the Scheduler
   *
   * @return boolean TRUE on successful execution, FALSE on error
   */
  public function execute() {
	  
	if ( !empty($this->file_path) )
		$this->filepath = $this->file_path;

    // Init repositories
    $this->dealersRepository = $this->objectManager->get("PXA\PxaDealers\Domain\Repository\DealersRepository");
    $this->countryRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository");
    $this->countryZoneRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryZoneRepository");

    $default_storage_pid = 1;

    $current_config = $this->createCrrentConfig();

    if($current_config[$this->records_storage_pid]['start_from_row'] >= 0) {

      // Check if numeric
      if(!is_numeric($this->records_storage_pid)) {
        $this->records_storage_pid = $default_storage_pid;
      }

      // Create connector service
      $services = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::findService('connector', 'csv');

      if ($services === false) {
        // TODO Issue an error
        // \TYPO3\CMS\Core\Utility\GeneralUtility::sysLog("[scheduler: pxa_garbage_collection]: Fail. CSV-connector not found.", 'pxa_garbage_collection', \TYPO3\CMS\Core\Utility\GeneralUtility::SYSLOG_SEVERITY_WARNING);
        return false;
      } else {
        $connector = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstanceService('connector', 'csv');
      }

      // TODO set filename in settings
      $parameters = array(
        'filename' => $this->filepath,
        'delimiter' => ';',
        'text_qualifier' => '',
        'encoding' => 'utf-8',
        'skip_rows' => 0,
        'locale' => 'sv_SE.UTF-8'
      );

      $data = $connector->fetchArray($parameters);

      if(!empty($data)) {

        $current_config[$this->records_storage_pid]['num_rows'] = count($data);

        // delete records from storage if needed
//        if($current_config[$this->records_storage_pid]['start_from_row'] == 0) {
//          if( !$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_pxadealers_domain_model_dealers', 'pid='.intval($this->records_storage_pid)) ) {
//            return false;
//          }
//        }
		
        for ($i = $current_config[$this->records_storage_pid]['start_from_row'], $last_row = $current_config[$this->records_storage_pid]['start_from_row'] + $this->records_per_run_limit; $i < $last_row; $i++) {

          // break if finish
          if($i >= $current_config[$this->records_storage_pid]['num_rows']) {
            $current_config[$this->records_storage_pid]['start_from_row'] = -1;
            break;
          }

	      // Little validation/correction
	      foreach($data[$i] as &$field) {
		    if($field == "NULL") {
		  	  $field = "";
		    }
          }

          $new_dealer_rec = $this->objectManager->get("PXA\PxaDealers\Domain\Model\Dealers");

          // Name
          $new_dealer_rec->setName( htmlspecialchars(trim($data[$i][0])) );

          // Address
          $new_dealer_rec->setAdrress( htmlspecialchars(trim($data[$i][1])) );

          // City
          $new_dealer_rec->setCity( htmlspecialchars(trim($data[$i][2])) );

          // Zip
          $new_dealer_rec->setZipcode( trim($data[$i][3]) );

          // Default country (if set)
          if( !empty($this->country) ) {
            $new_dealer_rec->setCountry( $this->countryRepository->findByUid($this->country) );
          } else {
            if( !empty($data[$i][7]) ) {
              $countryIso3 = trim( $data[$i][7] );
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
          $countryZoneCode = trim( $data[$i][4] );
          $countryZoneCollection = $this->countryZoneRepository->findByIsoCode($countryZoneCode);

          if( $countryZoneCollection->count() == 1 ) {

            $countryZone = $countryZoneCollection->getFirst();

            $new_dealer_rec->setCountryZone($countryZone);

            // Country
            if( empty($this->country) && empty($data[i][7]) ) {
              $countryCollection = $this->countryRepository->findByIsoCodeNumber( $countryZone->getCountryIsoCodeNumber() );
              if($countryCollection->count() == 1) {
                $new_dealer_rec->setCountry( $countryCollection->getFirst() );
              }
            }
          }

          // Phone
          $new_dealer_rec->setTelephone( htmlspecialchars(trim($data[$i][5])) );

          // Lat and lng
          $position = array_map( 'trim', explode(',', $data[$i][6]) );

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
	      $new_dealer_rec->setSysLanguageUid(7);

          // Set storage
          $defaultQuerySettings = $this->objectManager->get('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings');
          $defaultQuerySettings->setRespectStoragePage(false);
          $defaultQuerySettings->setStoragePageIds(array($this->records_storage_pid));
          $defaultQuerySettings->setSysLanguageUid(7);
          $this->dealersRepository->setDefaultQuerySettings($defaultQuerySettings);

          $exsistedDealers = $this->dealersRepository->findByNameAndPosition($new_dealer_rec->getName(), $new_dealer_rec->getLat(), $new_dealer_rec->getLng());

	$trimmedAddress = trim($data[$i][1]);

          if($exsistedDealers->count() <= 0 && !empty($trimmedAddress) ) {
            // Add to repo
            $this->dealersRepository->add($new_dealer_rec);
          }

          $current_config[$this->records_storage_pid]['start_from_row'] = $i + 1;
        }

        $this->objectManager->get("TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager")->persistAll();
        $this->registry->set("tx_pxadealers", "import_last_run_config", $current_config );
        
        return true;

      } else {
        // TODO Issue an error
        // no data in file or file is broken
        return false;  
      }

    } else {
      // TODO Issue an error or status
      // file hasn't changed
    }

    return true;
  }

  public function getProgress() {

    if ( !empty($this->file_path) )
      $this->filepath = $this->file_path;

    $currrent_config = $this->createCrrentConfig();

    $last_run_config = $this->registry->get("tx_pxadealers", "import_last_run_config", array());

    if( !empty($currrent_config[$this->records_storage_pid]['num_rows']) ) {
      if($currrent_config[$this->records_storage_pid]['start_from_row'] == -1) {
        return 100;
      } elseif($currrent_config[$this->records_storage_pid]['num_rows'] != 0) {
        return round($currrent_config[$this->records_storage_pid]['start_from_row'] / $currrent_config[$this->records_storage_pid]['num_rows'] * 100, 2);
      }    
    }

    return 0;

  }

  /**
   * Returns some additional information task progress, shown in
   * the scheduler's task overview list.
   *
   * @return  string  Information to display
   */
  public function getAdditionalInformation() {
    return false;
  }

  /**
   * Creates current import config based on last import config, 
   * file modification date and storage pid
   *
   * @return  array  Curent config
   */
  public function createCrrentConfig() {

    $last_run_config = $this->registry->get("tx_pxadealers", "import_last_run_config", array());

    $current_file_mod_date = filemtime(PATH_site . $this->filepath);

    if(!empty($last_run_config)) {

      $current_config = $last_run_config;

      if( isset( $last_run_config[$this->records_storage_pid] ) 
            && ( $last_run_config[$this->records_storage_pid]['file_modification_time'] == $current_file_mod_date ) ) {

      } else {

        $current_config[$this->records_storage_pid] = array(
          "file_modification_time" => $current_file_mod_date,
          "start_from_row" => 0
        );

      }
    } else {

      $current_config = array(
          $this->records_storage_pid => array(
            "file_modification_time" => $current_file_mod_date,
            "start_from_row" => 0
          )
      );

    }

    return $current_config;
  }

}

?>
