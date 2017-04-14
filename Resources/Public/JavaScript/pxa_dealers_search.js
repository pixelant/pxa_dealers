/*global PxaDealersMaps */

(function (w, $, Awesomplete) {
    var PxaDealersMaps = w.PxaDealersMaps || {};

    PxaDealersMaps.Suggest = {

        awesomplete: null,

        input: null,
        form: null,

        init: function (input) {
            var self = this;

            self.input = $(input);
            self.form = self.input.parents('form');

            if (self.input.length > 0) {
                self.awesomplete = new Awesomplete(input, {
                    minChars: 1,
                    autoFirst: true
                });

                self.input.on('keyup', function (e) {
                    var c = e.keyCode;
                    if (c === 13 || c === 27 || c === 38 || c === 40) {
                        return;
                    }

                    self._loadSuggest($(this));
                });

                self.input.on('awesomplete-selectcomplete', function() {
                    self.form.submit();
                });
            }
        },

        _loadSuggest: function (input) {
            var self = this;

            $.ajax({
                    url: /*self.form.attr("action")*/'https://restcountries.eu/rest/v1/name/' + input.val(),
                    type: 'GET',
                    dataType: 'json',
                    data: {
                        termLowercase: input.val().toLowerCase(),
                        termOriginal: input.val(),
                        L: input.data('language')
                    },
                    success: function (data) {
                        var list = [];

                        $.each(data, function (key, value) {
                            list.push(value.name);
                        });

                        self.awesomplete._list = list;
                        self.awesomplete.evaluate()
                    }
                }
            );
        }
    };

    w.PxaDealersMaps = PxaDealersMaps;
})(window, jQuery, Awesomplete);


$(document).ready(function () {
    if (typeof PxaDealersMaps !== 'undefined') {
        PxaDealersMaps.Suggest.init('#pxa-dealers-search .dealer-search-field');
    }
});