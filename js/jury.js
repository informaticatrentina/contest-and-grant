$(document).ready(function() {
  $('#save-jury').click(function() {
    var juryAdminEmail = $('#jury-admin').val();
    var juryMemberEmail = $('#jury-member').val();
    if (juryAdminEmail == '') {
      $('#error').html(Yii.t('js', 'Jury admin email id is not empty'));
      return false;
    }
    if (juryMemberEmail == '') {
      $('#error').html(Yii.t('js', 'Jury member email id is not empty'));
      return false;
    }
    var juryEmailArr = juryAdminEmail.split(',');
    juryEmailArr.push(juryMemberEmail.split(','));
    for (email in juryEmailArr) {
      if (!emailValidation(juryEmailArr[email])) {
        $('#error').html(juryEmailArr[email] + ' ' + Yii.t('js', 'Email id is not valid'));
        return false;
      }
    }
  });
 $('.auto-submit-star').click( function(){
   var vote = $(this).text();
   var entryId = $(this).parents('.jury-vote').attr('entry-id');
   saveRating(vote, entryId);
 });
 
 $('.rating-cancel').click( function(){
   var entryId = $(this).parents('.jury-vote').attr('entry-id');
   saveRating('', entryId);
 });
 
 $('#back-top').hide();
 $(window).scroll(function () {
   if ($(this).scrollTop() > 100) {
    $('#back-top').fadeIn();
   } else {
    $('#back-top').fadeOut();
   }
  });
  $('#back-top a').click(function () {
    $('body,html').animate({scrollTop: 0}, 800);
   return false;
  });
});

function saveRating(vote, entryId) {
  $.ajax({
    type: 'GET',
    url: page.save_rating_url,
    dataType: 'json',
    data: {
      rating: vote,
      entry_id: entryId,
    },
    success: function(resp) {

    },
    error: function() {
      alert('Oops! something wrong'); 
    }
  });
}