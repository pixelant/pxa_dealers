<?php
declare(strict_types = 1);
namespace Pixelant\PxaDealers\Form\Element;

use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class GoogleMapsElement extends AbstractFormElement
{
    public function render()
    {

        $PA = $this->data;

        if ($PA['row']['pid'] < 0) {
            // then "Save and create new was clicked"
            $pid = BackendUtility::getRecord(
                'tx_pxadealers_domain_model_dealer',
                abs($PA['row']['pid']),
                'pid'
            )['pid'];
        } else {
            $pid = $PA['row']['pid'];
        }

        $settings = $this->loadTS($pid);

        $outPut = '';

        if ($settings['map']['googleJavascriptApiKey']) {
            $outPut .= $this->getHtml($PA);

            $this->loadRequireJsWithConfiguration(
                $PA,
                $settings['map']['googleJavascriptApiKey']
            );
        } else {
            $outPut .= '<b>' . MainUtility::translate('tca_be_map.noApiKey') . '</b>';
        }

        return $outPut;
    }
}
