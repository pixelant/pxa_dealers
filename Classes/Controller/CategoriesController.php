<?php

namespace Pixelant\PxaDealers\Controller;

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

use Pixelant\PxaDealers\Domain\Model\Demand;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 *
 *
 * @package pxa_dealers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class CategoriesController extends ActionController
{
    /**
     * @var \Pixelant\PxaDealers\Domain\Repository\CategoryRepository
     * @inject
     */
    protected $categoriesRepository;

    /**
     * Categories filter plugin
     *
     * @return void
     */
    public function categoriesFilterAction()
    {
        $demand = Demand::getInstance($this->settings['demand']);

        $this->view->assign('categories', $this->categoriesRepository->findDemanded($demand));
    }
}
