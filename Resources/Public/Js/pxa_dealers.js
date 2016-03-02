function runAjax(position) {
  $('#ajax-load-dealer').fadeIn();

  url = '/?type=7505726&tx_pxadealers_pxadealerssearchresults%5Blatitude%5D='+position.coords.latitude+'&tx_pxadealers_pxadealerssearchresults%5Blongitude%5D='+position.coords.longitude;
  $.ajax({
    dataType: "json",
    url: url
  })
  .done(function(data){
    if(data.count > 0) {
      $('#ajax-load-dealer .pre-loader').remove();
      $('#ajax-load-dealer .dealers-header.after-load').show();
      $('#ajax-load-dealer').append(data.html);
      initializeMapPxaDealers();
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
    console.log = "Geolocation is not supported by this browser."
  }
}
function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            console.log = "User denied the request for Geolocation."
            break;
        case error.POSITION_UNAVAILABLE:
            console.log = "Location information is unavailable."
            break;
        case error.TIMEOUT:
            console.log = "The request to get user location timed out."
            break;
        case error.UNKNOWN_ERROR:
            console.log = "An unknown error occurred."
            break;
    }
}

function showDefaultMap() {
  var mapOptions = {
            center: new google.maps.LatLng(52.2547,5.3833), 
            zoom: 7,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
      };
  var map = new google.maps.Map(document.getElementById("pxa-dealers-map"),mapOptions);
  //checkLocation();
}
function initializeMapPxaDealers() {
  /*var styles = '[{ "elementType": "geometry", "stylers": [ { "hue": "#0055ff" }, { "saturation": -84 } ] },{ "elementType": "labels", "stylers": [ { "hue": "#0022ff" }, { "saturation": -69 } ] }]';
  var stylesObj = $.parseJSON(styles);
  var styledMap = new google.maps.StyledMapType(stylesObj,{name: "Map custom"});*/

  var bounds = new google.maps.LatLngBounds();       
  var infowindow = new google.maps.InfoWindow();       
                 
  var mapOptions = {
          mapTypeControlOptions: {
            //mapTypeIds: [google.maps.MapTypeId.ROADMAP, 'map_style']
            mapTypeIds: [google.maps.MapTypeId.ROADMAP]
          }            
  };
  var map = new google.maps.Map(document.getElementById("pxa-dealers-map"),mapOptions);
 
  //map.mapTypes.set('map_style', styledMap);
 // map.setMapTypeId('map_style');
  panorama = map.getStreetView();

  for (i = 0;  i < markers.length; i++) {
    if((markers[i]['lat'] != '') && (markers[i]['lng'] != '')) {
      var pos = new google.maps.LatLng(markers[i]['lat'],markers[i]['lng']);
              
      getAddress(pos,map,infowindow,function(markerMap){
        markersArray.push(markerMap);                             
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
  var mc = new MarkerClusterer(map, markersArray);
}
function switchToStreetView(i) {
  var position = markersArray[i].getPosition();
  panorama.setPosition(position);
  panorama.setPov({
    heading: 265,
    zoom:1,
    pitch:0}
  );
  panorama.setVisible(true);
  return false;
}
function getAddress(pos,map,infowindow, callback) {
  var markerIcon = "typo3conf/ext/pxa_dealers/Resources/Public/Icons/map_marker_icon_blue.png";

//  var markerIcon = "fileadmin/Pixelant/Extensions/pxa_dealer/map_marker_icon_dblue.png";

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


$( document ).ready(function() {
  
  $('form[name="searchDealers"] [type="radio"]').on('change', function(){
    $('[checked="checked"]').attr('checked',false)
    $(this).attr('checked','true')
  })


  $('form[name="searchDealers"]').on('submit',function(event){
    event.preventDefault();
    var url = $(this).attr('action').replace(/\/?$/, '/') + $(this).find('input[name="tx_pxadealers_pxadealerssearchresults[searchValue]"]').val();
    var url = url + '/' + $('[checked]').attr('value');
    
    url = (url.charAt(0) != '/' ? ('/'+url) : url);

    window.document.location = url;
  });

  var numberOfRows = Math.floor(resultLimit/numberOfColumns);

  for (i = 0; i < numberOfRows; i++) { 
      $('#' + i).fadeIn();
  }

  $(window).scroll(function() {
  var lastRaw = $('.pxa-dealers-list').filter(":visible").last();
  var lastRawId = $('.pxa-dealers-list').filter(":visible").last().attr('id');
  var bottomOfDocWithOffset = ($(document).height() - $(window).height()) - ($(window).height()/3); //avoid offset().top because of css
    if ($(window).scrollTop() >= bottomOfDocWithOffset) {
      var tempRawId = parseInt(lastRawId, 10);
      for (i = tempRawId + 1; i <= tempRawId + numberOfRows; i++) { 
          $('#' + i).fadeIn();
      }
    }
  });


});

