$(document).ready(function() {
  $('#home').find("a").removeClass('candgselected');
  $('#createContest').find("a").addClass('candgselected');
  
  $('#contestEntrySubmitbutton').click(function() {
    if(($('#entryTitle').val()).trim() == '') {
      $('.alert-error').html(Yii.t('js', 'Entry title should not be empty'));
      return false;
    }
    if(($('#entryDescription').val()).trim() == '') {
      $('.alert-error').html(Yii.t('js', 'Entry description should not be empty'));
      return false;
    }
    var url = ($('#videoUrl').val()).trim();
    var urlRegex = /^(https?:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?$/;
    if (url != '') {
      if (!urlRegex.test(url)) {
        $('.alert-error').html(Yii.t('js', 'Please enter valid video url'));
        return false;
      }
    }
    
    if(!$('#checkBox').is(":checked")) {
      $('.alert-error').html(Yii.t('js', 'Please checked check box'));
      return false;
    }
    $('.alert-error').html('');
  }); 
}); 
   