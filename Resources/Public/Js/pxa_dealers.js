/*global PxaDealersMaps */

$(document).ready(function () {
    if (typeof PxaDealersMaps !== "undefined") {

        for (var key in PxaDealersMaps.Maps) {
            if (!PxaDealersMaps.Maps.hasOwnProperty(key)) continue;

            $('#pxa-dealers-map-' + PxaDealersMaps.Maps[key]['uid']).pxaDealers(PxaDealersMaps.Maps[key]);
        }
    }
});