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
class DealerMarkerIndicatorViewHelper extends \TYPO3\CMS\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper {

	/**
	 * Render the supplied value as a string
	 *
	 * @param \PXA\PxaDealers\Domain\Model\Dealers $dealer
	 * @param \array $settings
	 * @return string marker indicator path
	 */

	public function render($dealer, $settings) {

		// Show country flag if country wide
		if( $settings['showCountryWide'] && $dealer->getCountryWide() == 1 ) {

			$countryIso2 = strtolower( $dealer->getCountry()->getIsoCodeA2() );
			$path = trim( $settings['flagsPath'], '/' );
			$iconPath = $path."/".$countryIso2.".png";

			$notFoundPath = "/typo3conf/ext/pxa_dealers/Resources/Public/Icons/image_not_found.png";

			//$dealer->getCountryUid()

			if( file_exists(PATH_site.$iconPath) ) {
				return $iconPath;
			} else {
				return $notFoundPath;
			}
		// else show partner type marker
		} else {
			if( $dealer->getPartnerType() == 0 ) {
				return $settings['map']['markerImageStandardPartner'];
			}
			if( $dealer->getPartnerType() == 1 ) {
				return $settings['map']['markerImagePremiumPartner'];
			}
			if( $dealer->getPartnerType() == 2 ) {
				return $settings['map']['markerImageCommercialPartner'];
			}
			if( $dealer->getPartnerType() == 3 ) {
				return $settings['map']['markerImageTestcenter'];
			}
			if( $dealer->getPartnerType() == 4 ) {
				return $settings['map']['markerImageButik'];
			}
		}

		return "";
    }

	
}
?>