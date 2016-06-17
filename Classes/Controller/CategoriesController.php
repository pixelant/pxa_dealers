<?php
namespace PXA\PxaDealers\Controller;

use \TYPO3\CMS\Extbase\Utility\DebuggerUtility as du;

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
class CategoriesController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 *  categoriesRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository
	 * @inject
	 */
	protected $categoriesRepository;

	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {

		$categoryUids = explode(",", $this->settings['dealers_categories']);
		$categoriesList = array();
		$enabledCategoriesUids = explode(",", $this->settings['dealers_categories_enabled']);
		$enabledCategories = array();

		foreach ($categoryUids as $categoryUid) {
			$category = $this->categoriesRepository->findByUid($categoryUid);
			if( is_object($category) ) {
				if( in_array($categoryUid, $enabledCategoriesUids) ) {
					$enabledCategories[$categoryUid] = 1;
				} else {
					$enabledCategories[$categoryUid] = 0;
				}
				$categoriesList[] = $category;
			}
		}

		$this->view->assign('categories',$categoriesList);
		$this->view->assign('enabledCategories', $enabledCategories);
	}

}
?>