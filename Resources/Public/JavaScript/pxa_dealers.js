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
                        _type = _element.dataset.filterType;
                        _filteringPrefix = _element.dataset.filterPrefix;

                        switch (_type) {
                            case 'checkbox':
                                _filterElementSelector = 'input[type="checkbox"]';
                                _showVisibleItemsCheckBox();
                                break;
                            case 'selectbox':
                                _filterElementSelector = 'select';
                                _showVisibleItemsSelectBox();
                                break;
                            case 'radiobox':
                                _filterElementSelector = 'input[type="radio"]';
                                _showVisibleItemsRadioBox();
                        }

                        return this;
                    }

                    /**
                     * Display only items that has dealers
                     * @private
                     */
                    function _showVisibleItemsCheckBox() {
                        var checkBoxes = convertJq.findAll(_element, '.checkbox'),
                            overlay = convertJq.findAll(_element, '.dealers-loader-overlay'),
                            visibleList = String(document.querySelector(_map).getAttribute('data-'+_element.dataset.visible));

                        if (PxaDealersMaps.FE.isValidList(visibleList)) {
                            checkBoxes.forEach(function(el) {
                                var checkbox = el.querySelector('input[type="checkbox"]');
                                if (PxaDealersMaps.FE.inList(visibleList, checkbox.value)) {
                                    convertJq.show(el);
                                }
                            });
                        } else {
                            // show all
                            checkBoxes.forEach(function(el){
                                convertJq.show(el)
                            });
                        }

                        convertJq.hide(overlay[0]);
                    }

                    /**
                     * Display only items that has dealers
                     * @private
                     */
                    function _showVisibleItemsRadioBox() {
                        var checkBoxes = convertJq.findAll(_element, '.radio'),
                            overlay = convertJq.findAll(_element, '.dealers-loader-overlay'),
                            visibleList = String(document.querySelector(_map).getAttribute('data-'+_element.dataset.visible));

                        if (PxaDealersMaps.FE.isValidList(visibleList)) {
                            checkBoxes.forEach(function(el) {
                                var checkbox = el.querySelector('input[type="radio"]');
                                if (PxaDealersMaps.FE.inList(visibleList, checkbox.value)) {
                                    convertJq.show(el);
                                }
                            });
                        } else {
                            // show all
                            checkBoxes.forEach(function(el){
                              convertJq.show(el)
                            });
                        }

                        convertJq.hide(overlay[0]);
                    }

                    /**
                     * Display only items that has dealers
                     * @private
                     */
                    function _showVisibleItemsSelectBox() {
                        var selectBox   = convertJq.findAll(_element, 'select'),
                            options     = convertJq.findAll(selectBox[0], 'option'),
                            overlay     = convertJq.findAll(_element, '.dealers-loader-overlay'),
                            visibleList = String(document.querySelector(_map).dataset.visibleCountries);

                        if (PxaDealersMaps.FE.isValidList(visibleList)) {
                            options.forEach(function (el) {
                                if (el.value !== '0' && !PxaDealersMaps.FE.inList(visibleList, el.value)) {
                                    convertJq.remove(el);
                                }
                            });
                        }
                        convertJq.hide(overlay[0]);
                        convertJq.show(selectBox[0]);
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
            var elements = document.querySelectorAll(selector);

            Array.prototype.forEach.call(elements, function(el, i){
                PxaDealersMaps.Filters[i] = that.addFilter(that.getMapSelector(), el);
            });
        }
    };

    w.PxaDealersMaps = PxaDealersMaps;

})(window);

document.addEventListener("DOMContentLoaded", function() {
    if (typeof PxaDealersMaps.settings !== 'undefined') {
        PxaDealersMaps.FE.initHelperFunctions();
        PxaDealersMaps.FE.initFilters(
            '[data-dealers-filter="1"]'
        );

        var mapSelector = document.querySelectorAll(PxaDealersMaps.FE.getMapSelector());

        pxaDealers(
            PxaDealersMaps.MapSettings,
            PxaDealersMaps.settings,
            mapSelector
        );
    }
});
