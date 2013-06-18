var entriesOffset = 0;
var postLimit = 20;
var flag = true;
var count = 0;

$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');  
  
  $(window).scroll(function() {
    var totalEntry = $("#entryCount").val();
    if(count == totalEntry) {
      return false;
    } 
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      $('#loading-image').show();
      var pagrUrl = $('#submitContestEntry').val();      
      if (flag) {
        flag = false;
        $.ajax({
          type: 'GET',
          url: pagrUrl,
          dataType: 'json',
          data: {
            offset: entriesOffset
          },
          success: function(resp) {
            
            if (resp.success) {
              entriesOffset += postLimit;
              var data = resp.data;
              var html = '';
              for (key in data) {
                count++;
                html += '<div class="post span4">';
                html += '<div style="padding: 10px 10px;"><h6>' + data[key].title + '</h6></div>';
                html += '<a href="' + pagrUrl + '/' + data[key].id + '"><img src="' + data[key].image + '" width="600" height="450" /></a>';
                html += '<div style="padding: 10px 10px; vertical-align:bottom;"> di ' + data[key].authorName + ' </div></div>';
              }
              $('#posts').append(html).masonry('reload') ;               
              $('#loading-image').hide();
              flag = true;
            } else {
              alert(resp.msg);
            }
          }
        });
      }     
      return false;
    }
   });
});