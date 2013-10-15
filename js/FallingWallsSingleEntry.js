$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('.contest-menu-text').removeClass('candgselected');  

  var widthOfPostClass = $('.single-entry-image').css('width');
  var widthForPlayBtn = (parseInt(widthOfPostClass) / 2) - 50;
  $('.play-button').css('left', widthForPlayBtn);

  var heightOfImage = $('.thumbnail-image').height();
  var heightForPlayBtn = heightOfImage / 2 - 50;
  $('.play-button').css('top', heightForPlayBtn);

  !function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (!d.getElementById(id)) {
      js = d.createElement(s);
      js.id = id;
      js.src = "https://platform.twitter.com/widgets.js";
      fjs.parentNode.insertBefore(js, fjs);
    }
  }
  (document, "script", "twitter-wjs");

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
    var width = $('.single-entry-image').css('width');
    width = parseInt(width) - 5;
    switch (videoDomain) {
      case 'youtube':
        html = '<iframe width="' + width + '" height="' + height + '" src="' + youtubeEmbedVideoUrl + videoId + '?rel=0&autoplay=1"></iframe>';
        break;
      case 'vimeo':
        html = '<iframe width="' + width + '" height="' + height + '" src="' + vimeoEmbedVideoUrl + videoId + '?autoplay=1"></iframe>';
        break;
      default:
        html = '';
    }
    $(this).html(html);
  });

  var windowWidth = $(window).width();
  $(window).resize(function() {
    var currentWidth = $(window).width();
    if (Math.abs(windowWidth - currentWidth) > 100) {
      windowWidth = currentWidth;
      var widthOfPostClass = $('.single-entry-image').css('width');
      var widthForPlayBtn = (parseInt(widthOfPostClass) / 2) - 50;
      $('.play-button').css('left', widthForPlayBtn);

      var heightOfImage = $('.thumbnail-image').height();
      var heightForPlayBtn = heightOfImage / 2 - 50;
      $('.play-button').css('top', heightForPlayBtn);
    }
  });
});