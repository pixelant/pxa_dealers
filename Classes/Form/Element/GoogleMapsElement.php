<?php
declare(strict_types = 1);
namespace Pixelant\PxaDealers\Form\Element;

use Pixelant\PxaDealers\Utility\TcaUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;

class GoogleMapsElement extends AbstractFormElement
{
    public function render()
    {
        // Custom TCA properties and other data can be found in $this->data, for example the above
        // parameters are available in $this->data['parameterArray']['fieldConf']['config']['parameters']
        $data = $this->data;

        //add the same array view like was in TcaUtility.php
        $PA = $data['parameterArray'];
        $PA['parameters'] = $data['parameterArray']['fieldConf']['config']['parameters'];
        $PA['row'] = $data['databaseRow'];
        $PA['field'] = $data['fieldName'];
        $PA['table'] = $data['tableName'];

        $TcaUtility = new TcaUtility();
        $renderHtmlForGoogleMap = $TcaUtility->renderGoogleMapPosition($PA);

        $result = $this->initializeResultArray();
        $result['html'] = $renderHtmlForGoogleMap;
        return $result;
    }
}
