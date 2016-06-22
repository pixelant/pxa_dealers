$( document ).ready(function() {

  $(".import-dealers-form .submit-btn").on("click", function(e) {

    e.preventDefault();

    var storagePid = $(".import-dealers-form .storage-pid").val();

    // Params
    var params = {};
    params['tx_pxadealers_user_pxadealersimport'] = {
      'storagePid' : storagePid
    }

    // getDealersInfoAjaxUrl is instantiated in the template... yeah..
    $.post( getDealersInfoAjaxUrl, params).done( function(data) {

      if( typeof data != undefined && data != "") {
        var dataParsed = JSON.parse(data);
        $('#warning-modal .dealers-count').html(dataParsed.dealersCount);
      }

    });

    $('#warning-modal .folder-uid').html(storagePid);
    $('#warning-modal').modal('show');

  });

  $('#warning-modal .approve-form-submit').on("click", function(){

    $(".import-dealers-form").submit();

  });

});