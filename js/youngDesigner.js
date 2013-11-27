$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('.contest-menu-text').removeClass('candgselected');

  $('#saveContestSubmission').click(function() {
    if (($('#entry-title').val()).trim() == '') {
      $('#error').html('Entry title should not be empty');
      return false;
    }
    if (($('#submitter-info').val()).trim() == '') {
      $('#error').html('Submitter bio should not be empty');
      return false;
    }
    $('#error').html('');
  });
});
   