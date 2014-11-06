<?php
namespace PXA\PxaDealers\ViewHelpers;

/***************************************************************
*  Copyright notice
*
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
 * View helper to check if a value is numeric.
 */
class IsNewRowViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * Render the supplied value as a string
	 *
	 * @param integer $current Current index
	 * @param integer $needed When to start new row
	 * @return boolean
	 */

	public function render($current,$needed) {
		$needNewRow = FALSE;

		if(($needed > 0) && ($current > 0)) {
			$needNewRow = ($current % $needed === 0 ? TRUE : FALSE);
		}

		return $needNewRow;
    }

	
}
?>