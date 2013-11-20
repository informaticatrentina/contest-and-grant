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
});