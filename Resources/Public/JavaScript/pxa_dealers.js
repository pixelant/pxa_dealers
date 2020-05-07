/*global PxaDealersMaps */
/*global PxaDealersMaps */

(function (w) {
    var PxaDealersMaps = w.PxaDealersMaps || {};

    /**
     * List of available filters for maps
     *
     */
    PxaDealersMaps.Filters = PxaDealersMaps.Filters || {};

    PxaDealersMaps.FE = {
        /**
         * Init functions if browser doesn't support it
         *
         */
        initHelperFunctions: function () {
            if (!String.prototype.format) {
                String.prototype.format = function () {
                    var args = arguments;
                    return this.replace(/{(\d+)}/g, function (match, number) {
                        return typeof args[number] !== 'undefined'
                            ? args[number]
                            : match
                            ;
                    });
                };
            }
        },

        /**
         * Check if needle in list
         * @param string
         * @param needle
         * @return {boolean}
         */
        inList: function (string, needle) {
            return (',' + string + ',').indexOf(',' + needle + ',') !== -1;
        },

        /**
         * Check if list valid
         * @param list
         * @return {boolean}
         */
        isValidList: function (list) {
            return (typeof list === 'string' && list !== '');
        },

        /**
         * Get translations
         *
         * @param key
         * @return {*}
         */
        translate: function (key) {
            return TYPO3.lang['js.' + key] || '';
        },

	    /**
         * Selector for map
         *
	     * @returns {string}
	     */
	    getMapSelector: function () {
            return '#pxa-dealers-map';
        },

        /**
         * Add one filter to group
         *
         * @param map
         * @param jQueryObject
         * @return {{getType: getType, getFilterElementSelector: getFilterElementSelector, getDom: getDom, getFilteringPrefix: getFilteringPrefix}}
         */
        addFilter: function (map, jQueryObject) {
            return (function () {

                function FilterItem(_map, _element) {
                    var _type,
                        _filteringPrefix,
                        _filterElementSelector;

                    function init() {
                        _type = _element.data('filter-type');
                        _filteringPrefix = _element.data('filter-prefix');

                        if (_type === 'checkbox') {
                            _filterElementSelector = 'input[type="checkbox"]';
                            _showVisibleItemsCheckBox();
                        } else if (_type === 'selectbox') {
                            _filterElementSelector = 'select';
                            _showVisibleItemsSelectBox();
                        }


                        return this;
                    }

                    /**
                     * Display only items that has dealers
                     * @private
                     */
                    function _showVisibleItemsCheckBox() {
                        var checkBoxes = _element.find('.checkbox'),
                            overlay = _element.find('.dealers-loader-overlay'),
                            visibleList = String($(_map).data(_element.data('visible')));

                        if (PxaDealersMaps.FE.isValidList(visibleList)) {
                            $(checkBoxes).each(function () {
                                var $this = $(this),
                                    checkbox = $this.find('input[type="checkbox"]');

                                if (PxaDealersMaps.FE.inList(visibleList, checkbox.val())) {
                                    $this.show();
                                }
                            });
                        } else {
                            // show all
                            checkBoxes.show();
                        }


                        overlay.hide();
                    }

                    /**
                     * Display only items that has dealers
                     * @private
                     */
                    function _showVisibleItemsSelectBox() {
                        var selectBox = _element.find('select'),
                            options = selectBox.find('option'),
                            overlay = _element.find('.dealers-loader-overlay'),
                            visibleList = String($(_map).data(_element.data('visible')));

                        if (PxaDealersMaps.FE.isValidList(visibleList)) {
                            $(options).each(function () {
                                var $this = $(this);

                                if ($this.val() !== '0' && !PxaDealersMaps.FE.inList(visibleList, $this.val())) {
                                    $this.remove();
                                }
                            });
                        }

                        overlay.hide();
                        selectBox.show();
                    }

                    /**
                     * type of filter
                     */
                    function getType() {
                        return _type;
                    }

                    /**
                     * checkbox or select box
                     * @return {*}
                     */
                    function getFilterElementSelector() {
                        return _filterElementSelector;
                    }

                    /**
                     * jQuery object
                     * @return {*}
                     */
                    function getjQueryObject() {
                        return _element;
                    }

                    /**
                     * Filtering prefix in dealers
                     */
                    function getFilteringPrefix() {
                        return _filteringPrefix;
                    }

                    /**
                     * public
                     */
                    return {
                        init: init,
                        getType: getType,
                        getFilterElementSelector: getFilterElementSelector,
                        getjQueryObject: getjQueryObject,
                        getFilteringPrefix: getFilteringPrefix
                    }
                }

                return (new FilterItem(map, jQueryObject).init());
            })();
        },

        /**
         * Init all filtering elements
         */
        initFilters: function (selector) {
            var that = this;

            $(selector).each(function (i) {
                var $this = $(this);

                PxaDealersMaps.Filters[i] = that.addFilter(that.getMapSelector(), $this);
            });
        }
    };

    w.PxaDealersMaps = PxaDealersMaps;

})(window);

$(document).ready(function () {
    if (typeof PxaDealersMaps.settings !== 'undefined') {
        PxaDealersMaps.FE.initHelperFunctions();
        PxaDealersMaps.FE.initFilters(
            '[data-dealers-filter="1"]'
        );

        $(PxaDealersMaps.FE.getMapSelector()).pxaDealers(
            PxaDealersMaps.MapSettings,
            PxaDealersMaps.settings
        );
    }
});
