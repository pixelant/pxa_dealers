<?php
namespace PXA\PxaDealers\Hook;

class PxaDealersHook {
	public function processDatamap_preProcessFieldArray(array &$incomingFieldArray, $table, $id, \TYPO3\CMS\Core\DataHandling\DataHandler &$reference) {
        if ($table == 'tx_pxadealers_domain_model_dealers') {
            $incomingFieldArray['lat_lng_is_set'] = 0;
            $incomingFieldArray['zipcode_search'] = preg_replace('/[^0-9]/', '', $incomingFieldArray['zipcode']);
        }
    }
}
?>