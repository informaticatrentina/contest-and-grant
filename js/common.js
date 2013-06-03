var e_date = '';
var s_date = '';
$(document).ready(function() {
  if ($('#startDate').length > 0) { 
    $( "#startDate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        $( "#endDate" ).datepicker( "option", "minDate", selectedDate );        
      }
    });
    $( "#endDate" ).datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        $( "#startDate" ).datepicker( "option", "maxDate", selectedDate );       
      }
    });
  }
});    
