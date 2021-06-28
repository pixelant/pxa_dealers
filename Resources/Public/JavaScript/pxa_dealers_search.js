/*global PxaDealersMaps */

(function (w, Awesomplete) {

    var PxaDealersMaps = w.PxaDealersMaps || {};

    PxaDealersMaps.Suggest = {
        awesomplete: null,
        input: null,
        form: null,
        findClosestButton: null,
	      searchInRadius: false,

        init: function (input) {
            var self   = this;
            var map    = document.querySelector(PxaDealersMaps.FE.getMapSelector());

            self.input = document.querySelector(input);
            self.form  = convertJq.closest(self.input, 'form', 'tag');
	          self.findClosestButton = document.querySelector('[data-find-closest="1"]');
            self.searchInRadius = parseInt(self.input.dataset.searchInRadius);

            if (typeof(map) != 'undefined') {
                var search = map.dataset.searchTerm;

                if (typeof search === 'string' && search !== '') {
                    self.input.value = search;
                }
            }

            if (typeof(self.input) != 'undefined' && self.input != null) {
                self.awesomplete = new Awesomplete(input, {
                    minChars: 3,
                    autoFirst: true,
                    filter: function(text, input) { return true; }
                });

                self.input.addEventListener('keyup', function (e) {

                    var c = e.keyCode;
                    if (c === 13 || c === 27 || c === 38 || c === 40) {
                        return;
                    }

                    self._loadSuggest(e.target);
                });

                self.input.addEventListener('awesomplete-select', function(e) {
                  e.preventDefault();

                	var valueParts = e.text.value.split('::'),
                      method = valueParts[0],
                      value = valueParts[1];

                  var $inputSearchRadius = document.querySelector('input[name="tx_pxadealers_pxadealers[search][searchInRadius]"]');
                	$inputSearchRadius.value = method === 'google' ? '1' : '0';

                  e.target.value = value;
                  self.awesomplete.close();
                  self.form.submit();
                });
            }

            if (self.findClosestButton && self.findClosestButton.length > 0) {
	            self.findClosestButton.on('click', function (e) {
                    e.preventDefault();
                    self._findClosestAction($(this));
	            })
            }
        },

	    /**
         * Load suggest options by ajax
         *
	     * @param input
	     * @private
	     */
        _loadSuggest: function (input) {
            var self = this;

            //old format data (not using)
            // var data = {
            //     tx_pxadealers_pxadealers: {
            //         search: {
            //             searchTermOriginal: input.value,
            //             searchInRadius: self.searchInRadius,
            //             pid: input.dataset['pid']
            //         }
            //     }
            // }

            //new data format
            var txDealersSearch = 'tx_pxadealers_pxadealers[search]';
            var newData = txDealersSearch + '[searchTermOriginal]=' + input.value +
                    '&' + txDealersSearch + '[searchInRadius]=' + self.searchInRadius +
                    '&' + txDealersSearch + '[pid]=' + input.dataset['pid'];


            var xhr = new XMLHttpRequest();
            xhr.open('POST', input.dataset.ajaxUri, true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

            //old format
            // xhr.send(JSON.stringify(newData));
            xhr.send(newData);


            xhr.onload = function() {
                var resp = JSON.parse(this.response)
                if (this.status >= 200 && this.status < 400) {
                    var list = [];

                    Array.from(resp['db']).forEach(function (value, index) {
                        list.push({label: value, value: 'db::' + value});
                    });

                    Array.from(resp['google']).forEach(function (value, index) {
                        list.push({label: value, value: 'google::' + value});
                    });

                    self.awesomplete._list = list;
                    self.awesomplete.evaluate()

                } else {
                    console.error(resp)
                }
            };
        },

	    /**
         * Find closest for current user location
         *
         * @param button
	     * @private
	     */
	    _findClosestAction: function (button) {
		    if (navigator.geolocation) {
			    navigator.geolocation.getCurrentPosition(
				    function (position) {
					    var form = button.parents('form'),
					        latInput = form.find('[name="tx_pxadealers_pxadealers[search][lat]"]'),
					        lngInput = form.find('[name="tx_pxadealers_pxadealers[search][lng]"]'),
                            searchInRadiusInput = form.find('[name="tx_pxadealers_pxadealers[search][searchInRadius]"]');

					    // Prepare form data
					    searchInRadiusInput.val('1');
					    latInput.val(position.coords.latitude);
					    lngInput.val(position.coords.longitude);

					    form.submit();
				    },
                    function (error) {
	                    var errorMessage = '';

	                    switch(error.code) {
		                    case error.PERMISSION_DENIED:
			                    errorMessage = 'User denied the request for Geolocation.';
			                    break;
		                    case error.POSITION_UNAVAILABLE:
			                    errorMessage = 'Location information is unavailable.';
			                    break;
		                    case error.TIMEOUT:
			                    errorMessage = 'The request to get user location timed out.';
			                    break;
		                    case error.UNKNOWN_ERROR:
			                    errorMessage = 'An unknown error occurred.';
			                    break;
	                    }

	                    button
		                    .text(errorMessage)
		                    .prop('disabled', true);
                    },
                    {
	                    timeout: 10000
                    }
                );
		    } else {
		        button
                    .text(PxaDealersMaps.FE.translate('geolocationError'))
                    .prop('disabled', true);
            }
        }
    };

    w.PxaDealersMaps = PxaDealersMaps;
})(window, Awesomplete);


document.addEventListener("DOMContentLoaded", function() {
    if (typeof PxaDealersMaps !== 'undefined') {
        PxaDealersMaps.Suggest.init('#pxa-dealers-search .dealer-search-field');
    }
});
