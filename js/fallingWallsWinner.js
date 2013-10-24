var prevWeight = '';
$(document).ready(function() {
  $(".add-entry").live('click',function() {
    var entryId = $(this).attr('id');
    if (entryId != '') {
      $('#add-entry-id').attr('value',entryId);
    }    
  });
  
  $('#add-prize-modal-save').live('click',function(e) { 
    e.preventDefault();
    submitPrizeModal('add-prize-modal-form');    
  });    
  
  $('.delete-winner').click(function(e) {
    var response =  confirm(Yii.t('js', "Are you sure you want to remove this winner ?"));
    if (!response) {
      return false;
    }  
  });  
  
  $('.update-winner').click(function(){
    var entryId = $(this).attr('id');
    var weight = $(this).attr('weight');
    var prizeTitle = $(this).attr('prize');
    prevWeight = weight;
    $('#edit-entry-id').attr('value',entryId);
    $('#prize').attr('value',prizeTitle);
    $('#weight').attr('value',weight);
    
  }); 
  $('#edit-prize-modal-save').click(function() {
    submitPrizeModal('update-winner-form');
  });
});

function submitPrizeModal(formId) {
  var prizeWeightArr = Array();
  var prize = $('#prize').val();
  var weight = $('#weight').val();
  var prizeWeight = $('#winnerWeight').val();
  if (prizeWeight != '') {
    prizeWeightArr = prizeWeight.split(",");
  }

  if ($.trim(prize) == '') {
    $('#add-prize-modal-error').show();
    $('#add-prize-modal-error').html(Yii.t('js', 'Please enter prize'));
    return false;
  }
  if ($.trim(weight) == '') {
    $('#add-prize-modal-error').show();
    $('#add-prize-modal-error').html(Yii.t('js', 'Please enter prize weight'));
    return false;
  } else if (!(/^\d+$/).test($.trim(weight))) {
    $('#add-prize-modal-error').show();
    $('#add-prize-modal-error').html(Yii.t('js', 'Prize weight should be numeric'));
    return false;
  } else if (prizeWeightArr.indexOf(weight) > -1 && prevWeight != weight) { 
    $('#add-prize-modal-error').show();
    $('#add-prize-modal-error').html(Yii.t('js', 'Prize weight already exist'));
    return false;
  } else {
    $('#add-prize-modal-error').hide();
  }
  $('#' + formId).submit();
} 

