var postLimit = 20;
var entriesOffset = postLimit;
var flag = true;
var count = postLimit;

$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');

  $("#posts").imagesLoaded(function() {
    $("#posts").masonry({
      itemSelector: '.post',
      isAnimated: true,
      columnWidth: function(containerWidth) {
        var width = $(window).width();
        var col = 200;

        var widthOfPostClass = $('.post').css('width');
        var widthForPlayBtn = parseInt(widthOfPostClass) / 2 - 20;
        $('.play-button').css('left', widthForPlayBtn);
        $('#posts').find('.post').each(function() {
          var heightOfImage = $(this).find('.thumbnail-image').height();
          var heightForPlayBtn = heightOfImage / 2 - 20;
          $(this).find('.play-button').css('top', heightForPlayBtn);

          var currentHtml = $(this).find('.thumbnail-image').html();
          if (currentHtml.indexOf('iframe') != -1) {
            var widthOfIframe = parseInt(widthOfPostClass) - 5;
            $(this).find('.thumbnail-image iframe').attr('width', widthOfIframe);
            $(this).find('.thumbnail-image iframe').attr('height', heightOfImage);
          }
        });

        if (width < 1200 && width >= 980) {
          col = 160;
        } else if (width < 980 && width >= 768) {
          col = 8;
        }
        return col;
      }
    });
  });

  $('.thumbnail-image').live('click', function() {
    var youtubeEmbedVideoUrl = page.youtubeEmbedVideoUrl;
    var vimeoEmbedVideoUrl = page.vimeoEmbedVideoUrl;
    var currentHtml = $(this).html();
    if (currentHtml.indexOf('iframe') != -1) {
      return false;
    }
    var html = '';
    var videoId = $(this).attr('video-id');
    var videoDomain = $(this).attr('video-domain');
    var height = $(this).height();
    var  width = $('.post').css('width') ;
    width = parseInt(width) - 5;
    switch (videoDomain) {
      case 'youtube':
        html = '<iframe width="' + width + '" height="'+ height +'" src="' + youtubeEmbedVideoUrl + videoId + '?rel=0&autoplay=1"></iframe>';
        break;
      case 'vimeo':
        html = '<iframe width="' + width  + '" height="'+ height +'" src="' + vimeoEmbedVideoUrl + videoId + '?autoplay=1"></iframe>';
        break;
      default:
        html = '';
    }
    $(this).html(html);
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
                var height = (data[key].video_image_dimension.height)/4; 
                html += '<div class="post span4">\n\
                         <div style="padding: 10px 10px;">\n\
                           <h6>'+ data[key].title +'</h6>\n\
                         </div>\n\
                         <div class="thumbnail-image" video-id="'+ data[key].video_id +'" video-domain="'+ data[key].video_domain +'">\n\
                           <img src= "'+ data[key].video_image_Url +'" width="600" height="450" />\n\
                           <span class="play-button" style="top:'+ height +';">\n\
                             <img width="50" src="'+ data[key].play_button_url +'" alt="play"> \n\
                           </span>\n\
                         </div>\n\
                         <div style="padding: 10px 10px 0px 10px; vertical-align:bottom;"> di '+ data[key].author_name +'</div>\n\
                       </div>';
             }
             $('#posts').append(html).masonry('reload');     
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
