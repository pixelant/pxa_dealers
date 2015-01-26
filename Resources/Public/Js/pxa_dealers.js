var map;
var markerclusterer;
var originalDealersHeader;
var allDealersListItems;
var countriesList;

var FB_COUNTRY = 0;
var FB_STATE = 1;
var FB_CITY = 2;
var FB_MARKERS = 3;
var FB_NONE = 4;

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
  countriesList = settings['mainCountries'].split(",");

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
    if(data.count > 0) {
      $('#ajax-load-dealer .pre-loader').remove();
      $('#ajax-load-dealer .dealers-header.after-load').show();
      $('#ajax-load-dealer').append(data.html);

      initializeMapPxaDealers(false);
      allDealersListItems = $(".pxa-dealers-list-container .dealer-item");
      originalDealersHeader = $("#dealers-header-original").html();
    } else {
      $('#ajax-load-dealer').fadeOut();
    }
  })
  .fail(function( jqXHR, textStatus ) {
    console.log( "Request failed: " + textStatus );
  });
}

function checkLocation() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(runAjax, showError);
  } else {
    runAjax();
    console.log ("Geolocation is not supported by this browser.");
  }
}

function showError(error) {
    runAjax();
    switch(error.code) {
        case error.PERMISSION_DENIED:
            console.log ("User denied the request for Geolocation.");
            break;
        case error.POSITION_UNAVAILABLE:
            console.log ("Location information is unavailable.");
            break;
        case error.TIMEOUT:
            console.log ("The request to get user location timed out.");
            break;
        case error.UNKNOWN_ERROR:
            console.log ("An unknown error occurred.");
            break;
    }
}

function showDefaultMap() {
  var mapOptions = {
            center: new google.maps.LatLng(51.165691,10.451526), 
            zoom: 4,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
      };
  var map = new google.maps.Map(document.getElementById("pxa-dealers-map"),mapOptions);
  checkLocation();
}

function arrayIntersect (a, b) {
  for (var i = a.length - 1; i >= 0; i--) {
    if( $.inArray(a[i], b) !== -1 ) {
      return true;
    }
  };

  return false;
}

function initializeMapPxaDealers(doMarkersFilter) {

  console.log("init");

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
    console.log("settings.map.stylesJSON has to be JSON formatted string");
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
    filterMarkers( $(".pxa-dealers-list-container .dealer-item"), 
        $(".pxa-dealers .dealer-countries").val(), 
        $(".pxa-dealers .dealer-country-states").val(), 
        '',
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
    }
  });

}

function belongsToCountry(marker, selectedCountry) {

  var markerCountry = marker['country'];

  //markerCountry = markerCountry.toLowerCase().trim();
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

  if(/\d+/.test(searchString)) {

    var expression = /[\. ,:-]+/g;

    var processedZipcode = marker['zipcode'].replace(expression,'').toLowerCase();
    var processedSearchString = searchString.replace(expression,'').toLowerCase();

    if( processedZipcode.startsWith(processedSearchString) || processedZipcode.substr(2).startsWith(processedSearchString)) {
      return true;
    }

  } else {

    if( marker['city'].toLowerCase().startsWith(searchString.toLowerCase()) ) {
      return true;
    }
  }

  return false;
}

function filterMarkers (allDealersListItems, selectedCountry, selectedCountryZone, searchString, fitBoundsType) {

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

    if(settings.clusterMarkers == 1) {
      markerclusterer.repaint();
    }

    //dealersFitBounds(filteredDealersMarkers);

    dealersFitLocation(filteredDealersMarkers, fitBoundsType);

    //dealersFitLocation(marker, type);

    enabledDealersListItems = [];

    $(".pxa-dealers-list-container").fadeToggle( "fast", "linear", function() {

      $(".pxa-dealers-list-container").html('');

      allDealersListItems.each( function() {
        $this = $(this);
        if( $.inArray($this.attr("data-uid"), filteredDealers) !== -1 ) {
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

$( document ).ready(function() {

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

  allDealersListItems = $(".pxa-dealers-list-container .dealer-item");

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
          FB_CITY);
    }
  });

});