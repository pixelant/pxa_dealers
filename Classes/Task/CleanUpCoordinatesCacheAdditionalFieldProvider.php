<?php
namespace PXA\PxaDealers\Task;

/**
 * Class CleanUpCoordinatesCacheAdditionalFieldProvider
 * @package PXA\PxaDealers\Task
 */
class CleanUpCoordinatesCacheAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an storage field
	 *
	 * @param array $taskInfo Reference to the array containing the info used in the add/edit form
	 * @param object $task When editing, reference to the current task object. Null when adding.
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return array	Array containing all the information pertaining to the additional fields
	 */
	public function getAdditionalFields(array &$taskInfo, $task, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

		// Initialize extra field value
		if (empty($taskInfo['cacheLifetime'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default
				$taskInfo['cacheLifetime'] = 180 * 24 * 60 * 60;
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['cacheLifetime'] = $task->cacheLifetime;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['cacheLifetime'] = '';
			}
		}

		// Write the code for the field

		$additionalFields = array();

		// Storage pid
		$fieldID = 'cacheLifetime';
		$fieldCode = '<input type="text" name="tx_scheduler[cacheLifetime]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['cacheLifetime']) . '" size="30" />';

		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			'label' => 'Cache Lifetime (in seconds)',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return TRUE
	 *
	 * @param array	 $submittedData Reference to the array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject Reference to the calling object (Scheduler's BE module)
	 * @return boolean TRUE if validation was ok (or selected class is not relevant), FALSE otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, \TYPO3\CMS\Scheduler\Controller\SchedulerModuleController $parentObject) {

		$submittedData['cacheLifetime'] = trim($submittedData['cacheLifetime']);
		if (empty($submittedData['cacheLifetime'])) {
			$parentObject->addMessage($GLOBALS['LANG']->sL('Please fill out the cache lifetime'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} elseif( !is_numeric($submittedData['cacheLifetime']) ) {
			$parentObject->addMessage($GLOBALS['LANG']->sL('Cache lifetime field has to be numeric'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} else {
			$result = TRUE;
		}

		return $result;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param array $submittedData Array containing the data submitted by the user
	 * @param \TYPO3\CMS\Scheduler\Task\AbstractTask $task Reference to the current task object
	 * @return void
	 */
	public function saveAdditionalFields(array $submittedData, \TYPO3\CMS\Scheduler\Task\AbstractTask $task) {

		$task->cacheLifetime = $submittedData['cacheLifetime'];

	}

}


?>