var prevWeight = '';
var catName = '';
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
    prevWeight = weight;
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
   
   
  $('.delete-winner').click(function(e) {
    e.preventDefault();
    var response =  confirm(Yii.t('js', "Are you sure you want to remove this winner ?"));
    if (!response) {
      return false;
    }  
    var winnerDelId = $(this).attr('id');
    var winnerDelTag = $(this).attr('tag');
    if (winnerDelId != '') {
      $('#delete-winner-id').attr('value', winnerDelId);
      $('#delete-winner-tag').attr('value', winnerDelTag);
    }
    $('#delete-winner-form').submit();
  });
  
  $(".updatecategory").click(function() {
    var categoryId = $(this).attr('categoryId');
    var categoryName = $(this).attr('categoryName');
    if (categoryId != '') {
      $('#categoryId').attr('value', categoryId);
      $('#categoryName').attr('value', categoryName);
    }
    openFancyBox('update-category');
  });
  $('#update-category-button').click(function(e) {
    e.preventDefault();
    var catName = $('#categoryName').val();
    if ($.trim(catName) == '') {
      $('#error').show();
      $('#error').html(Yii.t('js', 'Category name can not be empty'));
      return false;
    }
    $('#update-category').submit();
  }); 
  
  $('.delete-category').click(function() { 
    var response =  confirm(Yii.t('js', "Are you sure you want to delete this Category ?"));
    if (!response) {
      return false;
    }  
    
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
  if ($.trim(prize) == '') {
    $('#error').show();
    $('#error').html(Yii.t('js', 'Please enter prize'));
    return false;
  }
  if ($.trim(weight) == '') {
    $('#error').show();
    $('#error').html(Yii.t('js', 'Please enter prize weight'));
    return false;
  } else if (!(/^[0-9-+]+$/).test($.trim(weight))) {
    $('#error').show();
    $('#error').html(Yii.t('js', 'Prize weight should be numeric'));
    return false;
  } else if (prizeWeightArr.indexOf(weight) > -1 && weight != prevWeight) { 
    $('#error').show();
    $('#error').html(Yii.t('js', 'Prize weight already exist'));
    return false;
  }
  $('#' + formId).submit();
} 