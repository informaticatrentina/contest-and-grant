$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('.contest-menu-text').removeClass('candgselected');

  $('#saveContestSubmission').click(function() {
    if (($('#motivational-letter').val()).trim() == '') {
      $('#error').html(Yii.t('js','Please upload your motivational letter'));
      focusONError();
      return false;
    }
    if (($('#curriculum-vitae').val()).trim() == '') {
      $('#error').html(Yii.t('js','Please upload your curriculum vitae'));
      focusONError();
      return false;
    }
    if (!$('#confirmation-checkbox').is(':checked')) {
      $('#error').html(Yii.t('js', 'Please checked checkbox'));
      focusONError();
      return false;
    }
    $('#error').html('');
  });
});
   
function focusONError() {
  $('html, body').animate({
    scrollTop: $("#error").offset().top - 100
  }, 2);
}   