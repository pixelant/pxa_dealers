<?php
namespace Pixelant\PxaDealers\Task;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011-2013 Christian Kuhn <lolli@schwarzbu.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
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
 * Additional BE fields for sys log table garbage collection task.
 *
 * @author Christian Kuhn <lolli@schwarzbu.ch>
 */
class ImportAdditionalFieldProvider implements \TYPO3\CMS\Scheduler\AdditionalFieldProviderInterface {

	/**
	 *  objectManager
	 *
	 * @var \TYPO3\CMS\Extbase\Object\ObjectManager
	 */
	protected $objectManager;

	/**
	 *  countryRepository
	 *
	 * @var \SJBR\StaticInfoTables\Domain\Repository\CountryRepository
	 */
	protected $countryRepository;

	public function __construct() {
		$this->objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance("TYPO3\CMS\Extbase\Object\ObjectManager");
		$this->countryRepository = $this->objectManager->get("SJBR\StaticInfoTables\Domain\Repository\CountryRepository");
	}

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
		if (empty($taskInfo['records_storage_pid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default storage pid
				$taskInfo['records_storage_pid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['records_storage_pid'] = $task->records_storage_pid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['records_storage_pid'] = '';
			}
		}


		if (empty($taskInfo['records_per_run_limit'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default storage pid
				$taskInfo['records_per_run_limit'] = 100;
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['records_per_run_limit'] = $task->records_per_run_limit;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['records_per_run_limit'] = '';
			}
		}
		
		// filepath for .csv file
		if (empty($taskInfo['file_path'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default path and file name
				$taskInfo['file_path'] = "fileadmin/source.csv";
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['file_path'] = $task->file_path;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['file_path'] = '';
			}
		}

		// Country
		if (empty($taskInfo['country'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default path and file name
				$taskInfo['country'] = "";
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				if( !empty($task->country) ) {
					$taskInfo['country'] = $this->countryRepository->findByUid($task->country)->getIsoCodeA3();
				} else {
					$taskInfo['country'] = "";
				}
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['country'] = '';
			}
		}

		// Write the code for the field

		$additionalFields = array();

		$fieldID = 'task_records_storage_pid';
		$fieldCode = '<input type="text" name="tx_scheduler[records_storage_pid]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['records_storage_pid']) . '" size="30" />';

		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Storage page id',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		$fieldID = 'task_records_per_run_limit';
		$fieldCode = '<input type="text" name="tx_scheduler[records_per_run_limit]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['records_per_run_limit']) . '" size="30" />';
		
		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Records per run',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);
		
		$fieldID = 'file_path';
		$fieldCode = '<input type="text" name="tx_scheduler[file_path]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['file_path']) . '" size="50" />';
		
		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'File path to imported file',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		$fieldID = 'country';
		$fieldCode = '<input type="text" name="tx_scheduler[country]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['country']) . '" size="50" />';

		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Default country',
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

		$submittedData['records_storage_pid'] = trim($submittedData['records_storage_pid']);
		if (empty($submittedData['records_storage_pid'])) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('Please fill out the storage field'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} elseif( !is_numeric($submittedData['records_storage_pid']) ) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('Storage field has to be numeric'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} else {
			$result = TRUE;
		}

		$submittedData['records_per_run_limit'] = trim($submittedData['records_per_run_limit']);
		if (empty($submittedData['records_per_run_limit'])) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('Please fill out the "Records per run" field'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} elseif( !is_numeric($submittedData['records_per_run_limit']) ) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('"Records per run" field has to be numeric'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} else {
			$result = TRUE;
		}
		
		$submittedData['file_path'] = trim($submittedData['file_path']);
		if (empty($submittedData['file_path'])) {
			// TODO change hardcoded message to LLL
			$submittedData['file_path'] = "fileadmin/user_upload/ftp_upload/sms_hmab.csv";
		}
		
		if( !is_file( PATH_site.$submittedData['file_path']) ) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('Imported file doesn\'t exist'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} elseif ( strpos($submittedData['file_path'], '.csv') === FALSE ) {
			$parentObject->addMessage($GLOBALS['LANG']->sL('Imported file has wrong format'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} else {
			$result = TRUE;
		}

		$submittedData['country'] = trim($submittedData['country']);

		// Check if there is a country with such isocode3 in static info tables
		if( !empty($submittedData['country']) ) {
			$countryCollection = $this->countryRepository->findByIsoCodeA3($submittedData['country']);
			if($countryCollection->count() <= 0) {
				$parentObject->addMessage($GLOBALS['LANG']->sL("There is no country with {$submittedData['country']} isocode."), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				$result = FALSE;
			}
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

		$task->records_storage_pid = $submittedData['records_storage_pid'];
		$task->records_per_run_limit = $submittedData['records_per_run_limit'];
		$task->file_path = $submittedData['file_path'];

		// Default country
		if( !empty($submittedData['country']) ) {
			$task->country = $this->countryRepository->findByIsoCodeA3($submittedData['country'])->getFirst()->getUid();
		} else {
			$task->country = 0;
		}
	}

}


?>