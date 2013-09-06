$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  $('#rightCheckBox').click(function() {
    if ($(this).is(':checked')) {
      $('#leftCheckbox').attr('checked', false);
      $('#minor').show();
    } else {
      $('#minor').hide();
    }
  });
  $('#leftCheckbox').click(function() {
    if ($(this).is(':checked')) {
      $('#rightCheckBox').attr('checked', false);
      $('#minor').hide();
    }
  });
  $('#contestEntrySubmitbutton').click(function() {
    if(($('#entryTitle').val()).trim() == '') {
      $('.alert-error').html(Yii.t('js', 'Entry title should not be empty'));
      return false;
    }
    if(($('#entryDescription').val()).trim() == '') {
      $('.alert-error').html(Yii.t('js', 'Entry description should not be empty'));
      return false;
    }
    if ($('#rightCheckBox').is(':checked')) {
      if ($('#minorName').val() == '') {
        $('.alert-error').html(Yii.t('js', 'Please enter minor name'));
        return false;
      }
    }
    if (!$('#rightCheckBox').is(':checked') && !$('#leftCheckbox').is(':checked')) {
      $('.alert-error').html(Yii.t('js', 'Please check atleast one checkBox'));
      return false;
    }
    $('.alert-error').html('');
  });
  $('#entrySubmitButton').click(function() {
    $(this).addClass('active');
    $('#contestBrief').removeClass('active');
    $('#briefContent').hide();
    $('#entrySubmission').show();
    return false;
  });
  $('#contestBrief').click(function() {
    $(this).addClass('active');
    $('#entrySubmitButton').removeClass('active');
    $('#briefContent').show();
    $('#entrySubmission').hide();
    return false;
  });
  
  $('.winner-status').click(function() {
    var area = $(this);
    var contestId = $(this).attr('contestId');
    var status = $(this).html();
    
    $.ajax({
      type: 'GET',
      url: page.url,
      dataType: 'json',
      data: {
        id: contestId,
        status: status
      },      
      success: function(resp) {   
        if (resp.success) {
          area.html(resp.status);
        } else {
          area.html(status);
        }
      }, 
      error: function(resp){
        area.html(status);
      }        
    });
  });
});