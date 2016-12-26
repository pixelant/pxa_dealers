(function (w, $) {
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
            self.bounds = new google.maps.LatLngBounds();
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

                    // save visible on map dealer
                    self.settings.dealers[index]['isVisible'] = true;
                } else {
                    self.settings.dealers[index]['isVisible'] = false;
                }
            });


            /// Fit bounds
            if (self.dealers.length == 1) {
                self.mapGoogle.fitBounds(self.bounds);
                var listener = google.maps.event.addListener(self.mapGoogle, "idle", function () {
                    self.mapGoogle.setZoom(12);
                    google.maps.event.removeListener(listener);
                });
            } else {
                self.mapGoogle.fitBounds(self.bounds);
                self.mapGoogle.setCenter(self.bounds.getCenter());
            }
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

            self.repaintMarkerClusterer(false);

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

            self.bounds.extend(pos);

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

                var currentFilter = PxaDealersMaps.Filters["filter-" + self.settings.uid];

                for (var key in currentFilter) {
                    if (!currentFilter.hasOwnProperty(key)) continue;

                    if (currentFilter[key].type === "checkbox" || currentFilter[key].type === "selectbox") {
                        var filterWrapper = $(currentFilter[key].idPrefix + key);

                        if (filterWrapper.length > 0) {
                            var filterItems = filterWrapper.find(currentFilter[key].item);
                            // save items
                            PxaDealersMaps.Filters["filter-" + self.settings.uid][key].filterItems = filterItems;

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

                /**
                 * now if there is already selector create AND constraint
                 */
                if (selectors.length > 0) {
                    for (var j = 0; j < currentFilterSelectors.length; j++) {
                        for (var i = 0; i < selectors.length; i++) {
                            selectors[i] = selectors[i] + currentFilterSelectors[j];
                        }
                    }
                } else {
                    selectors = currentFilterSelectors;
                }
            }

            var selectorString = selectors.join(",");

            self.isotope.isotope({
                filter: selectorString
            });
            
            self.processMarkersState(selectorString);
        };

        /**
         * Filter hidden and visible markers
         *
         * @param selectorString
         */
        self.processMarkersState = function (selectorString) {
            var allItems = $(self.settings.dealerItems),
                visibleItems = selectorString != "" ? $(selectorString) : allItems;

            if (selectorString != "") {
                var hiddenItems = allItems.not(selectorString);

                $.each(hiddenItems, function () {
                    var $this = $(this),
                        uid = $this.data("uid");

                    self.markers[uid].setVisible(false);
                });
            }


            $.each(visibleItems, function () {
                var $this = $(this),
                    uid = $this.data("uid");

                self.markers[uid].setVisible(true);
            });

            self.repaintMarkerClusterer(true);
        };

        /**
         * Repaint markers clusterer
         *
         * @param checkVisible
         */
        self.repaintMarkerClusterer = function(checkVisible) {
            if (PxaDealersMaps.settings.markerClusterer.enable) {
                if (checkVisible) {
                    for (var key in  self.markers) {
                        if (!self.markers.hasOwnProperty(key)) continue;

                        self.markerClusterer.removeMarker(self.markers[key], true);

                        if (self.markers[key].getVisible()) {console.log(self.markers[key]);
                            self.markerClusterer.addMarker(self.markers[key], true);
                        }
                    }
                }

                self.markerClusterer.repaint();
            }
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

})(window, jQuery);

$(document).ready(function () {

    if (typeof PxaDealersMaps.Maps !== "undefined") {

        for (var key in PxaDealersMaps.Maps) {
            if (!PxaDealersMaps.Maps.hasOwnProperty(key)) continue;

            $('#pxa-dealers-map-' + PxaDealersMaps.Maps[key]['uid']).pxaDealers(PxaDealersMaps.Maps[key]);
        }
    }
});

if ($(".tx-pxa-dealers").length > 0) {

    var countriesList;


    var FB_COUNTRY = 0;
    var FB_STATE = 1;
    var FB_CITY = 2;
    var FB_MARKERS = 3;
    var FB_NONE = 4;

// Prototypes
    if (typeof String.prototype.startsWith != 'function') {
        // see below for better implementation!
        String.prototype.startsWith = function (str) {
            return this.indexOf(str) == 0;
        };
    }

    if (typeof Array.prototype.getColumn != 'function') {
        Array.prototype.getColumn = function (name) {
            return this.map(function (el) {
                // gets corresponding 'column'
                if (el.hasOwnProperty(name)) return el[name];
                // removes undefined values
            }).filter(function (el) {
                return typeof el != 'undefined';
            });
        };
    }

    if (typeof Array.prototype.diff != 'function') {
        Array.prototype.diff = function (a) {
            return this.filter(function (i) {
                return a.indexOf(i) < 0;
            });
        };
    }

    function arrayIntersect(a, b) {
        for (var i = a.length - 1; i >= 0; i--) {
            if ($.inArray(a[i], b) !== -1) {
                return true;
            }
        }

        return false;
    }

// Pxa dealers lib
    function PxaDealers() {
        var self = this;

        self.pluginSettings = settings;
        self.dealers = {};
        self.labels = labels;
        self.countriesList = $.map(self.pluginSettings['mainCountries'].split(","), $.trim);
        self.allDealersList = "";
        self.originalDealersHeader = "";

        self.markerclusterer;
        self.markers = [];
        self.bounds = null;
        self.dealers = dealers;

        self.selectedCountry = "0";
        self.selectedCountryZone = "0";
        self.searchString = "";
        self.fitBoundsType = FB_MARKERS;
        self.extendedZipSearch = true;
        self.prefilteredList = {};

        self.lastFilteredDealers = [];
        self.lastSearchType = "";
        self.zipSearchType = "googleCall";

        self.filteredDealersUids = [];
        self.filteredDealers = [];

        /**
         * initializeMapPxaDealers
         */
        self.initializeMapPxaDealers = function () {

            self.bounds = new google.maps.LatLngBounds();
            self.infowindow = new google.maps.InfoWindow();

            // Options
            var mapOptions = {
                mapTypeControlOptions: {
                    mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
                }
            };

            // Create map
            self.map = new google.maps.Map(document.getElementById("pxa-dealers-map"), mapOptions);

            // Set map styles
            // Check if styles was parsed correctly
            var stylesIsJSON = true;
            try {
                var stylesObj = $.parseJSON(self.pluginSettings.map.stylesJSON);
            } catch (err) {
                stylesIsJSON = false;
            }

            if (stylesIsJSON) {
                var styledMap = new google.maps.StyledMapType(stylesObj, {name: self.pluginSettings.map.name});

                self.map.mapTypes.set('map_style', styledMap);
                self.map.setMapTypeId('map_style');
            }

            // Panorama
            self.panorama = self.map.getStreetView();

            // Styles
            var clusterStyles = [{
                textColor: 'black',
                url: 'typo3conf/ext/pxa_dealers/Resources/Public/Icons/MarkerClusterer/m1.png',
                height: 52,
                width: 53
            }, {
                textColor: 'black',
                url: 'typo3conf/ext/pxa_dealers/Resources/Public/Icons/MarkerClusterer/m2.png',
                height: 55,
                width: 56
            }, {
                textColor: 'black',
                url: 'typo3conf/ext/pxa_dealers/Resources/Public/Icons/MarkerClusterer/m3.png',
                height: 65,
                width: 66
            }
            ];

            // Enable marker clusterer
            if (self.pluginSettings.clusterMarkers == 1) {

                self.markerclusterer = new MarkerClusterer(self.map, [], {
                    maxZoom: parseInt(self.pluginSettings.map.markerClusterer.maxZoom),
                    gridSize: parseInt(self.pluginSettings.map.markerClusterer.gridSize),
                    styles: clusterStyles
                });
            }

            var dealersProcessed = [];


            // Generate markers
            $.each(self.dealers, function (index, dealer) {
                if ((dealer['lat'] != '') && (dealer['lng'] != '')) {

                    self.getAddress(dealer, function (marker) {
                        dealer['marker'] = marker;
                        self.markers[dealer['uid']] = dealer['marker'];
                        if (self.pluginSettings.clusterMarkers == 1) {
                            self.markerclusterer.addMarker(marker, true);
                        }
                    });

                    dealersProcessed.push(dealer);
                }
            });

            self.dealers = dealersProcessed;

            /// Fit bounds
            if (self.dealers.length == 1) {
                self.map.fitBounds(self.bounds);
                var listener = google.maps.event.addListener(self.map, "idle", function () {
                    self.map.setZoom(12);
                    google.maps.event.removeListener(listener);
                });
            } else {
                self.map.fitBounds(self.bounds);
                self.map.setCenter(self.bounds.getCenter());
            }

            var filterOn = FB_MARKERS;
            if ($(".pxa-dealers .dealer-countries").val() != 0 && $(".pxa-dealers .dealer-countries").val() !== undefined) {
                filterOn = FB_COUNTRY;
            }

            self.selectedCountry = $(".pxa-dealers .dealer-countries").val();
            self.selectedCountryZone = $(".pxa-dealers .dealer-country-states").val();
            self.searchString = $(".pxa-dealers .dealer-cityzip-search").val();
            self.fitBoundsType = filterOn;
            self.filterDealers();

        }

        // Get map marker
        self.getAddress = function (dealer, callback) {

            var markerType = $(".dealer-item[data-uid=" + dealer.uid + "]").data("marker-type");
            var markerIcon = self.pluginSettings.map.markerTypes[markerType];

            var pos = new google.maps.LatLng(dealer['lat'], dealer['lng']);

            var address = dealer['address'] ? '<br/>' + dealer['address'] : '';
            var zipcode = dealer['zipcode'] ? '<br/>' + dealer['zipcode'] : '';
            var city = dealer['city'] ? '<br/>' + dealer['city'] : '';

            var telephone = dealer['telephone'] ? "<br/><a href=\"tel:" + dealer['telephone_clear'] +
                "\">" + self.labels['infoWindowPhone'] + " " + dealer['telephone'] + "</a>" : '';
            var email = dealer['email'] ? "<br/><a href=\"mailto:" + dealer['email'] + "\">" +
                dealer['email'] + "</a>" : '';

            if (dealer['website']) {
                // Check if website starts from http
                var websiteUrl = dealer['website'];
                var websiteUrlTitle = websiteUrl;
                if (!(/^http:\/\//.test(dealer['website']))) {
                    websiteUrl = 'http://' + websiteUrl;
                }

                var website = "<br/><a href=" + websiteUrl + " target=\"_blank\" class=\"website-link\">" + websiteUrlTitle + '</a>';
            } else {
                var website = '';
            }

            var imageStreetPreviewContent = '<td><div class="image-street-preview">';
            imageStreetPreviewContent += '<a href="#streetview" data-marker-id="' + dealer['uid']
                + '" class="street-switch-trigger website-link">';
            imageStreetPreviewContent += '<img src="http://maps.googleapis.com/maps/api/streetview?language=' + self.pluginSettings.googleJavascriptApiLanguage + '&size=90x70&location=' +
                dealer['lat'] + ',' + dealer['lng'] + '&key=' + self.pluginSettings.googleJavascriptApiKey + '"/><br>';
            imageStreetPreviewContent += '<span>Streetview</span></a>';
            imageStreetPreviewContent += '</div></td>';

            var infowindowCont = "<div class=\"google-map-marker\">";
            infowindowCont += "<table><tr><td>";
            infowindowCont += "<strong>" + dealer['name'] + "</strong>";
            infowindowCont += address;
            infowindowCont += zipcode;
            infowindowCont += city;
            infowindowCont += telephone;
            infowindowCont += email;
            infowindowCont += website;
            if (typeof dealer['showStreetView'] != 'undefined' && dealer['showStreetView'] == 1) {
                infowindowCont += "STREET_MAP";
            }
            infowindowCont += "</div></tr></table>";

            var marker = new google.maps.Marker({
                map: self.map,
                position: pos,
                animation: google.maps.Animation.DROP,
                icon: markerIcon
            });

            google.maps.event.addListener(marker, 'click', function () {
                self.infowindow.setContent(infowindowCont);
                self.infowindow.open(self.map, marker);
            });

            if (typeof dealer['showStreetView'] != 'undefined' && dealer['showStreetView'] == 1) {
                var streetview = new google.maps.StreetViewService();
                streetview.getPanoramaByLocation(pos, 50, function (data, status) {
                    if (status == 'OK') {

                        infowindowCont = infowindowCont.replace("STREET_MAP", imageStreetPreviewContent);

                        google.maps.event.addListener(marker, 'click', function () {
                            self.infowindow.setContent(infowindowCont);
                            self.infowindow.open(self.map, marker);
                        });
                    } else {
                        infowindowCont = infowindowCont.replace("STREET_MAP", '');
                    }
                });
            } else {
                google.maps.event.addListener(marker, 'click', function () {
                    self.infowindow.setContent(infowindowCont);
                    self.infowindow.open(self.map, marker);
                });
            }

            self.bounds.extend(pos);

            callback(marker);
        }

        self.deg2rad = function (deg) {
            return deg * (Math.PI / 180)
        }

        self.getDistanceFromLatLonInKm = function (lat1, lon1, lat2, lon2) {
            var R = 6371; // Radius of the earth in km
            var dLat = self.deg2rad(lat2 - lat1);  // deg2rad below
            var dLon = self.deg2rad(lon2 - lon1);
            var a =
                    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                    Math.cos(self.deg2rad(lat1)) * Math.cos(self.deg2rad(lat2)) *
                    Math.sin(dLon / 2) * Math.sin(dLon / 2)
                ;
            var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            var d = R * c; // Distance in km
            return d;
        }

        self.findClosest = function (dealers, position) {

            if (typeof(position) === 'undefined') {
                positionLat = '59.339799';
                positionLong = "18.047922";
            } else {
                positionLat = position.lat();
                positionLong = position.lng();
            }

            $.each(dealers, function (index, dealer) {
                dealer['distance'] = self.getDistanceFromLatLonInKm(positionLat, positionLong, dealer['lat'], dealer['lng']);
            });

            var closest;
            closest = dealers.sort(function (a, b) {
                return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
            });

            closest = closest.slice(0, self.pluginSettings.resultLimit);

            return closest;
        }

        self.switchToStreetView = function (uid) {
            var position = self.markers[uid].getPosition();
            self.panorama.setPosition(position);
            self.panorama.setPov({
                    heading: 265,
                    zoom: 1,
                    pitch: 0
                }
            );
            self.panorama.setVisible(true);
            return false;
        }

        self.getSelectedCategories = function () {

            var selectedCategories = [];

            $('.pxa-dealers > .categories > .selected').each(function () {

                var categories = $(this).attr("data-category-uid").split(",");

                $(categories).each(function () {
                    selectedCategories.push(parseInt(this));
                });

            });

            return selectedCategories;
        }

        self.belongsToCountry = function (marker) {
            return (marker['country'] === self.selectedCountry
            || (self.selectedCountry === "row" && $.inArray(marker['country'], self.countriesList) === -1 )
            || self.selectedCountry == 0);
        }

        self.belongsToCountryZone = function (marker) {
            return (marker['countryZone'] == self.selectedCountryZone || self.selectedCountryZone == 0);
        }

        self.belongsToCategories = function (marker, selectedCategories) {

            var categories = JSON.parse(marker['categories']);

            if (selectedCategories.indexOf(-1) >= 0) {
                return true;
            }

            if (selectedCategories.length == 0) {
                return (self.pluginSettings.showUncategorizedIfNoCategorySelected == 1);
            } else {
                return arrayIntersect(categories, selectedCategories);
            }
        }

        // Filter markers
        self.filterDealers = function () {
            var filtered = self.getFilteredDealers();
            self.updateAll(filtered);
            return true;
        }

        // Get filtered markers
        self.getFilteredDealers = function () {

            var filtered = [];
            var selectedCategories = self.getSelectedCategories();

            $.each(self.dealers, function (index, dealer) {

                var isOk = [];

                if ($(".pxa-dealers .dealer-countries").length > 0) {
                    isOk.push(self.belongsToCountry(dealer));
                }

                if ($(".pxa-dealers .dealer-country-states").length > 0) {
                    isOk.push(self.belongsToCountryZone(dealer));
                }

                if ($(".pxa-dealers > .categories").length > 0) {
                    isOk.push(self.belongsToCategories(dealer, selectedCategories));
                }

                if ($.inArray(false, isOk) === -1) {
                    filtered.push(dealer);
                }

            });

            self.lastFilteredDealers = filtered;
            return filtered;

        }

        self.zipSearch = function (searchString, dealers, successDealers) {

            // THIS IS TEMPORARY SOLUTION. SHOULD BE CHANGED BY USING SOME POSTCODES/GEOPOSITION DATABASE OR API

            if (typeof(successDealers) === 'undefined') {
                successDealers = [];
            }

            if (searchString == '') {
                return false;
            }

            var expression = /[\. ,:-]+/g;
            var processedSearchString = searchString.replace(expression, '').toLowerCase();

            var failedDealers = [];

            $.each(dealers, function (index, dealer) {
                var processedZipcode = dealer['zipcode'].replace(expression, '').toLowerCase();

                if (processedZipcode.startsWith(processedSearchString)) {
                    successDealers.push(dealer);
                } else if (/^[a-zA-Z]+$/.test(processedZipcode.substr(0, 2)) && processedZipcode.substr(2).startsWith(processedSearchString)) {
                    successDealers.push(dealer);
                } else {
                    failedDealers.push(dealer);
                }
            });

            if (successDealers.length >= self.pluginSettings.resultLimit || searchString.length <= 1) {
                if (successDealers.length < self.pluginSettings.resultLimit && successDealers.length > 0) {
                    var newSuccessDealers = [];
                    $.each(successDealers, function (index, dealer) {
                        var position = new google.maps.LatLng(dealer['lat'], dealer['lng']);
                        var closestDealers = self.findClosest(self.lastFilteredDealers, position);
                        newSuccessDealers = $.merge(successDealers, closestDealers.diff(successDealers));
                        if (newSuccessDealers >= self.pluginSettings.resultLimit) {
                            return newSuccessDealers;
                        }
                    });
                    return newSuccessDealers;
                } else {
                    return successDealers;
                }
            } else {
                searchString = searchString.substring(0, searchString.length - 1);
                return self.zipSearch(searchString, failedDealers, successDealers);
            }

        }

        self.cityZipSearch = function (searchString) {

            var searchType;

            if (self.lastSearchType == 'cityZip') {
                self.filterDealers();
            }

            self.lastSearchType = "cityZip";

            if (searchString == '') {
                self.updateAll(self.lastFilteredDealers);
                return true;
            }

            if (/\d+/.test(searchString)) {
                searchType = "zip";
            }

            if (searchType == 'zip' && self.zipSearchType == 'searchSimilar') {

                // THIS IS TEMPORARY SOLUTION. SHOULD BE CHANGED BY USING SOME POSTCODES/GEOPOSITION DATABASE OR API

                var foundDealers = self.zipSearch(searchString, self.lastFilteredDealers);

                if (foundDealers.length == 0) {
                    self.updateAll(self.lastFilteredDealers);
                    self.zipSearchType = "googleCall";
                    return true;
                } else {
                    foundDealers = foundDealers.slice(0, self.pluginSettings.resultLimit);
                    self.fitBoundsType = FB_MARKERS;
                    self.updateAll(foundDealers);
                    self.zipSearchType = "googleCall";
                    return true;
                }

            } else {

                // Default country
                var defaultCountryName = self.pluginSettings['defaultCountry'];

                var geocoder = new google.maps.Geocoder();
                var geocoderOptions = {};
                geocoderOptions.componentRestrictions = {};

                var addressParts = [];
                var allowedGeocodeAnswersTypes = ['locality', 'sublocality'];
                if (searchType == 'zip') {
                    allowedGeocodeAnswersTypes = ['postal_code'];
                    geocoderOptions.componentRestrictions.country = defaultCountryName;
                    geocoderOptions.componentRestrictions.postalCode = searchString;
                }

                // Get country name
                if (self.selectedCountry != 0 && self.selectedCountry != 'row') {
                    var countryName = $("select[name='dealer-countries'] option[value='" + self.selectedCountry + "']").text();
                    if (countryName.length > 0) {
                        addressParts.push(countryName);
                        geocoderOptions.componentRestrictions.country = countryName;
                    }
                }

                // Get country zone name
                if (self.selectedCountryZone != 0) {
                    var countryZoneName = $("select[name='dealer-country-states'] option[value='" + self.selectedCountryZone + "']").text();
                    if (countryZoneName.length > 0) {
                        addressParts.push(countryZoneName);
                    }
                }

                addressParts.push(searchString);

                geocoderOptions.address = addressParts.join(", ");

                if (searchType == 'zip') {
                    geocoderOptions.address = "";
                }

                geocoder.geocode(geocoderOptions, function (results, status) {

                    if (status == google.maps.GeocoderStatus.OK && results.length > 0) {

                        for (var i = 0; i < results.length; i++) {
                            if (arrayIntersect(results[i].types, allowedGeocodeAnswersTypes)) {
                                if (typeof results[i].geometry.bounds != 'undefined') {
                                    bounds = results[i].geometry.bounds;
                                } else {
                                    bounds = results[i].geometry.viewport;
                                }
                                break;
                            } else {
                                bounds = 0;
                            }
                        }

                        if (typeof bounds === 'object') {

                            //var originalBounds = bounds;
                            //var pos1 = new google.maps.LatLng(
                            //    originalBounds.getNorthEast().lat() + 2,
                            //    originalBounds.getNorthEast().lng() + 2 * 2
                            //);
                            //var pos2 = new google.maps.LatLng(
                            //    originalBounds.getSouthWest().lat() - 2,
                            //    originalBounds.getSouthWest().lng() - 2 * 2
                            //);
                            //bounds.extend(pos1);
                            //bounds.extend(pos2);

                            //var rectangle = new google.maps.Rectangle({
                            //                                              strokeColor: '#FF0000',
                            //                                              strokeOpacity: 0.8,
                            //                                              strokeWeight: 2,
                            //                                              fillColor: '#FF0000',
                            //                                              fillOpacity: 0.35,
                            //                                              map: self.map,
                            //                                              bounds: bounds
                            //                                          });

                            var closestMarkers = self.findClosest(self.lastFilteredDealers, bounds.getCenter());

                            self.fitBoundsType = FB_MARKERS;
                            self.updateAll(closestMarkers);

                        } else {
                            if (searchType == 'zip') {
                                self.zipSearchType = "searchSimilar";
                                self.cityZipSearch(searchString);
                            } else {
                                self.updateAll([]);
                            }
                        }
                    } else {
                        if (searchType == 'zip') {
                            self.zipSearchType = "searchSimilar";
                            self.cityZipSearch(searchString);
                        } else {
                            self.updateAll([]);
                        }
                    }
                });
            }

        }

        self.dealersFitLocation = function (dealers) {

            if (!dealers.length) {
                return false;
            }

            if (self.fitBoundsType == FB_CITY) {

                var dealersCities = dealers.getColumn('city');

                if ($.unique(dealersCities).length > 1) {
                    self.fitBoundsType = FB_MARKERS;
                }

            }

            if (self.fitBoundsType == FB_MARKERS) {

                var bounds = new google.maps.LatLngBounds();

                $.each(dealers, function (index, marker) {
                    var pos = 0;
                    pos = new google.maps.LatLng(marker['lat'], marker['lng']);
                    if (pos !== 0) {
                        bounds.extend(pos);
                    }
                });

                if (dealers.length == 1) {
                    self.map.fitBounds(bounds);
                    var listener = google.maps.event.addListener(self.map, "idle", function () {
                        self.map.setZoom(12);
                        google.maps.event.removeListener(listener);
                    });
                } else {
                    self.map.fitBounds(bounds);
                    self.map.setCenter(bounds.getCenter());
                }

                return true;

            }

            var sampleMarker = dealers[0];

            // Fix for New York state
            if (self.fitBoundsType == FB_STATE && sampleMarker['country'] == 220 && sampleMarker['countryZoneIsoCode'].toLowerCase() == "ny") {
                var nysPoints = [];
                nysPoints.push({'lat': 45.207730, 'lng': -80.055420});
                nysPoints.push({'lat': 40.055527, 'lng': -80.110351});
                nysPoints.push({'lat': 39.929273, 'lng': -71.661865});
                nysPoints.push({'lat': 45.246418, 'lng': -71.530029});
                self.fitBoundsType = FB_MARKERS;
                self.dealersFitLocation(nysPoints, FB_MARKERS);
                return true;
            }

            var addressChoses = {};
            addressChoses[FB_COUNTRY] = sampleMarker['countryName'];
            addressChoses[FB_STATE] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneIsoCode'];
            addressChoses[FB_CITY] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneName'] + ', ' + sampleMarker['city'];
            addressChoses[FB_MARKERS] = "";

            if (!(self.fitBoundsType in addressChoses)) {
                return false;
            }

            var address = addressChoses[self.fitBoundsType];

            var pos = new google.maps.LatLng(sampleMarker['lat'], sampleMarker['lng']);

            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': address, 'location': pos}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    self.map.setCenter(results[0].geometry.location);
                    self.map.fitBounds(results[0].geometry.viewport);
                    if (self.fitBoundsType == FB_CITY || self.fitBoundsType == FB_MARKERS) {
                        self.map.setZoom(settings.mapZoomLevel * 1);
                    }
                }
            });

        }

        // Clean map
        self.clearMap = function () {
            $.each(self.dealers, function (index, dealer) {
                if (dealer.marker.getVisible()) {
                    dealer.marker.setVisible(false);
                }
            });

            if (self.pluginSettings.clusterMarkers == 1) {
                self.markerclusterer.removeMarkers(self.markers, true);
                self.markerclusterer.repaint();
            }

        }

        self.updateAll = function (dealers) {
            self.clearMap();
            self.updateMap(dealers);

            if (self.pluginSettings.layoutType == "alphabet") {
                self.updateCards(dealers);
            }

            if (self.pluginSettings.layoutType == "grid") {
                self.updateCardsIsotope(dealers)
            }
        }

        // Update map
        self.updateMap = function (filteredDealers) {
            $.each(filteredDealers, function (index, dealer) {
                if (!dealer.marker.getVisible()) {
                    dealer.marker.setVisible(true);
                    if (settings.clusterMarkers == 1) {
                        self.markerclusterer.addMarker(dealer.marker, true);
                    }
                }
            });

            if (self.pluginSettings.clusterMarkers == 1) {
                self.markerclusterer.repaint();
            }

            self.dealersFitLocation(filteredDealers);
        }

        self.updateCards = function (dealers) {

            var dealerUids = [];

            enabledDealersListItems = [];

            $.each(dealers, function (index, dealer) {
                enabledDealersListItems.push(
                    self.allDealersList.filter(".isotope-item[data-uid='" + dealer.uid + "']")
                );
                dealerUids.push(dealer.uid);
            });

            var letters = [];

            $(".pxa-dealers-list-container").fadeToggle("fast", "linear", function () {

                $(".pxa-dealers-list-container").html('');

                enabledDealersListItems.sort(function (a, b) {
                    return $(a).find(".dealer-name").text() > $(b).find(".dealer-name").text() ? 1 : -1;
                });

                $(enabledDealersListItems).each(function () {
                    if (letters.indexOf($(this).data("name")[0]) < 0) {
                        letters.push($(this).data("name")[0]);
                    }
                    $(".pxa-dealers-list-container").append(this);
                });

                $(letters).each(function (index, val) {
                    if ($(".letter-heading#" + val + "-letter").length <= 0) {
                        $(".isotope-item[data-name^='" + val + "']").first().before("<div id='" + val + "-letter' class='letter-heading'>" + val + "</div>");
                    }
                    $(".isotope-item[data-name^='" + val + "']").wrapAll("<div class='items-collection " + val + "-letter-collection' />");
                });

                $("#dealers-count").html(enabledDealersListItems.length);

                $(".dealers-header").fadeToggle("fast", "linear", function () {
                    if (enabledDealersListItems.length <= 0) {
                        $(".dealers-header").text(self.labels.notDealersFoundFilteringMessage);
                    } else {
                        $(".dealers-header").html(self.originalDealersHeader);
                        $(".dealers-header #dealers-count").html(enabledDealersListItems.length);
                    }
                    $(".dealers-header").fadeToggle("fast", "linear");
                });

                $(".pxa-dealers-list-container").fadeToggle("fast", "linear");
            });

        }


        self.updateCardsIsotope = function (dealers) {

            var dealerUids = [];

            $.each(dealers, function (index, dealer) {
                dealerUids.push(parseInt(dealer.uid));
            });

            $("#pxa-dealers-list-container").isotope({
                filter: function () {
                    var uid = $(this).data("uid");

                    if (dealerUids.indexOf(uid) !== -1) {
                        return true;
                    } else {
                        return false;
                    }
                },
                sortBy: 'name'
            });


            if (dealers.length == 0) {
                var dealersHeader = self.labels.notDealersFoundFilteringMessage;
            } else {
                var dealersHeader = dealers.length + " " + self.labels.dealers_found;
            }

            $("#dealers-header").addClass("hidden-dealers-header");
            $(".dealers-header").on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd',
                function () {
                    $("#dealers-header").html(dealersHeader);
                    $("#dealers-header").removeClass("hidden-dealers-header");
                });
        }

    }

    function checkLocation() {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(checkPositionSuccess, checkPositionError);
        } else {
            // TODO
            //console.log ("Geolocation is not supported by this browser.");
        }
    }

    function checkPositionSuccess(position) {
        findClosest(markers, position);
    }

    function checkPositionError() {
        // TODO
        switch (error.code) {
            case error.PERMISSION_DENIED:
                //console.log ("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                //console.log ("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                //console.log ("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                //console.log ("An unknown error occurred.");
                break;
        }
    }

    function showError(error) {
        //runAjax();
        switch (error.code) {
            case error.PERMISSION_DENIED:
                //console.log ("User denied the request for Geolocation.");
                break;
            case error.POSITION_UNAVAILABLE:
                //console.log ("Location information is unavailable.");
                break;
            case error.TIMEOUT:
                //console.log ("The request to get user location timed out.");
                break;
            case error.UNKNOWN_ERROR:
                //console.log ("An unknown error occurred.");
                break;
        }
    }

    function populateCountryZones(country) {

        $(".pxa-dealers .dealer-country-states").find('option').remove();

        $(".pxa-dealers .dealer-country-states").append($("<option></option>")
            .attr("value", 0)
            .text(labels['country_list.all_country_zones']));

        if (country != 0 && country != 'row') {
            var currentCountryZones = countryStatesCollection[country];

            if (currentCountryZones !== undefined) {

                $.each(currentCountryZones, function (key, value) {
                    $(".pxa-dealers .dealer-country-states")
                        .append($("<option></option>")
                            .attr("value", key)
                            .text(value));
                });

            }

        }

        $(".pxa-dealers .dealer-country-states").trigger("chosen:updated");
    }

    if (typeof pxa_dealers_enabled != 'undefined') {

        $(document).ready(function () {

            var pxa_dealers = new PxaDealers();

            // If using grid layout - init isotope
            if (pxa_dealers.pluginSettings.layoutType == 'grid') {

                $("#pxa-dealers-list-container").isotope({
                    itemSelector: '.isotope-item',
                    layoutMode: 'fitRows',
                    getSortData: {
                        name: '[data-name]'
                    }
                });
            }

            pxa_dealers.allDealersList = $(".pxa-dealers-list-container .dealer-item").clone();
            pxa_dealers.originalDealersHeader = $("#dealers-header-original").html();
            pxa_dealers.initializeMapPxaDealers();
            pxa_dealers.selectedCountry = $(".pxa-dealers .dealer-countries").val();

            // If results page was opened by using external search filed - apply the search
            if (initialSearchValue.length > 0) {
                $(".pxa-dealers .dealer-cityzip-search").val(initialSearchValue);
                $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
                initialSearchValue = "";
                setTimeout(function () {
                    $(".pxa-dealers .dealer-cityzip-search").trigger("search");
                }, 50);
            }

            // Show google street view
            $(document).on('click', '.street-switch-trigger', function (event) {
                event.preventDefault();
                if ($(this).attr("data-marker-id").length) {
                    pxa_dealers.switchToStreetView($(this).attr("data-marker-id"));
                }
            });

            // Change country zones if country has been changed
            if ($(".pxa-dealers .dealer-countries").length && $(".pxa-dealers .dealer-country-states").length) {
                populateCountryZones($(".pxa-dealers .dealer-countries").val());
            }

            // Categories change checkbox mode
            $(".pxa-dealers > .categories .category-item input[type='checkbox']").on("change", function () {
                var $this = $(this);
                $this.parent().toggleClass('selected');

                if (pxa_dealers.pluginSettings.map.enableCategoriesFilteringZoom == 1) {
                    pxa_dealers.fitBoundsType = FB_MARKERS;
                } else {
                    pxa_dealers.fitBoundsType = FB_NONE;
                }

                pxa_dealers.filterDealers();
            });

            // Categories change radio mode
            $(".pxa-dealers .categories .category-item-radio").on("click", function (e) {

                e.preventDefault();

                $(this).addClass("selected");
                $(".pxa-dealers .categories .category-item-radio").not($(this)).removeClass('selected');

                if (pxa_dealers.pluginSettings.map.enableCategoriesFilteringZoom == 1) {
                    pxa_dealers.fitBoundsType = FB_MARKERS;
                } else {
                    pxa_dealers.fitBoundsType = FB_NONE;
                }

                pxa_dealers.filterDealers();
            });

            // Countries change
            $(".pxa-dealers .dealer-countries").on("change", function () {
                var selectedCountry = $(this).val()

                var fbType = FB_MARKERS;

                if (selectedCountry != 0 && selectedCountry != 'row') {

                    fbType = FB_COUNTRY;

                }

                populateCountryZones(selectedCountry);

                $(".pxa-dealers .dealer-cityzip-search").val('');

                pxa_dealers.selectedCountry = selectedCountry;
                pxa_dealers.selectedCountryZone = $(".pxa-dealers .dealer-country-states").val();
                pxa_dealers.searchString = "";
                pxa_dealers.fitBoundsType = fbType;
                pxa_dealers.filterDealers();

            });

            // Country zone change
            $(".pxa-dealers .dealer-country-states").on("change", function () {

                var selectedCountryZone = $(this).val();

                $(".pxa-dealers .dealer-cityzip-search").val('');

                pxa_dealers.fitBoundsType = FB_STATE;

                if (selectedCountryZone == 0) {
                    pxa_dealers.fitBoundsType = FB_COUNTRY;
                }

                pxa_dealers.selectedCountryZone = selectedCountryZone;
                pxa_dealers.searchString = "";
                pxa_dealers.filterDealers();
            });

            // City/zip search entered
            $(".pxa-dealers .dealer-cityzip-search").on("keypress search", function (e) {

                if (e.type == "search" || (e.type = "keypress" && e.which == 13)) {

                    var searchValue = $(this).val();
                    pxa_dealers.cityZipSearch(searchValue);

                }

            });

            // Find closest
            $(".pxa-dealers-find-closest .find-closest-btn").click(function () {

                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {

                        var closest = findClosest(markers, position);
                        $(".pxa-dealers .dealer-cityzip-search").val("");
                        $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
                        $(".pxa-dealers .dealer-country-states").val($(".pxa-dealers .dealer-country-states option:first").val());

                        self.filterDealers(allDealersListItems,
                            $(".pxa-dealers .dealer-countries").val(),
                            $(".pxa-dealers .dealer-country-states").val(),
                            $(".pxa-dealers .dealer-cityzip-search").val(),
                            FB_MARKERS,
                            false,
                            closest
                        );
                    }, checkPositionError);
                } else {
                    // TODO
                    //console.log ("Geolocation is not supported by this browser.");
                }

            });

        });

    }

    $(document).ready(function () {

        $('form[name="searchDealers"]').on('submit', function (event) {
            event.preventDefault();

            var url = $(this).attr('action').replace(/\/?$/, '/') + $(this).find('input[name="tx_pxadealers_pxadealerssearchresults[searchValue]"]').val();

            url = (url.charAt(0) != '/' ? ('/' + url) : url);

            window.document.location = url;
        });

    });

}
