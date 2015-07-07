if( $(".tx-pxa-dealers").length > 0 ) {

var countriesList;

var pxa_dealers = new PxaDealers();

var FB_COUNTRY = 0;
var FB_STATE = 1;
var FB_CITY = 2;
var FB_MARKERS = 3;
var FB_NONE = 4;

// Prototypes
if (typeof String.prototype.startsWith != 'function') {
    // see below for better implementation!
    String.prototype.startsWith = function (str){
        return this.indexOf(str) == 0;
    };
}

if (typeof Array.prototype.getColumn != 'function') {
    Array.prototype.getColumn = function(name) {
        return this.map(function(el) {
            // gets corresponding 'column'
            if (el.hasOwnProperty(name)) return el[name];
            // removes undefined values
        }).filter(function(el) { return typeof el != 'undefined'; });
    };
}

if (typeof Array.prototype.diff != 'function') {
    Array.prototype.diff = function(a) {
        return this.filter(function(i) {return a.indexOf(i) < 0;});
    };
}

function arrayIntersect (a, b) {
    for (var i = a.length - 1; i >= 0; i--) {
        if( $.inArray(a[i], b) !== -1 ) {
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
    self.countriesList = $.map( self.pluginSettings['mainCountries'].split(","), $.trim );
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

    self.filteredDealersUids = [];
    self.filteredDealers = [];

    /**
     * initializeMapPxaDealers
     */
    self.initializeMapPxaDealers = function() {

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
        } catch(err) {
            stylesIsJSON = false;
        }

        if(stylesIsJSON) {
            var styledMap = new google.maps.StyledMapType(stylesObj, {name: self.pluginSettings.map.name});

            self.map.mapTypes.set('map_style', styledMap);
            self.map.setMapTypeId('map_style');
        }

        // Panorama
        self.panorama = self.map.getStreetView();

        // Enable marker clusterer
        if(self.pluginSettings.clusterMarkers == 1) {
            self.markerclusterer = new MarkerClusterer(self.map, [], {
                maxZoom: 9
            });
        }

        var dealersProcessed = [];


        // Generate markers
        $.each(self.dealers, function( index, dealer ) {
            if( (dealer['lat'] != '') && (dealer['lng'] != '') ) {

                self.getAddress( dealer, function(marker) {
                    dealer['marker'] = marker;
                    self.markers[dealer['uid']] = dealer['marker'];
                    if(self.pluginSettings.clusterMarkers == 1) {
                        self.markerclusterer.addMarker(marker, true);
                    }
                });

                dealersProcessed.push(dealer);
            }
        });

        self.dealers = dealersProcessed;

        /// Fit bounds
        if(self.dealers.length == 1 ) {
            self.map.fitBounds(self.bounds);
            var listener = google.maps.event.addListener(self.map, "idle", function() {
                self.map.setZoom(12);
                google.maps.event.removeListener(listener);
            });
        } else {
            self.map.fitBounds(self.bounds);
            self.map.setCenter(self.bounds.getCenter());
        }

        var filterOn = FB_MARKERS;
        if( $(".pxa-dealers .dealer-countries").val() != 0 && $(".pxa-dealers .dealer-countries").val() !== undefined ) {
            filterOn = FB_COUNTRY;
        }

        self.selectedCountry = $(".pxa-dealers .dealer-countries").val();
        self.selectedCountryZone = $(".pxa-dealers .dealer-countries").val();
        self.searchString = $(".pxa-dealers .dealer-cityzip-search").val();
        self.fitBoundsType = filterOn;
        self.filterDealers();

    }

    // Get map marker
    self.getAddress = function ( dealer, callback ) {

        var markerIcon = self.pluginSettings.map.markerImage;
        var pos = new google.maps.LatLng( dealer['lat'], dealer['lng'] );

        var address = dealer['address'] ? '<br/>' + dealer['address'] : '';
        var zipcode = dealer['zipcode'] ? '<br/>' + dealer['zipcode'] : '';
        var city = dealer['city'] ? '<br/>' + dealer['city'] : '';

        var telephone= dealer['telephone'] ? "<br/><a href=\"tel:" + dealer['telephone_clear'] +
                                             "\">Tel: " + dealer['telephone'] + "</a>": '';
        var email = dealer['email'] ? "<br/><a href=\"mailto:" + dealer['email']+"\">" +
                                      dealer['email']+ "</a>" : '';

        if(dealer['website']) {
            // Check if website starts from http
            var websiteUrl = dealer['website'];
            if(!(/^http:\/\//.test(dealer['website']))) {
                websiteUrl = 'http://'+websiteUrl;
            }

            var website = "<br/><a href=" + websiteUrl + " target=\"_blank\" class=\"website-link\">" + websiteUrl + '</a>';
        } else {
            var website = '';
        }

        var imageStreetPreviewContent = '<td><div class="image-street-preview">';
        imageStreetPreviewContent += '<a href="#streetview" data-marker-id="' + dealer['uid']
                                     + '" class="street-switch-trigger website-link">';
        imageStreetPreviewContent += '<img src="http://maps.googleapis.com/maps/api/streetview?size=90x70&location=' +
                                     dealer['lat'] + ',' + dealer['lng']+'&sensor=false" /><br>';
        imageStreetPreviewContent += '<span>Streetview</span></a>';
        imageStreetPreviewContent += '</div></td>';

        var infowindowCont = "<div class=\"google-map-marker\">";
        infowindowCont += "<table><tr><td>";
        infowindowCont += "<strong>" + dealer['name']+"</strong>";
        infowindowCont += address;
        infowindowCont += zipcode;
        infowindowCont += city;
        infowindowCont += telephone;
        infowindowCont += email;
        infowindowCont += website;
        infowindowCont += imageStreetPreviewContent;
        infowindowCont += "</div></tr></table>";

        var marker = new google.maps.Marker({
                                                map: self.map,
                                                position: pos,
                                                animation: google.maps.Animation.DROP,
                                                icon: markerIcon
                                            });
        google.maps.event.addListener(marker, 'click', function() {
            self.infowindow.setContent(infowindowCont);
            self.infowindow.open(self.map, marker);
        });

        self.bounds.extend(pos);

        callback(marker);
    }

    self.deg2rad = function(deg) {
        return deg * (Math.PI/180)
    }

    self.getDistanceFromLatLonInKm = function(lat1,lon1,lat2,lon2) {
        var R = 6371; // Radius of the earth in km
        var dLat = self.deg2rad(lat2-lat1);  // deg2rad below
        var dLon = self.deg2rad(lon2-lon1);
        var a =
                        Math.sin(dLat/2) * Math.sin(dLat/2) +
                        Math.cos(self.deg2rad(lat1)) * Math.cos(self.deg2rad(lat2)) *
                        Math.sin(dLon/2) * Math.sin(dLon/2)
                ;
        var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        var d = R * c; // Distance in km
        return d;
    }

    self.findClosest = function(dealers, position) {

        if( typeof(position)==='undefined') {
            positionLat = '59.339799';
            positionLong = "18.047922";
        } else {
            positionLat = position.lat();
            positionLong = position.lng();
        }

        $.each(dealers, function(index, dealer){
            dealer['distance'] = self.getDistanceFromLatLonInKm(positionLat, positionLong, dealer['lat'], dealer['lng']);
        });

        var closest;
        closest = dealers.sort(function (a, b) {
            return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
        });

        closest = closest.slice(0, self.pluginSettings.resultLimit);

        return closest;
    }

    self.switchToStreetView = function(uid) {
        var position = self.markers[uid].getPosition();
        self.panorama.setPosition(position);
        self.panorama.setPov({
                            heading: 265,
                            zoom:1,
                            pitch:0}
        );
        self.panorama.setVisible(true);
        return false;
    }

    self.getSelectedCategories = function() {

        var selectedCategories = [];

        $('.pxa-dealers > .categories > .selected').each( function() {
            selectedCategories.push( parseInt($(this).attr("data-category-uid")) );
        });

        return selectedCategories;
    }

    self.belongsToCountry = function(marker) {
        return (marker['country'] === self.selectedCountry
                || (self.selectedCountry === "row" && $.inArray(marker['country'], self.countriesList) === -1 )
                || self.selectedCountry == 0);
    }

    self.belongsToCountryZone = function(marker) {
        return (marker['countryZone'] == self.selectedCountryZone || self.selectedCountryZone == 0);
    }

    self.belongsToCategories = function(marker, selectedCategories) {

        var categories = JSON.parse( marker['categories'] );
        if( categories.length == 0 ) {
            return true;
        } else {
            return arrayIntersect(categories, selectedCategories);
        }
    }

    // Filter markers
    self.filterDealers = function() {
        var filtered = self.getFilteredDealers();
        self.updateAll(filtered);
        return true;
    }

    // Get filtered markers
    self.getFilteredDealers = function() {

        var filtered = [];
        var selectedCategories = self.getSelectedCategories();

        $.each( self.dealers, function( index, dealer ) {

            var isOk = [];

            if( $(".pxa-dealers .dealer-countries").length > 0 ) {
                isOk.push( self.belongsToCountry(dealer) );
            }

            if( $(".pxa-dealers .dealer-country-states").length > 0 ) {
                isOk.push( self.belongsToCountryZone(dealer) );
            }

            if( $(".pxa-dealers > .categories").length > 0 ) {
                isOk.push( self.belongsToCategories(dealer, selectedCategories) );
            }

            if( $.inArray(false, isOk) === -1 ) {
                filtered.push(dealer);
            }

        });

        self.lastFilteredDealers = filtered;
        return filtered;

    }

    self.zipSearch = function(searchString, dealers, successDealers) {

        // THIS IS TEMPORARY SOLUTION. SHOULD BE CHANGED BY USING SOME POSTCODES/GEOPOSITION DATABASE OR API

        if( typeof(successDealers)==='undefined' ) {
            successDealers = [];
        }

        if(searchString == '') {
            return false;
        }

        var expression = /[\. ,:-]+/g;
        var processedSearchString = searchString.replace(expression,'').toLowerCase();

        var failedDealers = [];

        $.each(dealers, function(index, dealer) {
            var processedZipcode = dealer['zipcode'].replace(expression,'').toLowerCase();

            if( processedZipcode.startsWith(processedSearchString) ) {
                successDealers.push(dealer);
            } else if( /^[a-zA-Z]+$/.test(processedZipcode.substr(0,2)) && processedZipcode.substr(2).startsWith(processedSearchString)) {
                successDealers.push(dealer);
            } else {
                failedDealers.push(dealer);
            }
        });

        if( successDealers.length >= self.pluginSettings.resultLimit || searchString.length <= 1) {
            if( successDealers.length < self.pluginSettings.resultLimit && successDealers.length > 0 ) {
                var newSuccessDealers = [];
                $.each(successDealers, function(index, dealer) {
                    var position = new google.maps.LatLng( dealer['lat'], dealer['lng'] );
                    var closestDealers = self.findClosest( self.lastFilteredDealers, position);
                    newSuccessDealers = $.merge( successDealers, closestDealers.diff(successDealers) );
                    if( newSuccessDealers >= self.pluginSettings.resultLimit ) {
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

    self.cityZipSearch = function(searchString) {

        if(searchString == '') {
            self.updateAll(self.lastFilteredDealers);
            return true;
        }

        if(/\d+/.test(searchString)) {

            // THIS IS TEMPORARY SOLUTION. SHOULD BE CHANGED BY USING SOME POSTCODES/GEOPOSITION DATABASE OR API

            var foundDealers = self.zipSearch(searchString, self.lastFilteredDealers);

            if(foundDealers.length == 0) {
                self.updateAll(self.lastFilteredDealers);
                return true;
            } else {
                foundDealers = foundDealers.slice(0, self.pluginSettings.resultLimit);
                self.fitBoundsType = FB_MARKERS;
                self.updateAll(foundDealers);
                return true;
            }

        } else {

            var geocoder = new google.maps.Geocoder();
            var geocoderOptions = {};
            geocoderOptions.componentRestrictions = {};

            var addressParts = [];
            var allowedGeocodeAnswersTypes = ['locality', 'sublocality'];

            // Get country name
            if ( self.selectedCountry != 0 && self.selectedCountry != 'row' ) {
                var countryName = $("select[name='dealer-countries'] option[value='"+self.selectedCountry+"']").text();
                if(countryName.length > 0) {
                    addressParts.push(countryName);
                    geocoderOptions.componentRestrictions.country = countryName;
                }
            }

            // Get country zone name
            if ( self.selectedCountryZone != 0 ) {
                var countryZoneName = $("select[name='dealer-country-states'] option[value='"+self.selectedCountryZone+"']").text();
                if(countryZoneName.length > 0) {
                    addressParts.push(countryZoneName);
                }
            }

            addressParts.push(searchString);
            geocoderOptions.address = addressParts.join(", ");

            geocoder.geocode(geocoderOptions, function (results, status) {

                if (status == google.maps.GeocoderStatus.OK && results.length > 0) {

                    for (var i = 0; i < results.length; i++) {
                        if( arrayIntersect(results[i].types, allowedGeocodeAnswersTypes) ) {
                            if(typeof results[i].geometry.bounds != 'undefined') {
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

                        var closestMarkers = self.findClosest(self.lastFilteredDealers, bounds.getCenter());

                        self.fitBoundsType = FB_MARKERS;
                        self.updateAll(closestMarkers);

                    } else {
                        self.updateAll([]);
                    }
                } else {
                    self.updateAll([]);
                }
            });
        }

    }

    self.dealersFitLocation = function(dealers) {

        if( !dealers.length ) {
            return false;
        }

        if( self.fitBoundsType == FB_CITY ) {

            var dealersCities = dealers.getColumn('city');

            if( $.unique(dealersCities).length > 1 ) {
                self.fitBoundsType = FB_MARKERS;
            }

        }

        if( self.fitBoundsType == FB_MARKERS ) {

            var bounds = new google.maps.LatLngBounds();

            $.each( dealers, function( index, marker ) {
                var pos = 0;
                pos = new google.maps.LatLng(marker['lat'],marker['lng']);
                if(pos !== 0) {
                    bounds.extend(pos);
                }
            });

            if(dealers.length == 1 ) {
                self.map.fitBounds(bounds);
                var listener = google.maps.event.addListener(self.map, "idle", function() {
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
        if(self.fitBoundsType == FB_STATE && sampleMarker['country'] == 220 && sampleMarker['countryZoneIsoCode'].toLowerCase() == "ny") {
            var nysPoints = [];
            nysPoints.push( {'lat': 45.207730, 'lng': -80.055420} );
            nysPoints.push( {'lat': 40.055527, 'lng': -80.110351} );
            nysPoints.push( {'lat': 39.929273, 'lng': -71.661865} );
            nysPoints.push( {'lat': 45.246418, 'lng': -71.530029} );
            self.fitBoundsType = FB_MARKERS;
            self.dealersFitLocation(nysPoints, FB_MARKERS);
            return true;
        }

        var addressChoses = {};
        addressChoses[FB_COUNTRY] = sampleMarker['countryName'];
        addressChoses[FB_STATE] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneIsoCode'];
        addressChoses[FB_CITY] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneName'] + ', ' + sampleMarker['city'];
        addressChoses[FB_MARKERS] = "";

        if( !(self.fitBoundsType in addressChoses) ) {
            return false;
        }

        var address = addressChoses[self.fitBoundsType];

        var pos = new google.maps.LatLng(sampleMarker['lat'],sampleMarker['lng']);

        var geocoder = new google.maps.Geocoder();
        geocoder.geocode( { 'address': address, 'location': pos}, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                self.map.setCenter(results[0].geometry.location);
                self.map.fitBounds(results[0].geometry.viewport);
                if(self.fitBoundsType == FB_CITY || self.fitBoundsType == FB_MARKERS) {
                    self.map.setZoom(settings.mapZoomLevel * 1);
                }
            }
        });

    }

    // Clean map
    self.clearMap = function() {
        $.each(self.dealers, function( index, dealer ) {
            if( dealer.marker.getVisible() ) {
                dealer.marker.setVisible(false);
            }
        });

        if(self.pluginSettings.clusterMarkers == 1) {
            self.markerclusterer.removeMarkers(self.markers, true);
            self.markerclusterer.repaint();
        }

    }

    self.updateAll = function(dealers) {
        self.clearMap();
        self.updateMap(dealers);
        self.updateCardsIsotope(dealers);
    }

    // Update map
    self.updateMap = function(filteredDealers) {
        $.each( filteredDealers, function( index, dealer ) {
            if( !dealer.marker.getVisible() ) {
                dealer.marker.setVisible(true);
                if(settings.clusterMarkers == 1) {
                    self.markerclusterer.addMarker(dealer.marker, true);
                }
            }
        });

        if(self.pluginSettings.clusterMarkers == 1) {
            self.markerclusterer.repaint();
        }

        self.dealersFitLocation(filteredDealers);
    }

    self.updateCards = function(dealers) {

        var dealerUids = [];

        $.each( dealers, function(index, dealer) {
            dealerUids.push(dealer.uid);
        });

        enabledDealersListItems = [];

        $(".pxa-dealers-list-container").fadeToggle( "fast", "linear", function() {

            $(".pxa-dealers-list-container").html('');

            self.allDealersList.each( function() {
                $this = $(this);
                if( $.inArray($this.attr("data-uid"), dealerUids) !== -1 ) {
                    enabledDealersListItems.push(this);
                    $(".pxa-dealers-list-container").append(this);
                }
            });

            enabledDealersListItems.sort(function(a, b){
                return $(a).find(".dealer-name").text() > $(b).find(".dealer-name").text() ? 1 : -1;
            });

            var counter = 0;
            var newRow;

            $(enabledDealersListItems).each(function() {

                if(counter === 0) {
                    newRow = $("<div class='pxa-dealers-list " + self.pluginSettings.additionalListWrapperClass + "'></div>");
                    $(".pxa-dealers-list-container").append(newRow);
                }

                $(newRow).append(this);

                counter++;

                if(counter == self.pluginSettings.newRow) {
                    counter = 0;
                }

            });

            $("#dealers-count").html(enabledDealersListItems.length);

            $(".dealers-header").fadeToggle( "fast", "linear", function() {
                if(enabledDealersListItems.length <= 0) {
                    $(".dealers-header").text(noResultsFoundLabel);
                } else {
                    $(".dealers-header").html(self.originalDealersHeader);
                    $(".dealers-header #dealers-count").html(enabledDealersListItems.length);
                }
                $(".dealers-header").fadeToggle( "fast", "linear");
            });

            $(".pxa-dealers-list-container").fadeToggle( "fast", "linear");
        });
    }


    self.updateCardsIsotope = function(dealers) {

        var dealerUids = [];

        $.each(dealers, function (index, dealer) {
            dealerUids.push( parseInt(dealer.uid) );
        });

        $("#pxa-dealers-list-container").isotope({
                                                     filter: function() {
                                                         var uid = $(this).data("uid");

                                                         if( dealerUids.indexOf(uid) !== -1 ) {
                                                             return true;
                                                         } else {
                                                             return false;
                                                         }
                                                     },
                                                     sortBy : 'name'
                                                 });



        if(dealers.length == 0) {
            var dealersHeader = self.labels.notDealersFoundFilteringMessage;
        } else {
            var dealersHeader = dealers.length + " " + self.labels.dealers_found;
        }

        $("#dealers-header").addClass("hidden-dealers-header");
        $(".dealers-header").on('transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd',
                           function() {
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
    switch(error.code) {
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
    switch(error.code) {
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

    if(country != 0 && country != 'row') {
        var currentCountryZones = countryStatesCollection[country];

        if(currentCountryZones !== undefined) {

            $.each(currentCountryZones, function(key, value) {
                $(".pxa-dealers .dealer-country-states")
                        .append($("<option></option>")
                                        .attr("value",key)
                                        .text(value));
            });

        }

    }
}

if (typeof pxa_dealers_enabled != 'undefined') {

    $( document ).ready(function() {

        // ISOTOPE TEST

        $("#pxa-dealers-list-container").isotope({
                                                     itemSelector: '.isotope-item',
                                                     layoutMode: 'fitRows',
                                                     getSortData: {
                                                         name: '[data-name]'
                                                     }
                                                 });

        //

        pxa_dealers.allDealersList = $(".pxa-dealers-list-container .dealer-item").clone();
        pxa_dealers.originalDealersHeader = $("#dealers-header-original").html();
        pxa_dealers.initializeMapPxaDealers();
        pxa_dealers.selectedCountry = $(".pxa-dealers .dealer-countries").val();
        //console.log(pxa_dealers);

        // If results page was opened by using external search filed - apply the search
        if(initialSearchValue.length > 0) {
            $(".pxa-dealers .dealer-cityzip-search").val(initialSearchValue);
            $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
        }

        // Show google street view
        $(document).on('click', '.street-switch-trigger', function(event) {
            event.preventDefault();
            if( $(this).attr("data-marker-id").length ) {
                pxa_dealers.switchToStreetView( $(this).attr("data-marker-id") );
            }
        });

        // Change country zones if country has been changed
        if( $(".pxa-dealers .dealer-countries").length && $(".pxa-dealers .dealer-country-states").length ) {
            populateCountryZones( $(".pxa-dealers .dealer-countries").val() );
        }

        $('form[name="searchDealers"]').on('submit', function(event){
            event.preventDefault();
            var url = $(this).attr('action').replace(/\/?$/, '/') + $(this).find('input[name="tx_pxadealers_pxadealerssearchresults[searchValue]"]').val();

            url = (url.charAt(0) != '/' ? ('/'+url) : url);

            window.document.location = url;
        });

        // Categories change
        $(".pxa-dealers > .categories .category-item input[type='checkbox']").on("change", function() {
            var $this = $(this);
            $this.parent().toggleClass('selected');

            pxa_dealers.fitBoundsType = FB_MARKERS;
            pxa_dealers.filterDealers();
        });

        // Countries change
        $(".pxa-dealers .dealer-countries").on("change", function() {
            var selectedCountry = $(this).val()

            var fbType = FB_MARKERS;

            if(selectedCountry != 0 && selectedCountry != 'row') {

                fbType = FB_COUNTRY;

            }

            populateCountryZones( selectedCountry );

            $(".pxa-dealers .dealer-cityzip-search").val('');

            pxa_dealers.selectedCountry = selectedCountry;
            pxa_dealers.selectedCountryZone = $(".pxa-dealers .dealer-country-states").val();
            pxa_dealers.searchString = "";
            pxa_dealers.fitBoundsType = fbType;
            pxa_dealers.filterDealers();

        });

        // Country zone change
        $(".pxa-dealers .dealer-country-states").on("change", function() {

            var selectedCountryZone = $(this).val();

            $(".pxa-dealers .dealer-cityzip-search").val('');

            pxa_dealers.fitBoundsType = FB_STATE;

            if(selectedCountryZone == 0) {
                pxa_dealers.fitBoundsType = FB_COUNTRY;
            }

            pxa_dealers.selectedCountryZone = selectedCountryZone;
            pxa_dealers.searchString = "";
            pxa_dealers.filterDealers();
        });

        // City/zip search entered
        $(".pxa-dealers .dealer-cityzip-search").keypress(function (e) {
            if(e.which == 13) {

                var searchValue = $(this).val();
                pxa_dealers.cityZipSearch(searchValue);

            }
        });

        // Find closest
        $(".pxa-dealers-find-closest .find-closest-btn").click(function () {

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position){

                    var closest = findClosest(markers, position);
                    $(".pxa-dealers .dealer-cityzip-search").val("");
                    $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
                    $(".pxa-dealers .dealer-country-states").val($(".pxa-dealers .dealer-country-states option:first").val());

                    self.filterDealers( allDealersListItems,
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

}