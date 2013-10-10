$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');


  $("#posts").masonry({
    itemSelector: '.post',
    isAnimated: true,
    columnWidth: function(containerWidth) {
      var width = $(window).width();     
      var col = 200;
      
      var widthOfPostClass = $('.post').css('width');
      var widthForPlayBtn = parseInt(widthOfPostClass)/2 - 20;
      $('.play-button').css('left', widthForPlayBtn);
      $('#posts').find('.post').each(function(){
        var heightOfImage = $(this).find('.thumbnail-image').height();
        var heightForPlayBtn = heightOfImage/2 -20;
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

  $('.thumbnail-image').click(function() {
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
});
