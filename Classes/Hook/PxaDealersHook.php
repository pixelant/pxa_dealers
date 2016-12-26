<?php
namespace Pixelant\PxaDealers\Hook;

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

use TYPO3\CMS\Core\DataHandling\DataHandler;

/**
 * Hook class to modify zipcode
 *
 * @package Pixelant\PxaDealers\Hook
 */
class PxaDealersHook
{

    /**
     * Remove all spaces from zipcode to for search
     *
     * @param array $incomingFieldArray
     * @param string $table
     * @param int $id
     * @param DataHandler $reference
     * @return void
     */
    public function processDatamap_preProcessFieldArray(array $incomingFieldArray, $table, $id, DataHandler $reference)
    {
        if ($table === 'tx_pxadealers_domain_model_dealers') {
            $incomingFieldArray['zipcode_search'] = preg_replace('/[^0-9]/', '', $incomingFieldArray['zipcode']);
        }
    }
}