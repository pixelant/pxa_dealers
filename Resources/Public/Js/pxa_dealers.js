var map;
var markerclusterer;
var originalDealersHeader;
var allDealersListItems;
var countriesList;

var userPosition = false;

var FB_COUNTRY = 0;
var FB_STATE = 1;
var FB_CITY = 2;
var FB_MARKERS = 3;
var FB_NONE = 4;

// google.maps.event.addDomListener(window, 'load', function () { 
//   initializeMapPxaDealers(true);
// });

//initializeMapPxaDealers(true);

// function PxaDealers() {

//   var self = this;

//   self.pluginSettings = settings;
//   self.markers = markers;
//   self.labels = {};
//   self.countriesList = {};
// }

// var pxa_dealers = new PxaDealers();

/* Prototypes */

function initEnv() {

  originalDealersHeader = $("#dealers-header-original").html();
  countriesList = $.map(settings['mainCountries'].split(","), $.trim);

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

}

function runAjax(position) {
  $('#ajax-load-dealer').fadeIn();

  if( typeof(position)==='undefined') {
    positionLat = '51.165691';
    positionLong = "10.451526";
  } else {
    positionLat = position.coords.latitude;
    positionLong = position.coords.longitude;
  }

  url = '/?type=7505726&tx_pxadealers_pxadealerssearchresults%5Blatitude%5D='+positionLat+'&tx_pxadealers_pxadealerssearchresults%5Blongitude%5D='+positionLong;
  $.ajax({
    dataType: "json",
    url: url
  })
  .done(function(data){
    if(data.length > 0) {
      $(".pxa-dealers .dealer-cityzip-search").val("");
      $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
      $(".pxa-dealers .dealer-country-states").val($(".pxa-dealers .dealer-country-states option:first").val());
      //$('#ajax-load-dealer .pre-loader').remove();
      //$('#ajax-load-dealer .dealers-header.after-load').show();
      //$('#ajax-load-dealer').append(data.html);
      filterMarkers( allDealersListItems,
          $(".pxa-dealers .dealer-countries").val(),
          $(".pxa-dealers .dealer-country-states").val(),
          $(".pxa-dealers .dealer-cityzip-search").val(),
          FB_MARKERS,
          false,
          data
      );
      //initializeMapPxaDealers(false);
      //allDealersListItems = $(".pxa-dealers-list-container .dealer-item");
      //originalDealersHeader = $("#dealers-header-original").html();
    } else {
      //$('#ajax-load-dealer').fadeOut();
    }
  })
  .fail(function( jqXHR, textStatus ) {
    //console.log( "Request failed: " + textStatus );
  });
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

function showDefaultMap() {
  var mapOptions = {
            center: new google.maps.LatLng(51.165691,10.451526), 
            zoom: 4,
            mapTypeId: google.maps.MapTypeId.ROADMAP
      };
  var map = new google.maps.Map(document.getElementById("pxa-dealers-map"),mapOptions);
  checkLocation();
}

function arrayIntersect (a, b) {
  for (var i = a.length - 1; i >= 0; i--) {
    if( $.inArray(a[i], b) !== -1 ) {
      return true;
    }
  }

  return false;
}

function deg2rad(deg) {
  return deg * (Math.PI/180)
}

function getDistanceFromLatLonInKm(lat1,lon1,lat2,lon2) {
  var R = 6371; // Radius of the earth in km
  var dLat = deg2rad(lat2-lat1);  // deg2rad below
  var dLon = deg2rad(lon2-lon1);
  var a =
          Math.sin(dLat/2) * Math.sin(dLat/2) +
          Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
          Math.sin(dLon/2) * Math.sin(dLon/2)
      ;
  var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
  var d = R * c; // Distance in km
  return d;
}

function findClosest(markers, position) {

  if( typeof(position)==='undefined') {
    // Malmo
    //positionLat = '51.165691';
    //positionLong = "10.451526";
    // Lviv
    //positionLat = '49.843446';
    //positionLong = "24.026070";
    //Other
    positionLat = '59.339799';
    positionLong = "18.047922";
  } else {
    positionLat = position.coords.latitude;
    positionLong = position.coords.longitude;
  }

  $.each(markers, function(index, marker){
    marker['distance'] = getDistanceFromLatLonInKm(positionLat, positionLong, marker['lat'], marker['lng']);
  });

  var closest;
  closest = markers.sort(function (a, b) {
    return ((a.distance < b.distance) ? -1 : ((a.distance > b.distance) ? 1 : 0));
  });

  closest = closest.slice(0, settings.resultLimit);

  var closestUids = [];
  $.each(closest, function(index, closestItem){
    closestUids.push(closestItem['uid']);
  });

  return closestUids;
}

function initializeMapPxaDealers(doMarkersFilter) {

  initEnv();

  if( typeof(doMarkersFilter)==='undefined') doMarkersFilter = true;

  var bounds = new google.maps.LatLngBounds();       
  var infowindow = new google.maps.InfoWindow();       
                 
  var mapOptions = {
          mapTypeControlOptions: {
            mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
          }            
  };

  map = new google.maps.Map(document.getElementById("pxa-dealers-map"),mapOptions);

  // Check if styles was parsed correctly
  var stylesIsJSON = true;
  try {
    var stylesObj = $.parseJSON(settings.map.stylesJSON);
  } catch(err) {
    stylesIsJSON = false;
    //console.log("settings.map.stylesJSON has to be JSON formatted string");
  }

  if(stylesIsJSON) {

    var styledMap = new google.maps.StyledMapType(stylesObj, {name: settings.map.name});

    map.mapTypes.set('map_style', styledMap);
    map.setMapTypeId('map_style');
  }

  panorama = map.getStreetView();

  // Enable marker clasterer
  if(settings.clusterMarkers == 1) {
    markerclusterer = new MarkerClusterer(map, [], {
        maxZoom: 9
    });

  }

  var markersProcessed = [];

  for (i = 0;  i < markers.length; i++) {

    if((markers[i]['lat'] != '') && (markers[i]['lng'] != '')) {

      var pos = new google.maps.LatLng(markers[i]['lat'],markers[i]['lng']);

      getAddress(pos,map,infowindow,function(markerMap){
        markersArray[markers[i]['uid']] = markerMap;

        if(settings.clusterMarkers == 1) {
          markerclusterer.addMarker(markerMap, true);
        }

        bounds.extend(pos);
      });

      markersProcessed.push(markers[i]);

    }

  };

  markers = markersProcessed;

  if(markers.length == 1 ) {
    map.fitBounds(bounds);
      var listener = google.maps.event.addListener(map, "idle", function() { 
          map.setZoom(12); 
          google.maps.event.removeListener(listener); 
      });                            
  } else {
    map.fitBounds(bounds);
      map.setCenter(bounds.getCenter()); 
  }

  var filterOn = FB_MARKERS;

  if( $(".pxa-dealers .dealer-countries").val() != 0 ) {
    filterOn = FB_COUNTRY;
  }

  if(doMarkersFilter === true) {
    filterMarkers( allDealersListItems, 
        $(".pxa-dealers .dealer-countries").val(), 
        $(".pxa-dealers .dealer-country-states").val(),
        $(".pxa-dealers .dealer-cityzip-search").val(),
        filterOn);
  }

}

function switchToStreetView(i) {
  var position = markersArray[markers[i]['uid']].getPosition();
  panorama.setPosition(position);
  panorama.setPov({
    heading: 265,
    zoom:1,
    pitch:0}
  );
  panorama.setVisible(true);
  return false;
}

function getAddress(pos,map,infowindow,callback) {
  var markerIcon = settings.map.markerImage;

  var address = markers[i]['address'] ? '<br/>' + markers[i]['address'] : '';
  var zipcode = markers[i]['zipcode'] ? '<br/>' + markers[i]['zipcode'] : '';
  var city = markers[i]['city'] ? '<br/>' + markers[i]['city'] : '';
                   
  var telephone= markers[i]['telephone'] ? "<br/><a href=\"tel:" + markers[i]['telephone_clear'] + "\">Tel: " + markers[i]['telephone'] + "</a>": '';
  var email = markers[i]['email'] ? "<br/><a href=\"mailto:"+markers[i]['email']+"\">" +markers[i]['email']+ "</a>" : '';
  
  if(markers[i]['website']) {
    // Check if website starts from http
    var websiteUrl = markers[i]['website'];
    if(!(/^http:\/\//.test(markers[i]['website']))) {
      websiteUrl = 'http://'+websiteUrl;
    }

    var website = "<br/><a href="+websiteUrl+" target=\"_blank\" class=\"website-link\">"+websiteUrl+ '</a>';
  } else {
    var website = '';
  }

  var imageStreetPreviewContent = '<td><div class="image-street-preview">';
      imageStreetPreviewContent += '<a href="#streetview" data-marker-id="'+i+'" class="street-switch-trigger website-link">';
      imageStreetPreviewContent += '<img src="http://maps.googleapis.com/maps/api/streetview?size=90x70&location='+markers[i]['lat']+','+markers[i]['lng']+'&sensor=false" /><br>';
      imageStreetPreviewContent += '<span>Streetview</span></a>';
      imageStreetPreviewContent += '</div></td>';
      
  var infowindowCont = "<div class=\"google-map-marker\">";
      infowindowCont += "<table><tr><td>";
      infowindowCont += "<strong>"+markers[i]['name']+"</strong>";
      infowindowCont += address;
      infowindowCont += zipcode;
      infowindowCont += city;
      infowindowCont += telephone;
      infowindowCont += email;
      infowindowCont += website;
      infowindowCont += imageStreetPreviewContent;
      infowindowCont += "</div></tr></table>";

  var markerMap = new google.maps.Marker({
          map: map,
          position: pos,
          animation: google.maps.Animation.DROP,
          icon: markerIcon
  });
  google.maps.event.addListener(markerMap,'click',function(){
          infowindow.setContent(infowindowCont);
          infowindow.open(map, markerMap);
  });

  callback(markerMap);
}

function getSelectedCategories() {

  var selectedCategories = [];

  $('.pxa-dealers > .categories > .selected').each( function() {
    selectedCategories.push( parseInt($(this).attr("data-category-uid")) );
  });

  return selectedCategories;
}

function dealersFitBounds(filteredDealersMarkers) {

  if(filteredDealersMarkers.length > 0) {

    var bounds = new google.maps.LatLngBounds();

    $.each( filteredDealersMarkers, function( index, marker ) {  
      var pos = 0;
      pos = new google.maps.LatLng(marker['lat'],marker['lng']);
      if(pos !== 0) {
        bounds.extend(pos);
      }
    });

    if(filteredDealersMarkers.length == 1 ) {
      map.fitBounds(bounds);
      var listener = google.maps.event.addListener(map, "idle", function() { 
          map.setZoom(12); 
          google.maps.event.removeListener(listener); 
      });                            
    } else {
      map.fitBounds(bounds);
      map.setCenter(bounds.getCenter());
    }

    //map.panTo(bounds.getCenter());

  }

}

function dealersFitLocation(markers, type) {

  if( !markers.length ) {
    return false;
  }

  if( type == FB_CITY ) {

    var markersCities = markers.getColumn('city');

    if( $.unique(markersCities).length > 1 ) {
      type = FB_MARKERS;
    }

  }

  if( type == FB_MARKERS ) {

    var bounds = new google.maps.LatLngBounds();

    $.each( markers, function( index, marker ) {  
      var pos = 0;
      pos = new google.maps.LatLng(marker['lat'],marker['lng']);
      if(pos !== 0) {
        bounds.extend(pos);
      }
    });

    if(markers.length == 1 ) {
      map.fitBounds(bounds);
      var listener = google.maps.event.addListener(map, "idle", function() { 
          map.setZoom(12); 
          google.maps.event.removeListener(listener); 
      });                            
    } else {
      map.fitBounds(bounds);
      map.setCenter(bounds.getCenter());
    }

    return true;

  }

  var sampleMarker = markers[0];
  
  // Fix for New York state
  if(type == FB_STATE && sampleMarker['country'] == 220 && sampleMarker['countryZoneIsoCode'].toLowerCase() == "ny") {
    var nysPoints = [];
    nysPoints.push( {'lat': 45.207730, 'lng': -80.055420} );
    nysPoints.push( {'lat': 40.055527, 'lng': -80.110351} );
    nysPoints.push( {'lat': 39.929273, 'lng': -71.661865} );
    nysPoints.push( {'lat': 45.246418, 'lng': -71.530029} );
    dealersFitLocation(nysPoints, FB_MARKERS);
    return true;
  }

  var addressChoses = {};
  addressChoses[FB_COUNTRY] = sampleMarker['countryName'];
  addressChoses[FB_STATE] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneIsoCode'];
  addressChoses[FB_CITY] = sampleMarker['countryName'] + ', ' + sampleMarker['countryZoneName'] + ', ' + sampleMarker['city'];
  addressChoses[FB_MARKERS] = "";

  if( !(type in addressChoses) ) {
    return false;
  }

  var address = addressChoses[type];

  var pos = new google.maps.LatLng(sampleMarker['lat'],sampleMarker['lng']);

  var geocoder = new google.maps.Geocoder();
  geocoder.geocode( { 'address': address, 'location': pos}, function(results, status) {
    if (status == google.maps.GeocoderStatus.OK) {
      map.setCenter(results[0].geometry.location);
      map.fitBounds(results[0].geometry.viewport);
      if(type == FB_CITY || type == FB_MARKERS) {
          map.setZoom(settings.mapZoomLevel * 1);
      }
    }
  });

}

function belongsToCountry(marker, selectedCountry) {

  var markerCountry = marker['country'];

  return (markerCountry === selectedCountry || (selectedCountry === "row" && $.inArray(markerCountry, countriesList) === -1 ) || selectedCountry == 0);
}

function belongsToCountryZone(marker, selectedCountryZone) {
  return (marker['countryZone'] == selectedCountryZone || selectedCountryZone == 0);
}

function belongsToCategories(marker, selectedCategories) {
  var categories = JSON.parse( marker['categories'] );
  if( categories.length == 0 ) {
    return true;
  } else {
    return arrayIntersect(categories, selectedCategories);
  }
}

function containsSearchString(marker, searchString) {

  if(searchString == '') {
    return true;
  }

  if( typeof searchString === 'object') {
      return searchString.contains(markersArray[marker['uid']].getPosition());
  }

  if(/\d+/.test(searchString)) {

    var expression = /[\. ,:-]+/g;

    var processedZipcode = marker['zipcode'].replace(expression,'').toLowerCase();
    var processedSearchString = searchString.replace(expression,'').toLowerCase();

    if( processedZipcode.startsWith(processedSearchString) ) {
        return true;
    } else if( /^[a-zA-Z]+$/.test(processedZipcode.substr(0,2)) && processedZipcode.substr(2).startsWith(processedSearchString)) {
        return true;
    }

  } else {
    if( marker['city'].toLowerCase() == searchString.toLowerCase() ) {
      return true;
    }

  }

  return false;
}

function belongsToList(marker, list) {
  return ( $.inArray(marker['uid'], list) !== -1 );
}

function filterMarkers (allDealersListItems, selectedCountry, selectedCountryZone, searchString, fitBoundsType, extendedZipSearch, prefilteredList, boundsFailed) {

    var allDealersListItemsCopy = $(allDealersListItems).clone();

    var filteredDealers = [];
    var filteredDealersMarkers = [];

    var selectedCategories = getSelectedCategories();

    $.each( markers, function( index, marker ) {

      var isOk = [];

      if( $(".pxa-dealers .dealer-countries").length > 0 ) {
        isOk.push( belongsToCountry(marker, selectedCountry) );
      }

      if( $(".pxa-dealers .dealer-country-states").length > 0 ) {
        isOk.push( belongsToCountryZone(marker, selectedCountryZone) );
      }

      if( $(".pxa-dealers > .categories").length > 0 ) {
        isOk.push( belongsToCategories(marker, selectedCategories) );
      }

      if( $(".pxa-dealers .dealer-cityzip-search").length > 0 ) {
        isOk.push( containsSearchString(marker, searchString) );
      }

      if( prefilteredList !== undefined && prefilteredList.length > 0 ) {
        isOk.push( belongsToList(marker, prefilteredList) );
      }

      if( $.inArray(false, isOk) === -1 ) {

        if( !markersArray[marker['uid']].getVisible() ) {
          markersArray[marker['uid']].setVisible(true);
          if(settings.clusterMarkers == 1) {
            markerclusterer.addMarker(markersArray[marker['uid']], true);
          }
        }

        filteredDealers.push(marker['uid']);
        filteredDealersMarkers.push(marker);
      } else {
        if( markersArray[marker['uid']].getVisible() ) {
          markersArray[marker['uid']].setVisible(false);
          if(settings.clusterMarkers == 1) {
            markerclusterer.removeMarker(markersArray[marker['uid']], true);
          }
        }
      }

    });

    if(!/\d+/.test(searchString) && typeof searchString !== 'object' && boundsFailed != true) {
        if (searchString != '') {
            // City search
            var csCountryName = '';
            var csCountryZoneName = '';

            if (selectedCountry != 0 && selectedCountry != 'row') {
                csCountryName = countryNames[selectedCountry];
            }

            var csAddress = csCountryName + /*', ' + selectedCountryZone +*/ ', ' + searchString;

            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({'address': csAddress}, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK && results.length > 0) {

                    var extendBy = settings['cityZoneExtendBy'] * 1 / 100;
                    var bounds = results[0].geometry.bounds;

                    if (typeof bounds === 'object') {

                        // New NE position
                        var pos = new google.maps.LatLng(
                            bounds.getNorthEast().lat() + extendBy,
                            bounds.getNorthEast().lng() + extendBy
                        );

                        bounds.extend(pos);

                        // New SW position
                        var pos = new google.maps.LatLng(
                            bounds.getSouthWest().lat() - extendBy,
                            bounds.getSouthWest().lng() - extendBy
                        );

                        bounds.extend(pos);

                        filterMarkers(allDealersListItems, selectedCountry, selectedCountryZone, bounds, FB_CITY,
                            true);
                    } else {
                        filterMarkers(allDealersListItems, selectedCountry, selectedCountryZone, searchString, FB_CITY,
                            true,'', true);
                    }
                } else {
                    filterMarkers(allDealersListItems, selectedCountry, selectedCountryZone, searchString, FB_CITY,
                        true,'', true);
                }
            });

            return true;
        }

    }

    if(typeof userPosition == "object") {

        var closest = findClosest(filteredDealersMarkers, userPosition);
        var filteredDealers = [];
        var filteredDealersMarkers = [];

        $(markers).each(function(index, marker){
            if( $.inArray(marker['uid'], closest) !== -1 ) {

                if( !markersArray[marker['uid']].getVisible() ) {
                    markersArray[marker['uid']].setVisible(true);
                    if(settings.clusterMarkers == 1) {
                        markerclusterer.addMarker(markersArray[marker['uid']], true);
                    }
                }

                filteredDealers.push(marker['uid']);
                filteredDealersMarkers.push(marker);
            } else {
                if( markersArray[marker['uid']].getVisible() ) {
                    markersArray[marker['uid']].setVisible(false);
                    if(settings.clusterMarkers == 1) {
                        markerclusterer.removeMarker(markersArray[marker['uid']], true);
                    }
                }
            }
        });
    }

    if(extendedZipSearch === true && /\d+/.test(searchString) && typeof searchString !== 'object') {
      //var zipLimit = 10;
      //
      //if(filteredDealers <= zipLimit) {
      //  if(searchString.length > 2) {
      //    searchString = searchString.substring(0, searchString.length - 1);
      //    filterMarkers (allDealersListItems, selectedCountry, selectedCountryZone, searchString, fitBoundsType, extendedZipSearch);
      //    return 1;
      //  }
      //}

        if(filteredDealersMarkers.length == 0) {
          if(searchString.length > 2) {
            searchString = searchString.substring(0, searchString.length - 1);
            filterMarkers (allDealersListItems, selectedCountry, selectedCountryZone, searchString, fitBoundsType, extendedZipSearch);
            return 1;
          }
        } else {

            var bounds = new google.maps.LatLngBounds();
            extendBy = settings['zipZoneExtendBy'] * 1 / 100;

            $(filteredDealersMarkers).each(function(index,item){
                bounds.extend( markersArray[item['uid']].getPosition() );
            });

            // New NE position
            var pos = new google.maps.LatLng(
                bounds.getNorthEast().lat() + extendBy,
                bounds.getNorthEast().lng() + extendBy
            );

            bounds.extend(pos);

            // New SW position
            var pos = new google.maps.LatLng(
                bounds.getSouthWest().lat() - extendBy,
                bounds.getSouthWest().lng() - extendBy
            );

            bounds.extend(pos);

            filterMarkers(allDealersListItems, selectedCountry, selectedCountryZone, bounds, FB_CITY,
                true);

            return 1;
        }
    }

    if(settings.clusterMarkers == 1) {
      markerclusterer.repaint();
    }

    dealersFitLocation(filteredDealersMarkers, fitBoundsType);

    enabledDealersListItems = [];

    $(".pxa-dealers-list-container").fadeToggle( "fast", "linear", function() {

      $(".pxa-dealers-list-container").html('');

      allDealersListItemsCopy.each( function() {
        $this = $(this);
        if( $.inArray($this.attr("data-uid"), filteredDealers) !== -1 ) {
          enabledDealersListItems.push(this);
          $(".pxa-dealers-list-container").append(this);
        }
      });

      var golden = enabledDealersListItems.filter(function(a){
          if($(a).data("partnertype") == "1") {
              return 1;
          } else {
              return 0;
          }
      });

      var silver = enabledDealersListItems.filter(function(a){
          if($(a).data("partnertype") != "1") {
              return 1;
          } else {
              return 0;
          }
      });

      golden.sort(function(a, b){
          return $(a).find(".dealer-name").text() > $(b).find(".dealer-name").text() ? 1 : -1;
      });

      silver.sort(function(a, b){
          return $(a).find(".dealer-name").text() > $(b).find(".dealer-name").text() ? 1 : -1;
      });

      var all = golden.concat(silver);

      enabledDealersListItems = all;

      var counter = 0;
      var newRow;

      $(enabledDealersListItems).each(function() {

        if(counter === 0) {
          newRow = $("<div class='pxa-dealers-list " + settings.additionalListWrapperClass + "'></div>");
          $(".pxa-dealers-list-container").append(newRow);
        }

        $(newRow).append(this);

        counter++;

        if(counter == settings.newRow) {
          counter = 0;
        }

      });

      $("#dealers-count").html(enabledDealersListItems.length);

      $(".dealers-header").fadeToggle( "fast", "linear", function() {
        if(enabledDealersListItems.length <= 0) {
          $(".dealers-header").text(noResultsFoundLabel);
        } else {
          $(".dealers-header").html(originalDealersHeader);
          $(".dealers-header #dealers-count").html(enabledDealersListItems.length);
        }
        $(".dealers-header").fadeToggle( "fast", "linear");
      });
      
      $(".pxa-dealers-list-container").fadeToggle( "fast", "linear");
    });

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

  if(initialSearchValue.length > 0) {
    $(".pxa-dealers .dealer-cityzip-search").val(initialSearchValue);
    $(".pxa-dealers .dealer-countries").val($(".pxa-dealers .dealer-countries option:first").val());
  }

  allDealersListItems = $(".pxa-dealers-list-container .dealer-item").clone();

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position){
            userPosition = position;
            filterMarkers(allDealersListItems, $(".pxa-dealers .dealer-countries").val(), $(".pxa-dealers .dealer-country-states").val(), '', FB_MARKERS);
        }, function(){
        });
    } else {
    }

    initializeMapPxaDealers(true);

  // Show google street view
  $(document).on('click', '.street-switch-trigger', function(event) {
    event.preventDefault();
    if( $(this).attr("data-marker-id").length ) {
      switchToStreetView( $(this).attr("data-marker-id") );  
    }
  });

  if( $(".pxa-dealers .dealer-countries").length && $(".pxa-dealers .dealer-country-states").length ) {
    populateCountryZones( $(".pxa-dealers .dealer-countries").val() );  
  }
  
  $('form[name="searchDealers"]').on('submit',function(event){
    event.preventDefault();
    var url = $(this).attr('action').replace(/\/?$/, '/') + $(this).find('input[name="tx_pxadealers_pxadealerssearchresults[searchValue]"]').val();

    url = (url.charAt(0) != '/' ? ('/'+url) : url);

    window.document.location = url;
  });

  // Categories change
  $(".pxa-dealers > .categories .category-item input[type='checkbox']").on("change", function() {
    var $this = $(this);
    $this.parent().toggleClass('selected');

    filterMarkers(allDealersListItems, $(".pxa-dealers .dealer-countries").val(), $(".pxa-dealers .dealer-country-states").val(), $(".pxa-dealers .dealer-cityzip-search").val(), FB_MARKERS);

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

    filterMarkers(allDealersListItems, selectedCountry, $(".pxa-dealers .dealer-country-states").val(), '', fbType);

  });

  // Country zone change
  $(".pxa-dealers .dealer-country-states").on("change", function() {

    var selectedCountryZone = $(this).val();

    $(".pxa-dealers .dealer-cityzip-search").val('');

    var fbType = FB_STATE;

    if(selectedCountryZone == 0) {
      fbType = FB_COUNTRY;
    }

    filterMarkers(allDealersListItems, $(".pxa-dealers .dealer-countries").val(), $(".pxa-dealers .dealer-country-states").val(), '', fbType);
  });

  // City/zip search entered
  $(".pxa-dealers .dealer-cityzip-search").keypress(function (e) {
    if(e.which == 13) {

        var searchValue = $(this).val();

        filterMarkers(allDealersListItems,
            $(".pxa-dealers .dealer-countries").val(),
            $(".pxa-dealers .dealer-country-states").val(),
            searchValue,
            FB_CITY,
            true);

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

          filterMarkers( allDealersListItems,
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