define([
  'TYPO3/CMS/Backend/Notification'
], function (Notification) {
    'use strict';

    /**
     * return instance method
     */
    return (function () {
        /**
         * @private
         *
         * Hold the instance (Singleton Pattern)
         */
        var _dealersMapPoints = null;

        function DealersMapPoints(_key, _configuration) {

            var _isGoogleLoaded = false;

            var _map;

            var _marker;

            var _geoCoder;

            /**
             * initialize main function
             */
            function _initializeAfterGoogle() {
                var startPosition = new google.maps.LatLng(_getConfiguration('lat'), _getConfiguration('lng'));

                var mapOptions = {
                    center: startPosition,
                    zoom: _getConfiguration('zoom')
                };

                _map = new google.maps.Map(document.getElementById(_getConfiguration('baseId') + '_map'), mapOptions);

                _marker = new google.maps.Marker({
                    map: _map,
                    position: startPosition,
                    draggable: true
                });

                google.maps.event.addListener(_marker, 'dragend', function () {
                    var lat = _marker.getPosition().lat().toFixed(6),
                        lng = _marker.getPosition().lng().toFixed(6);

                    // update fields
                    _updateValue(_getTypoFieldName('latitudeField'), lat);
                    _updateValue(_getTypoFieldName('longitudeField'), lng);

                    _triggerFieldsChange();
                });

                _geoCoder = new google.maps.Geocoder();
            }

            /**
             * get configuration field value
             *
             * @param confName
             * @returns {string}
             */
            function _getConfiguration(confName) {
                return (typeof _configuration[confName] !== 'undefined' ? _configuration[confName] : '');
            }

            /**
             * update typo TCA fields after marker was dragend
             *
             * @param fieldName
             * @param value
             */
            function _updateValue(fieldName, value) {
                document[TBE_EDITOR.formname][fieldName].value = value;
                document.querySelector('[data-formengine-input-name="' + fieldName + '"]').value = value;
            }

            /**
             * tell TYPO3 about changes
             */
            function _triggerFieldsChange() {
                TBE_EDITOR.fieldChanged(_getConfiguration('tableName'), _getConfiguration('recordUid'), _getConfiguration('latitudeField'), _getTypoFieldName('latitudeField'));
                TBE_EDITOR.fieldChanged(_getConfiguration('tableName'), _getConfiguration('recordUid'), _getConfiguration('longitudeField'), _getTypoFieldName('longitudeField'));

                TYPO3.FormEngine.Validation.validate();
            }

            /**
             * get full name of typo3 TCA field
             *
             * @param field
             * @returns {string}
             */
            function _getTypoFieldName(field) {
                return _getConfiguration('fieldPrefixName') + '[' + _getConfiguration(field) + ']';
            }


            /**
             * Display message
             *
             * @param type
             * @param title
             * @param text
             * @param duration
             * @private
             */
            function _showMessage(type, title, text, duration) {
                duration = duration || 5;

                if (type === 'error') {
                    Notification.error(title, text, duration);
                } else {
                    Notification.success(title, text, duration);
                }
            }

            /**
             * generate address string from TCA fields
             * @returns {string}
             */
            function _generateAddress() {
                var addressParts = [],
                    fields = ['cityField', 'zipcodeField', 'addressField'],
                    selectCountryBox = document.getElementsByName(_getTypoFieldName('countryField'))[0],
                    selectedCountry = selectCountryBox.options[selectCountryBox.selectedIndex].text;

                addressParts.push(selectedCountry);

                for (var i = 0; i < fields.length; i++) {
                    var fieldValue = document.getElementsByName(_getTypoFieldName(fields[i]))[0].value;
                    if (fieldValue !== '') {
                        addressParts.push(fieldValue);
                    }
                }

                return addressParts.join(', ');
            }

            /**
             * Load google map script
             *
             * @private
             */
            function _loadGoogleMapScript() {
                var script = document.createElement('script');

                script.src = 'https://maps.googleapis.com/maps/api/js?key=' + _key;

                script.onerror = function () {
                    // handling error when loading script
                    alert('Error to load script')
                };

                script.onload = function () {
                    _initializeAfterGoogle()
                };

                document.getElementsByTagName('head')[0].appendChild(script);
            }

            /**
             * use TYPO3 geocoder to get coordinates by address
             * trigger by button in TCA
             */
            function getAddressLatLng() {
                var address = _generateAddress();

                _geoCoder.geocode({'address': address}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        _map.setCenter(results[0].geometry.location);
                        _map.setZoom(_getConfiguration('zoom') === 1 ? 8 : _getConfiguration('zoom'));
                        _marker.setPosition(results[0].geometry.location);

                        var lat = _marker.getPosition().lat().toFixed(6),
                            lng = _marker.getPosition().lng().toFixed(6);

                        // update fields
                        _updateValue(_getTypoFieldName('latitudeField'), lat);
                        _updateValue(_getTypoFieldName('longitudeField'), lng);

                        _triggerFieldsChange();

                        _showMessage('success', 'Success', 'Latitude and longitude are set');
                    } else {
                        _showMessage('error', 'Fail', 'Geocode was not successful. Nothing found for "' + address + '"');
                    }
                });
            }

            /**
             * Main init function
             */
            function init() {
                if (_isGoogleLoaded === false) {
                    _loadGoogleMapScript();
                }

                return this;
            }

            /**
             * return public methods
             */
            return {
                init: init,
                getAddressLatLng: getAddressLatLng
            }
        }

        /**
         * Emulation of static methods
         */
        return {
            /**
             * @public
             * @static
             *
             * Implement the "Singleton Pattern".
             *
             * @return object
             */
            getInstance: function (key, configuration) {
                if (_dealersMapPoints === null) {
                    _dealersMapPoints = new DealersMapPoints(key, configuration);
                }

                return _dealersMapPoints;
            }
        };
    })();
});
