$(document).ready(function() {
  //check when left checkbox is checked than right shoudl be unchecked and vice a versa and minor field should be hidden 
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
  $('#entrySubmitbutton').click(function() {
    if ($('#rightCheckBox').is(':checked')) {
      if ($('#minorName').val() == '') {
        $('.alert-error').html(Yii.t('js', 'Please enter minor name'));
        return false;
      }
    }
    console.log($('#leftCheckbox').prop(':checked'));
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
});