$(document).ready(function() {
  $('#add-new-category').click(function() {
    $('#add-new-category').hide();
    $('#add-category').show();
  });
  $(".add-entry").click(function() {
    var entryId = $(this).attr('id');
    var tag = $(this).attr('tag');
    var categoryName = $(this).attr('category');
    if (entryId != '') {
      $('#add-entry-id').attr('value',entryId);
      $('#add-entry-tag').attr('value',tag);
      $('#add-entry-category').attr('value',categoryName);
    }    
    openFancyBox('add-entry-in-category');
  });
  
  $('#add-entry-button').click(function(e) {
    e.preventDefault();
    submitFancyBox('add-entry-in-category');    
  });  
  
  $('.update-winner').click(function(){ 
    var winnerId = $(this).attr('id');
    var winnerTag = $(this).attr('tag');
    var winnerCategoryName = $(this).attr('category');
    var prize = $(this).attr('prize');
    var weight = $(this).attr('weight');
    if (winnerId != '') {
      $('#update-winner-id').attr('value',winnerId);
      $('#update-winner-tag').attr('value',winnerTag);
      $('#update-winner-category').attr('value',winnerCategoryName);
      $('#prize').attr('value', prize);
      $('#weight').attr('value', weight);
    }
    openFancyBox('update-winner-form');
  });
  
  $('#update-winner-button').click(function(e) {
    e.preventDefault();
    submitFancyBox('update-winner-form');     
   });        
});

function openFancyBox(id) {
  $.fancybox.open({
    href: "#" + id,
    padding: 0,
    onClosed: function() {
      $("#error").hide();
    }
  });
}

function submitFancyBox(formId) {
  var prizeWeightArr = Array();
  var prize = $('#prize').val();
  var weight = $('#weight').val();
  var prizeWeight = $('#winnerWeight').val();
  if (prizeWeight != '') {
    prizeWeightArr = prizeWeight.split(",");
  }
  if (prize == '') {
    $('#error').show();
    $('#error').html(Yii.t('contest', 'Please enter prize'));
    return false;
  }
  if (weight == '') {
    $('#error').show();
    $('#error').html(Yii.t('contest', 'Please enter prize weight'));
    return false;
  } else if (!(/^[0-9-+]+$/).test($.trim(weight))) {
    $('#error').show();
    $('#error').html(Yii.t('contest', 'Prize weight should be numeric'));
    return false;
  } else if (prizeWeightArr.indexOf(weight) > -1) {
    $('#error').show();
    $('#error').html(Yii.t('contest', 'Prize weight already exist'));
    return false;
  }
  $('#' + formId).submit();
} 