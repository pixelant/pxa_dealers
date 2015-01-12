var map;
var originalDealersHeader = $("#dealers-header-original").html();
var allDealersListItems;

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
    var stylesObj = $.parseJSON(mapStylesJSON);
  } catch(err) {
    stylesIsJSON = false;
    console.log("settings.map.stylesJSON has to be JSON formatted string");
  }

  if(stylesIsJSON) {

    var styledMap = new google.maps.StyledMapType(stylesObj, {name: mapName});

    map.mapTypes.set('map_style', styledMap);
    map.setMapTypeId('map_style');
  }

  panorama = map.getStreetView();

  for (i = 0;  i < markers.length; i++) {

    if((markers[i]['lat'] != '') && (markers[i]['lng'] != '')) {

      var pos = new google.maps.LatLng(markers[i]['lat'],markers[i]['lng']);

      getAddress(pos,map,infowindow,function(markerMap){
        markersArray[markers[i]['uid']] = markerMap;
        bounds.extend(pos);
      });

    }    
  };
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

  if(doMarkersFilter === true) {
    filterMarkers( $(".pxa-dealers-list-container .dealer-item"), $(".pxa-dealers > .dealer-countries").val(), true );
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
  var markerIcon = mapMarkerImage;

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
      imageStreetPreviewContent += '<img src="http://maps.googleapis.com/maps/api/streetview?size=90x70&location='+markers[i]['lat']+','+markers[i]['lng']+'&sensor=false" /><br>';
      imageStreetPreviewContent += '<span class="street-switch-trigger website-link" onClick="switchToStreetView('+i+');">Streetview</span>';
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

  }

}

function belongsToCountry(marker, selectedCountry) {
  var markerCountry = marker['country'];
  markerCountry = markerCountry.toLowerCase().trim();
  return (markerCountry === selectedCountry || (selectedCountry === "row" && $.inArray(markerCountry, countriesList) === -1 ) );
}

function belongsToCategories(marker, selectedCategories) {
  var categories = JSON.parse( marker['categories'] );
  if( categories.length == 0 ) {
    return true;
  } else {
    return arrayIntersect(categories, selectedCategories);
  }
}

function filterMarkers (allDealersListItems, selectedCountry, fitBounds) {

    var filteredDealers = [];
    var filteredDealersMarkers = [];

    var selectedCategories = getSelectedCategories();

    $.each( markers, function( index, marker ) {

      var isOk = [];

      if( $(".pxa-dealers > .dealer-countries").length > 0 ) {
        isOk.push( belongsToCountry(marker, selectedCountry) );
      }

      if( $(".pxa-dealers > .categories").length > 0 ) {
        isOk.push( belongsToCategories(marker, selectedCategories) );
      }

      if( $.inArray(false, isOk) === -1 ) {
        markersArray[marker['uid']].setVisible(true);
        filteredDealers.push(marker['uid']);
        filteredDealersMarkers.push(marker);
      } else {
        markersArray[marker['uid']].setVisible(false);
      }

    });

    //if(fitBounds) {
      dealersFitBounds(filteredDealersMarkers);  
    //}

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

      var counter = 0;
      var newRow;

      $(enabledDealersListItems).each(function() {

        if(counter === 0) {
          newRow = $("<div class='pxa-dealers-list " + dealersRowAdditionalClass + "'></div>");
          $(".pxa-dealers-list-container").append(newRow);
        }

        $(newRow).append(this);

        counter++;

        if(counter == dealersRows) {
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


$( document ).ready(function() {
  
  $('form[name="searchDealers"]').on('submit',function(event){
    event.preventDefault();
    var url = $(this).attr('action').replace(/\/?$/, '/') + $(this).find('input[name="tx_pxadealers_pxadealerssearchresults[searchValue]"]').val();

    url = (url.charAt(0) != '/' ? ('/'+url) : url);

    window.document.location = url;
  });

  allDealersListItems = $(".pxa-dealers-list-container .dealer-item");

  $(".pxa-dealers > .categories .category-item input[type='checkbox']").on("change", function() {
    var $this = $(this);
    $this.parent().toggleClass('selected');

    filterMarkers(allDealersListItems, $(".pxa-dealers > .dealer-countries").val(), false);

  });

  $(".pxa-dealers > .dealer-countries").on("change", function() {
    var $this = $(this);

    var selectedCountry = $this.val();

    filterMarkers(allDealersListItems, selectedCountry, true);

  });  

});