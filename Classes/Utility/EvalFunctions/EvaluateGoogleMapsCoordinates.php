<?php
namespace PXA\PxaDealers\Utility\EvalFunctions;

class EvaluateGoogleMapsCoordinates {

    function returnFieldJS() {
        return '
         if( isNaN(value) || value == "") {
            return 0;
         } else {
            return parseFloat(value);
         }
      ';
    }

    function evaluateFieldValue($value, $is_in, &$set) {
        return (float) $value;
    }

}

?>