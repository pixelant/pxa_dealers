/*global PxaDealersMaps */

(function (w, $, Awesomplete) {

    var PxaDealersMaps = w.PxaDealersMaps || {};

    PxaDealersMaps.Suggest = {

        awesomplete: null,

        input: null,
        form: null,
        findClosestButton: null,

	    searchInRadius: false,

        init: function (input) {
            var self = this;
            var  map = $(PxaDealersMaps.FE.getMapSelector());


	        self.input = $(input);
	        self.form = self.input.parents('form');
	        self.findClosestButton = $('[data-find-closest="1"]');

	        self.searchInRadius = parseInt(self.input.data('search-in-radius'));

            if (map.length === 1) {
                var search = map.data('search-term');
                if (typeof search === 'string' && search !== '') {
                    self.input.val(search);
                }
            }

            if (self.input.length > 0) {
                self.awesomplete = new Awesomplete(input, {
                    minChars: 3,
                    autoFirst: true,
                    filter: function(text, input) { return true; }
                });

                self.input.on('keyup', function (e) {
                    var c = e.keyCode;
                    if (c === 13 || c === 27 || c === 38 || c === 40) {
                        return;
                    }

                    self._loadSuggest($(this));
                });

                self.input.on('awesomplete-select', function(e) {
                	e.preventDefault();
                	var valueParts = e.originalEvent.text.value.split('::'),
						method = valueParts[0],
						value = valueParts[1];

                	var $inputSearchRadius=$('input[name="tx_pxadealers_pxadealers[search][searchInRadius]"]');
                	$inputSearchRadius.val(method === 'google' ? '1' : '0');

					$(e.target).val(value);
					self.awesomplete.close();
					self.form.submit();
                });
            }

            if (self.findClosestButton.length > 0) {
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

            $.ajax({
                    url: input.data('ajax-uri'),
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        tx_pxadealers_pxadealers: {
                            search: {
                                searchTermOriginal: input.val(),
	                            searchInRadius: self.searchInRadius,
                                pid: input.data('pid')
                            }
                        }
                    },
                    success: function (data) {
                        var list = [];

                        $.each(data['db'], function (key, value) {
                            list.push({label: value, value: 'db::' + value});
                        });

						$.each(data['google'], function (key, value) {
							list.push({label: value, value: 'google::' + value});
						});

                        self.awesomplete._list = list;
                        self.awesomplete.evaluate()
                    }
                }
            );
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
})(window, jQuery, Awesomplete);


document.addEventListener("DOMContentLoaded", function() {
    if (typeof PxaDealersMaps !== 'undefined') {
        PxaDealersMaps.Suggest.init('#pxa-dealers-search .dealer-search-field');
    }
});
