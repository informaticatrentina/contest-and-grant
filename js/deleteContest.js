$(document).ready(function() {
  $('.deleteContest').click(function() { 
    var response =  confirm(Yii.t('js', "Are you sure you want to delete this Contest ?"));
    if (!response) {
      return false;
    }  
  });
});  