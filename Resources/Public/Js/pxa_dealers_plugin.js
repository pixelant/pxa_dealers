(function (w, $, google) {
    var document = w.document,
        PxaDealersMaps = w.PxaDealersMaps || {};

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
                        return typeof args[number] != 'undefined'
                            ? args[number]
                            : match
                            ;
                    });
                };
            }
        },

        /**
         * Get translations
         *
         * @param key
         * @return {*}
         */
        translate: function (key) {
            if (typeof TYPO3.lang["js." + key] !== "undefined") {
                return TYPO3.lang["js." + key];
            } else {
                return "";
            }
        }
    };

    function PxaDealersMapsRender() {

        var self = this;

        self.settings = null;

        self.dealers = [];

        self.mapDom = null;
        self.mapGoogle = null;
        self.mapParent = null;
        self.panorama = null;

        self.markerClusterer = null;
        self.markers = [];

        self.bounds = null;
        self.infoWindow = null;
        self.panorama = null;

        self.isotope = null;

        /**
         * Init main options
         *
         * @param settings
         * @param map
         */
        self.init = function (settings, map) {

            self.settings = $.extend({}, $.fn.pxaDealers.settings, settings);

            if (map.length === 1) {
                PxaDealersMaps.FE.initHelperFunctions();

                self.mapDom = map;
                self.mapParent = map.parents(self.settings.mapParentWrapper);

                self.initMap();

                /**
                 * show on map dealer
                 */
                self.mapParent.find(self.settings.showOnMapSelector).on("click", function (e) {
                    self.clickShowOnMap(e, $(this));
                });

                /**
                 * check if there are any filters available
                 */
                self.initFilters();

                /**
                 * switch to street view
                 */
                $(document).on('click', '.street-view-link', function (event) {
                    event.preventDefault();
                    var uid = $(this).data("marker-id");

                    if (uid) {
                        self.switchToStreetView(uid);
                    }
                });
            }
        };

        /**
         * Init map and all required variables
         *
         */
        self.initMap = function () {
            self.infoWindow = new google.maps.InfoWindow();

            // Options
            var mapOptions = {
                mapTypeControlOptions: {
                    mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
                }
            };

            // Create map
            self.mapGoogle = new google.maps.Map(document.getElementById(self.mapDom.attr("id")), mapOptions);

            // Set map styles
            // Check if styles was parsed correctly
            var stylesIsJSON = true;
            try {
                var stylesObj = $.parseJSON(PxaDealersMaps.settings.stylesJSON);
            } catch (err) {
                stylesIsJSON = false;
            }

            if (stylesIsJSON) {
                var styledMap = new google.maps.StyledMapType(stylesObj, {name: PxaDealersMaps.settings.name});

                self.mapGoogle.mapTypes.set('map_style', styledMap);
                self.mapGoogle.setMapTypeId('map_style');
            }

            // Panorama
            self.panorama = self.mapGoogle.getStreetView();


            // Enable marker cluster
            if (PxaDealersMaps.settings.markerClusterer.enable == 1) {
                self.markerClusterer = new MarkerClusterer(self.mapGoogle, [], {
                    maxZoom: parseInt(PxaDealersMaps.settings.markerClusterer.maxZoom),
                    imagePath: PxaDealersMaps.settings.markerClusterer.imagePath
                });
            }

            // Generate markers
            $.each(self.settings.dealers, function (index, dealer) {
                if ((dealer['lat'] != '') && (dealer['lng'] != '')) {

                    self.generateMarker(dealer, function (marker) {
                        // save marker
                        self.markers[dealer['uid']] = marker;

                        if (PxaDealersMaps.settings.markerClusterer.enable == 1) {
                            self.markerClusterer.addMarker(marker, true);
                        }
                    });
                }
            });

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

            var uid = parseInt(link.data("dealer-uid"));

            self.mapGoogle.setZoom(parseInt(PxaDealersMaps.settings.zoomToShowMarker));
            self.mapGoogle.setCenter(self.markers[uid].getPosition());

            self.infoWindow.setContent(self.getInfoWindowHtml(self.settings.dealers[uid]));
            self.infoWindow.open(self.map, self.markers[uid]);

            $(self.settings.dealerItems + "." + self.settings.showOnMapActiveClass).removeClass(self.settings.showOnMapActiveClass);
            $("#dealer-" + uid).addClass(self.settings.showOnMapActiveClass);

            var scrollFix = parseInt($(document).width() > 991 ? PxaDealersMaps.settings.scrollFix : PxaDealersMaps.settings.scrollFixMobile);

            $('html,body').animate({
                scrollTop: (self.mapParent.offset().top + scrollFix)
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
                infoWindowHtml = "";

            var dealerDom = $("#dealer-" + dealer.uid),
                markerType = dealerDom.data("marker-type"),
                markerIcon;

            if (typeof PxaDealersMaps.settings.markerTypes[markerType] !== "undefined") {
                markerIcon = PxaDealersMaps.settings.markerTypes[markerType];
            } else {
                markerIcon = PxaDealersMaps.settings.markerTypes["default"]
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
            var infoWindowHtml = "";

            // html for left part of info window, mostly regular fields
            var leftPart = [];
            leftPart.push("<strong>" + dealer['name'] + "</strong>");

            // simple fields
            for (var i = 0, fields = ["address", "zipcode", "city"]; i < fields.length; i++) {
                if (dealer[fields[i]] != "") {
                    leftPart.push(dealer[fields[i]]);
                }
            }

            // country
            if (dealer["countryName"] != "") {
                leftPart.push("<b>" + dealer["countryName"] + "</b>");
            }

            // telephone
            if (dealer["phone"] != "") {
                var phone = '<a href="tel:{0}">{1}</a>'.format(dealer['phoneClear'], (PxaDealersMaps.FE.translate("infoWindowPhone") + " " + dealer['phone'] ));
                leftPart.push(phone);
            }

            // email
            if (dealer["email"] != "") {
                leftPart.push(dealer['email']);
            }

            // links fields
            for (var i = 0, fields = ["website", "link"]; i < fields.length; i++) {
                if (dealer[fields[i]] != "") {
                    leftPart.push(dealer[fields[i]]);
                }
            }

            var rightPart = '';

            // street view
            if (typeof dealer['showStreetView'] != 'undefined' && dealer['showStreetView'] == 1) {
                rightPart += '<div class="image-street-preview">';
                rightPart += '<a href="javascript: {}" class="street-view-link" data-marker-id="{0}" class="street-switch-trigger website-link">'.format(dealer['uid']);
                rightPart += '<img src="http://maps.googleapis.com/maps/api/streetview?language={0}&size=90x70&location={1},{2}&key={3}"/><br>'.format(
                    PxaDealersMaps.settings.googleJavascriptApiLanguage,
                    dealer["lat"],
                    dealer["lng"],
                    PxaDealersMaps.settings.googleJavascriptApiKey
                );
                rightPart += '<span>' + PxaDealersMaps.FE.translate('streetView') + '</span>';
                rightPart += '</a>';
                rightPart += '</div>';
            }

            infoWindowHtml += '<div class="google-map-marker"><table><tr>';

            // left part
            infoWindowHtml += '<td>' + leftPart.join("<br>") + '</td>';
            //right part
            if (rightPart != "") {
                infoWindowHtml += '<td>' + rightPart + '</td>';
            }
            //close tags
            infoWindowHtml += '</tr></table></div>';

            return infoWindowHtml
        };

        /**
         * Switch map to street view
         *
         * @param uid
         */
        self.switchToStreetView = function (uid) {
            var position = self.markers[uid].getPosition();

            self.panorama.setPosition(position);
            self.panorama.setPov({
                    heading: 265,
                    pitch: 0
                }
            );

            self.panorama.setVisible(true);
        };

        /**
         * Check if there are any filters in PxaDealersMaps.Filters["filter-{uid of currecnt map}"]
         *
         */
        self.initFilters = function () {
            if (typeof PxaDealersMaps.Filters["filter-" + self.settings.uid] !== "undefined") {

                self.isotope = $(self.settings.itemsListWrapper).isotope({
                    itemSelector: '.isotope-item',
                    layoutMode: 'fitRows'
                });

                // when filtering done
                self.isotope.on('arrangeComplete', function (event, filteredItems) {
                    self.processMarkersState(filteredItems);
                });

                var currentFilter = PxaDealersMaps.Filters["filter-" + self.settings.uid];

                for (var key in currentFilter) {
                    if (!currentFilter.hasOwnProperty(key)) continue;

                    if (currentFilter[key].type === "checkbox" || currentFilter[key].type === "selectbox") {
                        var filterWrapper = $(currentFilter[key].idPrefix + key);

                        if (filterWrapper.length > 0) {
                            var filterItems = filterWrapper.find(currentFilter[key].item);
                            // save items
                            PxaDealersMaps.Filters["filter-" + self.settings.uid][key].filterItems = filterItems;

                            // ff fix
                            if (currentFilter[key].type === "selectbox") {
                                filterItems.prop("selectedIndex",0);
                            } else {
                                filterItems.attr("checked",false);
                            }

                            filterItems.on(currentFilter[key].type === "selectbox" ? "change" : "click", function () {
                                self.doFiltering(PxaDealersMaps.Filters["filter-" + self.settings.uid]);
                            });
                        }
                    }
                }
            }
        };

        /**
         * Generate jquery selector according to filters value and run isotope
         *
         * @param filters
         */
        self.doFiltering = function (filters) {
            var selectors = [];

            for (var key in filters) {
                if (!filters.hasOwnProperty(key)) continue;
                var currentFilterSelectors = [];

                if (filters[key].type === "checkbox") {
                    var checked = filters[key].filterItems.filter(":checked");

                    $.each(checked, function () {
                        var val = $(this).val();
                        if (val != "" && val != "0") {
                            // if value is comma-separated
                            var valSplit = val.split(",");

                            for (var i = 0; i < valSplit.length; i++) {
                                currentFilterSelectors.push(filters[key].filteringPrefix + valSplit[i]);
                            }
                        }
                    });
                } else if (filters[key].type === "selectbox") {
                    var val = filters[key].filterItems.val();

                    if (val != "" && val != "0") {
                        currentFilterSelectors.push(filters[key].filteringPrefix + val);
                    }
                }

                selectors.push(currentFilterSelectors);
            }

            var selectorString = self.buildSelector(selectors);

            self.isotope.isotope({
                filter: selectorString
            });
        };

        /**
         * Filter hidden and visible markers
         */
        self.processMarkersState = function (filteredItems) {
            var allItems = $(self.settings.dealerItems),
                visibleItems = [],
                hiddenItems = [];

            var visibleSelector = "";

            for (var i = 0; i < filteredItems.length; i++) {
                visibleSelector += "#" + filteredItems[i].element.id + ((filteredItems.length - 1) === i ? "" : ",");
            }

            if (visibleSelector !== "") {
                visibleItems = $(visibleSelector);
            }

            if (visibleItems.length > 0) {
                hiddenItems = allItems.not(visibleItems);
            } else {
                hiddenItems = allItems;
            }

            $.each(hiddenItems, function () {
                var $this = $(this),
                    uid = $this.data("uid");

                self.markers[uid].setVisible(false);
            });


            $.each(visibleItems, function () {
                var $this = $(this),
                    uid = $this.data("uid");

                self.markers[uid].setVisible(true);
            });

            self.repaintMarkerClusterer();
            self.fitBounds();
        };

        /**
         * Repaint markers clusterer
         */
        self.repaintMarkerClusterer = function () {
            if (PxaDealersMaps.settings.markerClusterer.enable) {
                var visibleMarkers = [],
                    hiddenMarkers = [];

                for (var key in  self.markers) {
                    if (!self.markers.hasOwnProperty(key)) continue;

                    if (self.markers[key].getVisible()) {
                        visibleMarkers.push(self.markers[key]);
                    } else {
                        hiddenMarkers.push(self.markers[key]);
                    }
                }

                if (hiddenMarkers.length > 0 && visibleMarkers.length !== 0) {
                    self.markerClusterer.removeMarkers(hiddenMarkers);
                }

                if (visibleMarkers.length !== 0) {
                    self.markerClusterer.addMarkers(visibleMarkers, true);
                } else {
                    self.markerClusterer.clearMarkers();
                }

                self.markerClusterer.redraw();
            }
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
            if (i == 1) {
                self.mapGoogle.fitBounds(self.bounds);
                var listener = google.maps.event.addListener(self.mapGoogle, "idle", function () {
                    self.mapGoogle.setZoom(12);
                    google.maps.event.removeListener(listener);
                });
            } else if (i > 1) {
                self.mapGoogle.fitBounds(self.bounds);
                self.mapGoogle.setCenter(self.bounds.getCenter());
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

            return jquerySelectors.join(",");
        }
    }


    $.fn.pxaDealers = function (settings) {
        var pxaDealersMapsRenderer = new PxaDealersMapsRender();
        pxaDealersMapsRenderer.init(settings, this);

        // save object
        PxaDealersMaps.Maps["map-" + settings.uid]["objectRenderer"] = pxaDealersMapsRenderer;

        return this;
    };

    $.fn.pxaDealers.settings = {
        dealerItems: ".dealer-item",
        showOnMapSelector: ".show-on-map-link",
        showOnMapActiveClass: 'active',
        mapParentWrapper: ".pxa-dealers-wrapper",
        itemsListWrapper: ".pxa-dealers-list"
    };

    w.PxaDealersMaps = PxaDealersMaps;

})(window, jQuery, google);