$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('.contest-menu-text').removeClass('candgselected');

  $('#saveContestSubmission').click(function() {
    if (($('#entry-title').val()).trim() == '') {
      $('#error').html(Yii.t('js','Entry title should not be empty'));
      return false;
    }
    if (($('#submitter-info').val()).trim() == '') {
      $('#error').html(Yii.t('js','Submitter bio should not be empty'));
      return false;
    }
    var videoLink = ($('#video-link').val()).trim();
    if (($('#pdf-file').val()).trim() == '' && videoLink == '' ) {
      $('#error').html(Yii.t('js','Please either upload pdf file or add youtube video url'));
      return false;
    }
    if (videoLink != '') {
      var urlRegex = /^(https?:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
      if (!urlRegex.test(videoLink)) {
        $('#error').html(Yii.t('js', 'Please enter valid video url'));
        return false;
      }
    }
    $('#error').html('');
  });
});
   