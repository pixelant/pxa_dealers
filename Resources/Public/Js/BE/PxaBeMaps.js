(function (w) {
    var document = w.document,
        PxaDealersMaps = w.PxaDealersMaps || {};

    PxaDealersMaps.BE = {

        map: null,

        marker: null,

        geocoder: null,

        /**
         * inialize main function
         */
        initialize: function () {
            var self = this;

            self.startPosition = new google.maps.LatLng(self.getConfiguration("lat"), self.getConfiguration("lng"));

            var mapOptions = {
                center: self.startPosition,
                zoom: self.getConfiguration("zoom")
            };

            self.map = new google.maps.Map(document.getElementById(self.getConfiguration("baseId") + "_map"), mapOptions);

            self.marker = new google.maps.Marker({
                map: self.map,
                position: self.startPosition,
                draggable: true
            });

            google.maps.event.addListener(self.marker, 'dragend', function () {
                var lat = self.marker.getPosition().lat().toFixed(6),
                    lng = self.marker.getPosition().lng().toFixed(6);

                // update fields
                self.updateValue(self.getTypoFieldName("latitudeField"), lat);
                self.updateValue(self.getTypoFieldName("longitudeField"), lng);

                self.triggerFieldsChange();
            });

            self.geocoder = new google.maps.Geocoder();
        },

        /**
         * get configuration field value
         *
         * @param confName
         * @returns {string}
         */
        getConfiguration: function (confName) {
            if (typeof PxaDealersMaps.BEConfiguration[confName] !== "undefined") {
                return PxaDealersMaps.BEConfiguration[confName];
            } else {
                return "no value";
            }
        },

        /**
         * update typo TCA fields after marker was dragend
         *
         * @param fieldName
         * @param value
         */
        updateValue: function (fieldName, value) {
            document[TBE_EDITOR.formname][fieldName].value = value;
            TYPO3.jQuery('[data-formengine-input-name="' + fieldName + '"]').val(value);
        },

        /**
         * tell TYPO3 about changes
         */
        triggerFieldsChange: function () {
            var self = this;

            TBE_EDITOR.fieldChanged(self.getConfiguration("tableName"), self.getConfiguration("recordUid"), self.getConfiguration("latitudeField"), self.getTypoFieldName("latitudeField"));
            TBE_EDITOR.fieldChanged(self.getConfiguration("tableName"), self.getConfiguration("recordUid"), self.getConfiguration("longitudeField"), self.getTypoFieldName("longitudeField"));

            TYPO3.FormEngine.Validation.validate();
        },

        /**
         * get full name of typo3 TCA field
         *
         * @param field
         * @returns {string}
         */
        getTypoFieldName: function (field) {
            var self = this;

            return self.getConfiguration("fieldPrefixName") + '[' + self.getConfiguration(field) + ']';
        },

        /**
         * use TYPO3 geocoder to get coordinates by address
         * trigger by button in TCA
         */
        getAddressLatLng: function (e) {
            var self = this,
                address = self.generateAddress();

            self.geocoder.geocode({'address': address}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {

                    self.map.setCenter(results[0].geometry.location);
                    self.map.setZoom(self.getConfiguration("zoom"));
                    self.marker.setPosition(results[0].geometry.location);

                    var lat = self.marker.getPosition().lat().toFixed(6),
                        lng = self.marker.getPosition().lng().toFixed(6);

                    // update fields
                    self.updateValue(self.getTypoFieldName("latitudeField"), lat);
                    self.updateValue(self.getTypoFieldName("longitudeField"), lng);

                    self.triggerFieldsChange();

                    PxaDealersMaps.BE.showMessage('success', 'Success', 'Latitude and longitude are set', 5);
                } else {
                    PxaDealersMaps.BE.showMessage('error', 'Fail', 'Geocode was not successful. Nothing found for "' + address + '"', 5);
                }
            });

        },

        /**
         * generate address string from TCA fields
         * @returns {string}
         */
        generateAddress: function () {
            var self = this,
                addressParts = [],
                fields = ["cityField", "zipcodeField", "addressField"],
                selectCountryBox = document.getElementsByName(self.getTypoFieldName("countryField"))[0],
                selectedCountry = selectCountryBox.options[selectCountryBox.selectedIndex].text;

            addressParts.push(selectedCountry);

            for (var i = 0; i < fields.length; i++) {
                var fieldValue = document.getElementsByName(self.getTypoFieldName(fields[i]))[0].value;
                if (fieldValue != '') {
                    addressParts.push(fieldValue);
                }
            }

            return addressParts.join(", ");
        }
    };

    w.PxaDealersMaps = PxaDealersMaps;
})(window);

requirejs(['TYPO3/CMS/Backend/Notification'], function (Notification) {
    (function (w) {
        var PxaDealersMaps = w.PxaDealersMaps || {};

        PxaDealersMaps.BE['showMessage'] = function (type, title, text, duration) {
            if (type === 'error') {
                Notification.error(title, text, duration);
            } else {
                Notification.success(title, text, duration);
            }

        };

        w.PxaDealersMaps = PxaDealersMaps;
    })(window);
});


function initBEMap() {
    PxaDealersMaps.BE.initialize();
}
