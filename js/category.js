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
    $.fancybox.open({
      href: "#add-entry-in-category",
      padding: 0,
      onClosed: function() {
        $("#error").hide();
      }
    });
  });
  
  $('#add-entry-button').click(function() {
    var prizeWeightArr =  Array();
    var prize = $('#prize').val();
    var weight = $('#weight').val();
    var prizeWeight = $('#winnerWeight').val();
    if (prizeWeight != '') {
      prizeWeightArr = prizeWeight.split(",");
    }
    if (prize == '') {      
      $('#error').show();      
      $('#error').html(Yii.t('contest','Please enter prize'));
      return false;
    }
    if (weight == '') {
      $('#error').show();
      $('#error').html(Yii.t('contest','Please enter prize weight'));
      return false;
    } else if (!(/^[0-9-+]+$/).test($.trim(weight))) {
      $('#error').show();
      $('#error').html(Yii.t('contest','Prize weight should be numeric'));
      return false;
    } else if (prizeWeightArr.indexOf(weight) > -1) {
      $('#error').show();
      $('#error').html(Yii.t('contest','Prize weight already exist'));
      return false;
    }
    $('#add-entry-in-category').submit();
  });
});