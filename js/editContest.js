$(document).ready(function() {
   $('#home').find("a").removeClass('candgselected');
   $('#createContest').find("a").addClass('candgselected');
   $('#create').click(function() {
        if ($('#startDate').val() == '') {
            $('#error').html(Yii.t('js', "Please enter start date")).css('color', 'red');
            return false;
        } else if (!isDate($('#startDate').val())) {
            $('#error').html(Yii.t('js', "Please enter a valid date")).css('color', 'red');
            return false;
        }
        
        if ($('#endDate').val() == '') {
            $('#error').html(Yii.t('js', "Please enter end date")).css('color', 'red');
            return false;
        } else if (!isDate($('#endDate').val())) {
            $('#error').html(Yii.t('js', "Please enter a valid date")).css('color', 'red');
            return false;
        }
        if ($('#image').val() == '') {
            $('#error').html(Yii.t('js', "Please upload an image")).css('color', 'red');
            return false;
        }
        if ($('#contestDescription').val() == '') {
            $('#error').html(Yii.t('js', "Please enter Content Description")).css('color', 'red');
            return false;
        }
    });
});

function isDate(txtDate) {
    var currVal = txtDate;
    if (currVal == '')
        return false;

    //Declare Regex  
    var rxDatePattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
    var dtArray = currVal.match(rxDatePattern); // is format OK?

    if (dtArray == null)
        return false;

    //Checks for mm/dd/yyyy format.
    dtMonth = dtArray[1];
    dtDay = dtArray[3];
    dtYear = dtArray[5];

    if (dtMonth < 1 || dtMonth > 12)
        return false;
    else if (dtDay < 1 || dtDay > 31)
        return false;
    else if ((dtMonth == 4 || dtMonth == 6 || dtMonth == 9 || dtMonth == 11) && dtDay == 31)
        return false;
    else if (dtMonth == 2)
    {
        var isleap = (dtYear % 4 == 0 && (dtYear % 100 != 0 || dtYear % 400 == 0));
        if (dtDay > 29 || (dtDay == 29 && !isleap))
            return false;
    }
    return true;
}