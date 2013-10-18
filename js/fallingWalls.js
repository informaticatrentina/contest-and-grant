var postLimit = 20;
var entriesOffset = postLimit;
var flag = true;
var count = postLimit;

$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('.contest-menu-text').removeClass('candgselected');

  $("#posts").imagesLoaded(function() { 
    loadMasonary();
  });

  $(window).scroll(function() {
    var totalEntry = $("#entryCount").val();
    if(count >= totalEntry) {
      return false;
    } 
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      $('#loading-image').show();     
      if (flag) {
        flag = false;
        $.ajax({
          type: 'GET',
          url: page.loadContestEntryUrl,
          dataType: 'json',
          data: {
            offset: entriesOffset
          },     
          success: function(resp) {
            $('#loading-image').hide();
            if (resp.success) {
              entriesOffset += postLimit;
              var data = resp.data;
              var html = '';
              for (key in data) {
                count++;
                html += '<div class="post span4">\n\
                           <div style="padding: 10px 10px;">\n\
                             <h6><a href="'+ page.loadContestEntryUrl + data[key].id +'">'+ data[key].title +'</a></h6>\n\
                           </div>\n\
                           <a href="'+ page.loadContestEntryUrl + '/' + data[key].id +'">\n\
                             <img src= "'+ data[key].video_image_Url +'" width="600" height="450" />\n\
                           </a>\n\
                           <div style="padding: 10px 10px 0px 10px; vertical-align:bottom;"> di '+ data[key].author_name +'</div>\n\
                         </div>';
              }
              $('#posts').append(html).masonry('reload'); 
              $("#posts").imagesLoaded(function() {
                loadMasonary();
              });
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

function loadMasonary() {
  $("#posts").masonry({
    itemSelector: '.post',
    isAnimated: true,
    columnWidth: function() {
      var width = $(window).width();
      var col = 200;
      if (width < 1200 && width >= 980) {
        col = 160;
      } else if (width < 980 && width >= 768) {
        col = 8;
      }
      return col;
    }
  });
}