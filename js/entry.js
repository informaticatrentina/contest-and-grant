var postLimit = 20;
var entriesOffset = postLimit;
var flag = true;
var count = postLimit;

$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');  
  
  $(window).scroll(function() {
    var totalEntry = $("#entryCount").val();
    if(count >= totalEntry) {
      return false;
    } 
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      $('#loading-image').show();
      var pagrUrl = $('#submitContestEntry').val();   
      var category = $('#entryCategory').val();  
      if (flag) {
        flag = false;
        $.ajax({
          type: 'GET',
          url: pagrUrl,
          dataType: 'json',
          data: {
            offset: entriesOffset,
            category: category
          },
          success: function(resp) {
            $('#loading-image').hide();
            if (resp.success) {
              entriesOffset += postLimit;
              var data = resp.data;
              var html = '';
              for (key in data) {
                count++;
                html += '<div class="post span4">';
                html += '<div style="padding: 10px 10px;"><h6>' + data[key].title + '</h6></div>';
                html += '<a href="' + pagrUrl + '/' + data[key].id + '"><img src="' + data[key].image + '" width="600" height="450" /></a>';
                html += '<div style="padding: 10px 10px 0px 10px; vertical-align:bottom;"> di ' + data[key].authorName + ' </div>';
                html += '<div style="padding: 0px 10px 10px 10px; vertical-align:top;"> tag: ' + data[key].categoryName + ' </div></div>';
              }
              $('#posts').append(html).masonry('reload') ;     
              flag = true;
            } else {
              alert(resp.msg);
            }            
          },
          error: function() {
            $('#loading-image').hide();
          }
        });
      }     
      return false;
    }
   });
});