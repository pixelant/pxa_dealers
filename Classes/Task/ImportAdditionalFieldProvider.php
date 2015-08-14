<?php
namespace PXA\PxaDealers\Task;

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
		$this->countryRepository = $this->objectManager->get("\SJBR\StaticInfoTables\Domain\Repository\CountryRepository");
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
		
		// folder_path for .csv files
		if (empty($taskInfo['folder_path'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default path and file name
				$taskInfo['folder_path'] = "fileadmin/dealers_import";
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['folder_path'] = $task->folder_path;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['folder_path'] = '';
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

		// Force import
		if (empty($taskInfo['force_import'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default force_import
				$taskInfo['force_import'] = 0;
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['force_import'] = $task->force_import;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['force_import'] = 0;
			}
		}

		if (empty($taskInfo['record_syslang_uid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default record_syslang_uid
				$taskInfo['record_syslang_uid'] = 0;
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['record_syslang_uid'] = $task->record_syslang_uid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['record_syslang_uid'] = 0;
			}
		}

		// Write the code for the field

		$additionalFields = array();

		// Storage pid
		$fieldID = 'task_records_storage_pid';
		$fieldCode = '<input type="text" name="tx_scheduler[records_storage_pid]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['records_storage_pid']) . '" size="30" />';

		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Storage page id',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		// Folder path
		$fieldID = 'folder_path';
		$fieldCode = '<input type="text" name="tx_scheduler[folder_path]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['folder_path']) . '" size="50" />';
		
		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Folder path to imported files from',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		// Country
		$fieldID = 'country';
		$fieldCode = '<input type="text" name="tx_scheduler[country]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['country']) . '" size="50" />';

		$additionalFields[$fieldID] = array(
			'code' => $fieldCode,
			// TODO change hardcoded label to LLL
			'label' => 'Default country ( ISO3 )',
			'cshKey' => '_MOD_tools_txschedulerM1',
			'cshLabel' => $fieldID
		);

		// Force import
		$fieldID = 'force_import';
		$fieldCode = '<input type="checkbox" name="tx_scheduler[force_import]" id="' . $fieldID
				. '" value="1"' . ($taskInfo['force_import'] == 1 ? ' checked="checked"' : '') .'/>';

		$additionalFields[$fieldID] = array(
				'code' => $fieldCode,
			// TODO change hardcoded label to LLL
				'label' => 'Force import',
				'cshKey' => '_MOD_tools_txschedulerM1',
				'cshLabel' => $fieldID
		);

		// Records sys language
		if(is_null($taskInfo['record_syslang_uid'])) {
			$taskInfo['record_syslang_uid'] = 0;
		}
		$fieldID = 'record_syslang_uid';
		$fieldCode = '<input type="text" name="tx_scheduler[record_syslang_uid]" id="' . $fieldID . '" value="' . htmlspecialchars($taskInfo['record_syslang_uid']) . '" size="30" />';

		$additionalFields[$fieldID] = array(
				'code' => $fieldCode,
			// TODO change hardcoded label to LLL
				'label' => 'Records system language uid',
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
		
		$submittedData['folder_path'] = trim($submittedData['folder_path']);
		if (empty($submittedData['folder_path'])) {
			// TODO change hardcoded message to LLL
			$submittedData['folder_path'] = "fileadmin/dealers_import";
		}
		
		if( !file_exists( PATH_site.$submittedData['folder_path']) ) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('Import folder doesn\'t exist'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
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

		if( empty($submittedData['force_import']) ) {
			$submittedData['force_import'] = 0;
		}

		if( !is_numeric($submittedData['record_syslang_uid']) ) {
			// TODO change hardcoded message to LLL
			$parentObject->addMessage($GLOBALS['LANG']->sL('"Records system language uid" field has to be numeric'), \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
			$result = FALSE;
		} else {
			$result = TRUE;
		}

		$submittedData['record_syslang_uid'] = intval($submittedData['record_syslang_uid']);

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
		$task->folder_path = $submittedData['folder_path'];
		$task->force_import = $submittedData['force_import'];
		$task->record_syslang_uid = $submittedData['record_syslang_uid'];

		// Default country
		if( !empty($submittedData['country']) ) {
			$country = $this->countryRepository->findByIsoCodeA3($submittedData['country'])->getFirst();
			if(is_object($country)) {
				$task->country = $country->getUid();
			} else {
				return false;
			}
		} else {
			$task->country = 0;
		}
	}

}


?>