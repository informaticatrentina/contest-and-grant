var e_date = '';
var s_date = '';
$(document).ready(function() {
  if ($('#startDate').length > 0) { 
    $( "#startDate" ).datetimepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        var endDateTime = $("#endDate" ).val();
        var startDateTime = $("#startDate" ).val();
        $( "#endDate" ).datepicker( "option", "minDate", selectedDate );    
        if (new Date(startDateTime) > new Date(endDateTime)) {
          $("#endDate" ).val(startDateTime);
        } else {
           $("#endDate" ).val(endDateTime);
        }
      }
    });
    $( "#endDate" ).datetimepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        var endDateTime = $("#endDate" ).val();
        var startDateTime = $("#startDate" ).val();
         $( "#startDate" ).datepicker( "option", "maxDate", selectedDate );    
         if (new Date(startDateTime) > new Date(endDateTime)) {
          $("#startDate" ).val(endDateTime);
        } else {
          $("#startDate" ).val(startDateTime);
        }
      }
    });
    $( "#juryRatingStartDate" ).datetimepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        var endDateTime = $("#juryRatingEndDate" ).val();
        var startDateTime = $("#juryRatingStartDate" ).val();
        $( "#juryRatingEndDate" ).datepicker( "option", "minDate", selectedDate );    
        if (new Date(startDateTime) > new Date(endDateTime)) {
          $("#juryRatingEndDate" ).val(startDateTime);
        } else {
           $("#juryRatingEndDate" ).val(endDateTime);
        }
      }
    });
    $( "#juryRatingEndDate" ).datetimepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        var endDateTime = $("#juryRatingEndDate" ).val();
        var startDateTime = $("#juryRatingStartDate" ).val();
         $( "#juryRatingStartDate" ).datepicker( "option", "maxDate", selectedDate );    
         if (new Date(startDateTime) > new Date(endDateTime)) {
          $("#juryRatingStartDate" ).val(endDateTime);
        } else {
          $("#juryRatingStartDate" ).val(startDateTime);
        }
      }
    });
  }
  
  $('.login-link').click(function() {
    var pathname = window.location.pathname;
    $.pageslide({ direction: 'left', href: page.base_url + 'login?back=' + pathname });
    return false; 
  });
  
  $('.register-link').click(function() {
    window.location.replace(page.registration_url);
  });
});    

function emailValidation(emailAddress) {
  var emailRegExp = /^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i;
  emailAddress = emailAddress.trim();
  if (emailAddress == '') {
    return false;
  }
  return emailRegExp.test(emailAddress);
}