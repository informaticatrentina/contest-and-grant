var e_date = '';
var s_date = '';
$(document).ready(function() {
  if ($('#start_date').length > 0) { 
    $( "#start_date" ).datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        $( "#end_date" ).datepicker( "option", "minDate", selectedDate );
        
      }
    });
    $( "#end_date" ).datepicker({
      changeMonth: true,
      changeYear: true,
      yearRange:"-90:+0",
      onSelect: function( selectedDate ) {
        $( "#start_date" ).datepicker( "option", "maxDate", selectedDate );
       
      }
    });
  }
});    
