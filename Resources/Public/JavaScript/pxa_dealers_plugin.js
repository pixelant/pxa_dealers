var convertJq = {
    /**
        * Get parents of given node element
        * @param  {node} arg1       ?
        * @param  {node} arg1       ?
        * @return {object}          ?
    */
    getParents: function (el, parentSelector) {

        // If no parentSelector defined will bubble up all the way to *document*
        if (parentSelector === undefined) {
            parentSelector = document;
        }

        var parents = [];
        var p = el.parentElement;

        while (p !== parentSelector && p.parentElement) {
            var o = p;
            parents.push(o);
            p = o.parentElement;
        }

        return parents;
    },
    /**
        * Get parent node element
        * @param  {node} arg1       DOM element node
        * @return {object}          DOM parent element node
    */
    getParent: function(el){
        return el.parentElement
    },
    /**
        * Change visibility of el. Equal to display=none
        * @param  {node} arg1       DOM element node
        * @return {[type]}          Undefined
    */
    hide: function(el){
        return el.style.display = 'none';
    },
    /**
        * Change visibility of el. Equal to display=block
        * @param  {node} arg1       DOM element node
        * @return {[type]}          Undefined
    */
    show: function(el) {
        return el.style.display = 'block';
    },
    /**
        * Find all child by given selector
        * @param  {node} arg1       DOM element node
        * @param  {string} arg2     Selector of nodes that fn need to find
        * @return {node collection} Collection of node elements
    */
    findAll: function(el, selector) {
        return el.querySelectorAll(selector)
    },
    /**
        * Get element by ID
        * @param  {string} arg1     Selector of element
        * @return {object}          DOM node or undefined
    */
    getById: function(selector) {
        return document.getElementById(selector);
    },
    /**
        * Get width of document
        * @return {number}           Number of document body width
    */
    getDocWidth: function() {
        return document.body.clientWidth
    },
    //[TODO] jquery animate here !!
    aimate: function(el) {

    },
    /**
        * Get offset of DOM element
        * @param  {element} arg1     DOM element
        * @return {object}           Object with top and left offset
    */
    offset: function(el) {
        return {
            top: el.offsetTop,
            left: el.offsetLeft
        }
    },
    /**
        * Filter elements by given callback
        * @param  {array} arg1      Array thay need to filtered
        * @param  {function} arg2   Callback function
        * @return {array}           Filtered Array
    */
    filter: function(arr, callback) {
        var out = Array.from(arr).filter(function(el){
            return callback(el);
        });

        return out;
    },
    /**
        * Remove class form DOM node
        * @param  {string} arg1     DOM element selector
        * @param  {string} arg2     Class that fn need to remove
        * @return {[type]}          Undefined
    */
    removeClass: function(selector, removeClass) {
        var el = document.querySelector(selector);
        return el && el.classList.remove(removeClass); 
    },
    /**
        * Add class to DOM node
        * @param  {string} arg1     DOM element selector
        * @param  {string} arg2     Class that fn need to add
        * @return {[type]}          Undefined
    */
    addClass: function(selector, addClass) {
        var el = document.querySelector(selector);
        return el && el.classList.add(addClass)
    },
    /**
        * Call callback to each value of obj
        * @param  {object} arg1     Object to bypass
        * @param  {function} arg2   Function to call for each element
        * @return {[type]}          Undefined
    */
    each: function(obj, callback) {
        var arr = Object.values(obj)
        arr.forEach(callback)
    },
    /**
         * Remove node from DOM
         * @param  {node} arg1 DOM el
    */
    remove: function(el) {
        return el.parentNode.removeChild(el);
    },
    /**
        * Find closest el by given selector and type
        * @param  {string} arg1     Selector for element
        * @param  {string} arg2     Search selector
        * @param  {string} arg3     Type of selector: id, tag or class
        * @return {node}            Return el or null
    */
    closest: function(selector, closest, type) {
        var allParents = this.getParents(selector, closest);
        var result = allParents.find(function(el) {
            if(type === 'class'){
                return el.className.match(closest)
            } else if(type === 'tag') {
                return el.tagName === closest.toUpperCase();
            } else if(type === 'id') {
                return el.id === closest
            }
        })
        return result ? result : null;
    },
    /**
        * Find all children for given parent
        * @param  {string} arg1     Selector for parents
        * @param  {string} arg2     Selector for childs
        * @return {array}           Array with all the children by given selector
    */
    getAllChilds: function(parentSelector, childSelector){
        var parents = this.findAll(parentSelector);
        var result = [];

        parents.forEach(function(el) {
            result.push(el.querySelectorAll(childSelector));
        })

        return result;
    }
}

/*global PxaDealersMaps */

function PxaDealersMapsRender() {

    var self = this;

    self.mapSettings = null;
    self.pluginSettings = null;

    self.dealers = [];

    self.mapDom = null;
    self.mapGoogle = null;
    self.mapParent = null;
    self.panorama = null;

    self.markerClusterer = null;
    self.markers = [];

    self.bounds = null;
    self.infoWindow = null;

    /**
     * Init main options
     *
     * @param mapSettings
     * @param pluginSettings
     * @param map
     */
    self.init = function (mapSettings, pluginSettings, map) {


        self.mapSettings = Object.assign({}, PxaDealersSettings, mapSettings);
        self.pluginSettings = pluginSettings;

        if (map.length === 1) {

            self.mapDom = map;
            // self.mapParent = convertJq.getParents(map[0], self.mapSettings.mapParentWrapper);
            self.mapParent = convertJq.getParent(map[0]);

            self.initMap();

            /**
             * show on map dealer
             */
            var allElements = convertJq.findAll(self.mapParent, self.mapSettings.showOnMapSelector)
            allElements.forEach(function(el){
                el.addEventListener('click', function(e){
                    self.clickShowOnMap(e, this)
                })
            })

            /**
             * check if there are any filters available
             */
            self.initFilters();

            /**
             * switch to street view
             */

            self.mapDom[0].addEventListener('click', function (event) {
                var target = event.target;
                if(target.className.match('street-view-link')){
                    event.preventDefault();
                    var uid = target.getAttribute('data-marker-id');

                    if (uid) {
                        self.switchToStreetView(uid);
                    }
                }
            });
        }
    };

    /**
     * Init map and all required variables
     *
     */
    self.initMap = function () {
        if (self.mapSettings.dealers.length === 0 && parseInt(self.pluginSettings.hideIfEmpty)) {
            convertJq.hide(self.mapDom[0]);

            return;
        }

        self.infoWindow = new google.maps.InfoWindow();

        // Options
        var mapOptions = {
            mapTypeControlOptions: {
                mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
            }
        };

        // Create map
        self.mapGoogle = new google.maps.Map(convertJq.getById(self.mapDom[0].getAttribute('id')), mapOptions);

        // Set map styles
        // Check if styles was parsed correctly
        var stylesIsJSON = true;
        try {
            var stylesObj = JSON.parse(self.pluginSettings.stylesJSON);
        } catch (err) {
            stylesIsJSON = false;
        }

        if (stylesIsJSON) {
            var styledMap = new google.maps.StyledMapType(stylesObj, {name: self.pluginSettings.name});

            self.mapGoogle.mapTypes.set('map_style', styledMap);
            self.mapGoogle.setMapTypeId('map_style');
        }

        // Panorama
        self.panorama = self.mapGoogle.getStreetView();


        // Enable marker cluster
        if (self.isMarkerClustererEnable()) {
            self.markerClusterer = new MarkerClusterer(
                self.mapGoogle,
                [],
                {
                    maxZoom: parseInt(self.pluginSettings.markerClusterer.maxZoom),
                    imagePath: self.pluginSettings.markerClusterer.imagePath
                }
            );
        }

        // Generate markers
        convertJq.each(self.mapSettings.dealers, function (dealer, index) {
            if ((dealer['lat'] !== '') && (dealer['lng'] !== '')) {
                self.generateMarker(dealer, function (marker) {
                    // save marker
                    self.markers[dealer['uid']] = marker;

                    if (self.isMarkerClustererEnable()) {
                        self.markerClusterer.addMarker(marker, true);
                    }
                });
            }
        })

        self.fitBounds();
    };

    /**
     * Click on link to show more detail on map
     *
     * @param e
     * @param link
     */
    self.clickShowOnMap = function (e, link) {
        e.preventDefault();

        //var uid = parseInt(link.data('dealer-uid'));
        var uid = parseInt(link.getAttribute('data-dealer-uid'));

        self.mapGoogle.setZoom(parseInt(self.pluginSettings.zoomOnShow));
        self.mapGoogle.setCenter(self.markers[uid].getPosition());

        self.infoWindow.setContent(self.getInfoWindowHtml(self.mapSettings.dealers[uid]));
        self.infoWindow.open(self.map, self.markers[uid]);

        convertJq.removeClass(self.mapSettings.dealerItems + '.' + self.mapSettings.showOnMapActiveClass ,self.mapSettings.showOnMapActiveClass);
        convertJq.addClass('#dealer-' + uid, self.mapSettings.showOnMapActiveClass)

        var scrollFix = parseInt(convertJq.getDocWidth() > 991 ? self.pluginSettings.scrollFix : self.pluginSettings.scrollFixMobile);

        // [TASK] animate function

        //document.querySelectorAll('html, body')
        console.log(self.mapParent)
        $('html,body').animate({
            scrollTop: (convertJq.offset(self.mapParent).top + scrollFix)
        }, 320);
    };

    /**
     * Get marker for map
     *
     * @param dealer
     * @param callback
     */
    self.generateMarker = function (dealer, callback) {
        var pos = new google.maps.LatLng(dealer['lat'], dealer['lng']),
            infoWindowHtml;

        var dealerDom = document.querySelector('#dealer-' + dealer.uid),
            markerType = dealerDom.getAttribute('marker-type'),
            markerIcon;

        if (typeof self.pluginSettings.markerTypes[markerType] !== 'undefined') {
            markerIcon = self.pluginSettings.markerTypes[markerType];
        } else {
            markerIcon = self.pluginSettings.markerTypes['default']
        }

        infoWindowHtml = self.getInfoWindowHtml(dealer);

        var marker = new google.maps.Marker({
            map: self.mapGoogle,
            position: pos,
            animation: google.maps.Animation.DROP,
            icon: markerIcon
        });

        google.maps.event.addListener(marker, 'click', function () {
            self.infoWindow.setContent(infoWindowHtml);
            self.infoWindow.open(self.mapGoogle, marker);
        });

        callback(marker);
    };

    /**
     * Generate html for dealer popup info on map
     *
     * @param dealer
     * @returns {string}
     */
    self.getInfoWindowHtml = function (dealer) {
        var infoWindowHtml = '',
            i,
            fields;

        // html for left part of info window, mostly regular fields
        var leftPart = [];
        leftPart.push("<strong>" + dealer['name'] + "</strong>");

        // simple fields
        for (i = 0, fields = ['address', 'zipcode', 'city']; i < fields.length; i++) {
            if (dealer[fields[i]] !== '') {
                leftPart.push(dealer[fields[i]]);
            }
        }

        // country
        if (dealer['countryName'] !== '') {
            leftPart.push("<b>" + dealer['countryName'] + "</b>");
        }

        // telephone
        if (dealer['phone'] !== '') {
            var phone = '<a href="tel:{0}">{1}</a>'.format(dealer['phoneClear'], (PxaDealersMaps.FE.translate('infoWindowPhone') + ' ' + dealer['phone'] ));
            leftPart.push(phone);
        }

        // email
        if (dealer['email'] !== '') {
            leftPart.push(dealer['email']);
        }

        // links fields
        for (i = 0, fields = ['website', 'link']; i < fields.length; i++) {
            if (dealer[fields[i]] !== '') {
                leftPart.push(dealer[fields[i]]);
            }
        }

        var rightPart = '';

        // street view
        if (typeof dealer['showStreetView'] !== 'undefined' && dealer['showStreetView'] === true) {
            rightPart += '<div class="image-street-preview">';
            rightPart += '<a href="javascript: {}" class="street-view-link" data-marker-id="{0}" class="street-switch-trigger website-link">'.format(dealer['uid']);
            rightPart += '<img src="https://maps.googleapis.com/maps/api/streetview?size=90x70&location={0},{1}&key={2}"/><br>'.format(
                dealer['lat'],
                dealer['lng'],
                self.pluginSettings.googleJavascriptApiKey
            );
            rightPart += PxaDealersMaps.FE.translate('streetView');
            rightPart += '</a>';
            rightPart += '</div>';
        }

        infoWindowHtml += '<div class="google-map-marker"><table><tr>';

        // left part
        infoWindowHtml += '<td>' + leftPart.join('<br>') + '</td>';
        //right part
        if (rightPart !== '') {
            infoWindowHtml += '<td>' + rightPart + '</td>';
        }
        //close tags
        infoWindowHtml += '</tr></table></div>';

        return infoWindowHtml;
    };

    /**
     * Switch map to street view
     *
     * @param uid
     */
    self.switchToStreetView = function (uid) {
        var position = self.markers[uid].getPosition();

        self.panorama.setPosition(position);
        self.panorama.setPov(
            {
                heading: 265,
                pitch: 0
            }
        );

        self.panorama.setVisible(true);
    };

    /**
     * Check if there are any filters in PxaDealersMaps.Filters
     *
     */
    self.initFilters = function () {
        if (typeof PxaDealersMaps.Filters !== 'undefined') {

            var currentFilters = PxaDealersMaps.Filters;

            for (var key in currentFilters) {
                if (!currentFilters.hasOwnProperty(key)) continue;

                if (currentFilters[key].getType() === 'checkbox' || currentFilters[key].getType() === 'selectbox') {

                    // var filterItems = currentFilters[key].getjQueryObject().find(currentFilters[key].getFilterElementSelector());
                    var filterItems = convertJq.findAll(currentFilters[key].getjQueryObject(), currentFilters[key].getFilterElementSelector());

                    // save items
                    PxaDealersMaps.Filters[key].filterItems = filterItems;

                    // ff fix
                    if (currentFilters[key].getType() === 'selectbox') {
                        filterItems.prop('selectedIndex', 0);
                    } else {
                        filterItems.forEach(item => {
                          item.checked = false;
                        });
                    }

                    filterItems.forEach(el => {
                        el.addEventListener(currentFilters[key].getType() === 'selectbox' ? 'change' : 'click', function () {
                          self.doFiltering(PxaDealersMaps.Filters);
                        });
                    });
                }
            }
        }
    };

    /**
     * Generate selector according to filters value
     *
     * @param filters
     */
    self.doFiltering = function (filters) {
        var selectors = [];

        for (var key in filters) {
            if (!filters.hasOwnProperty(key)) continue;
            var currentFilterSelectors = [];
            if (filters[key].getType() === 'checkbox') {
                var filteringPrefix = filters[key].getFilteringPrefix();
                var filters = filters[key].filterItems;
            
                var checked = convertJq.filter(filters, function(el) {
                  return el.checked
                });

                checked.forEach(function(el) {
                    var val = el.value;
                    if (val !== '' && val !== '0') {
                        // if value is comma-separated
                        var valSplit = val.split(',');

                        for (var i = 0; i < valSplit.length; i++) {
                            currentFilterSelectors.push(filteringPrefix + valSplit[i]);
                        }
                    }
                });
            } else if (filters[key].getType() === 'selectbox') {
                var val = filters[key].filterItems.val();

                if (val !== '' && val !== '0') {
                    currentFilterSelectors.push(filters[key].getFilteringPrefix() + val);
                }
            }

            selectors.push(currentFilterSelectors);
        }

        var selectorString = self.buildSelector(selectors);

        //check all dealers
        var allDealers = document.querySelector('.pxa-dealers-list') && document.querySelector('.pxa-dealers-list').children;
        var allDealersArr = allDealers ? Array.from(document.querySelector('.pxa-dealers-list').children) : false

        //hide dealers when it doesn't have cat-{uid} class
        if(allDealersArr) {
            allDealersArr.forEach(function(dealer) {
                if (selectorString.length > 0) {
                    if (dealer.matches(selectorString)) {
                        dealer.style.display = 'block';
                    } else {
                        dealer.style.display = 'none';
                    }
                } else {
                    dealer.style.display = 'block';
                }

            });
        }
    };

    /**
     * Filter hidden and visible markers
     */

    //[TASK] Check this
    self.processMarkersState = function (filteredItems) {
        var allItems = convertJq.findAll(self.mapSettings.dealerItems),
            visibleItems = [],
            hiddenItems = [];

        var visibleSelector = '';

        for (var i = 0; i < filteredItems.length; i++) {
            visibleSelector += '#' + filteredItems[i].element.id + ((filteredItems.length - 1) === i ? '' : ',');
        }

        if (visibleSelector !== '') {
            visibleItems = $(visibleSelector);
        }

        if (visibleItems.length > 0) {
            hiddenItems = allItems.not(visibleItems);

            if (parseInt(self.pluginSettings.hideIfEmpty)) {
                convertJq.show(self.mapDom[0])
            }
        } else {
            hiddenItems = allItems;

            if (parseInt(self.pluginSettings.hideIfEmpty)) {
                convertJq.hide(self.mapDom[0])
            }
        }

        hiddenItems.forEach(function (el) {
            var uid = el.dataset.uid;

            if(typeof self.markers[uid] !== 'undefined') {
                self.markers[uid].setVisible(false);
            }
        });


        visibleItems.forEach(function (el) {
            var uid = el.dataset.uid;

            if(typeof self.markers[uid] !== 'undefined') {
                self.markers[uid].setVisible(true);
            }
        });

        self.repaintMarkerClusterer();
        self.fitBounds();
    };

    /**
     * Repaint markers clusterer
     */
    self.repaintMarkerClusterer = function () {
        if (self.isMarkerClustererEnable()) {
            var visibleMarkers = [];

            for (var key in  self.markers) {
                if (!self.markers.hasOwnProperty(key)) continue;

                if (self.markers[key].getVisible()) {
                    visibleMarkers.push(self.markers[key]);
                }
            }

            self.markerClusterer.clearMarkers();
            if (visibleMarkers.length !== 0) {
                self.markerClusterer.addMarkers(visibleMarkers, true);
            }
        }
    };

    /**
     * Check if markerClusterer enable
     */
    self.isMarkerClustererEnable = function () {
        return (parseInt(self.pluginSettings.markerClusterer.enable, 10) === 1);
    };

    /**
     * Set map center and bounds according to visible markers
     */
    self.fitBounds = function () {
        self.bounds = new google.maps.LatLngBounds();

        var i = 0;

        for (var key in  self.markers) {
            if (!self.markers.hasOwnProperty(key)) continue;

            if (self.markers[key].getVisible()) {
                self.bounds.extend(self.markers[key].getPosition());
                i++;
            }
        }

        /// Fit bounds
        if (i === 1) {
            // get first
            for (var first in self.markers) {
                if (self.markers[first].getVisible()) {
                    // break on first visible
                    break;
                }
            }
            self.mapGoogle.setZoom(parseInt(self.pluginSettings.zoomOnShow));
            self.mapGoogle.setCenter(self.markers[first].getPosition());
        } else if (i > 1) {
            self.mapGoogle.fitBounds(self.bounds);
            self.mapGoogle.setCenter(self.bounds.getCenter());
        } else if (self.mapSettings.searchCenter['lat'] && self.mapSettings.searchCenter['lng']) {
            self.mapGoogle.setZoom(parseInt(self.pluginSettings.zoomOnShow));
            self.mapGoogle.setCenter(new google.maps.LatLng(
                self.mapSettings.searchCenter['lat'],
                self.mapSettings.searchCenter['lng']
            ));
        } else {
            self.mapGoogle.setZoom(1);
            self.mapGoogle.setCenter(new google.maps.LatLng(0, 0));
        }
    };

    /**
     * Generate a jquery selector string for current active filters
     *
     * @param selectors
     * @return {string}
     */
    self.buildSelector = function (selectors) {
        var jquerySelectors = [];

        // sort from bigger to smaller
        selectors.sort(function (itemA, itemB) {
            return itemA.length < itemB.length;
        });

        for (var i = 0; i < selectors.length; i++) {
            if (jquerySelectors.length === 0) {
                jquerySelectors = selectors[i];
            } else {
                for (var k = 0; k < selectors[i].length; k++) {
                    for (var j = 0; j < jquerySelectors.length; j++) {
                        jquerySelectors[j] = jquerySelectors[j] + selectors[i][k];
                    }
                }
            }
        }

        return jquerySelectors.join(',');
    }
}

// $.fn.pxaDealers = function (mapSettings, pluginSettings) {
//     var pxaDealersMapsRenderer = new PxaDealersMapsRender();
//     pxaDealersMapsRenderer.init(mapSettings, pluginSettings, this);


//     return this;
// };
var pxaDealers = function (mapSettings, pluginSettings, selector) {
    var pxaDealersMapsRenderer = new PxaDealersMapsRender();
    pxaDealersMapsRenderer.init(mapSettings, pluginSettings, selector);
};

// pxaDealers.call(document.querySelectorAll(PxaDealersMaps.FE.getMapSelector()));

var PxaDealersSettings = {
    dealerItems: '.dealer-item',
    showOnMapSelector: '.show-on-map-link',
    showOnMapActiveClass: 'active',
    mapParentWrapper: '.pxa-dealers-wrapper',
    itemsListWrapper: '.pxa-dealers-list'
};
