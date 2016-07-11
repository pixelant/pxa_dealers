<?php
namespace PXA\PxaDealers\Task;

/**
 * Class CleanUpCoordinatesCacheTask
 * @package PXA\PxaDealers\Task
 */
class CleanUpCoordinatesCacheTask extends \TYPO3\CMS\Scheduler\Task\AbstractTask  {

	/**
	 * @return bool
	 */
	public function execute()
	{

		$expirationDate = $this->getExpirationDate();

		$GLOBALS['TYPO3_DB']->exec_DELETEquery(
			'tx_pxadealers_coordinates_cache',
			"UNIX_TIMESTAMP(crdate) < '{$expirationDate}'");

		return true;

	}

	/**
	 * Returns some additional information task progress, shown in
	 * the scheduler's task overview list.
	 *
	 * @return  string  Information to display
	 */
	public function getAdditionalInformation()
	{

		$data = " ExpirationDate = " . date("d-m-Y H:i:s", $this->getExpirationDate()) . " (" . $this->getExpirationDate() . ") ";

		return $data;

	}

	private function getExpirationDate()
	{
		return time() - $this->cacheLifetime;
	}

}

?>
