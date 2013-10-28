var prevWeight = '';
var flag = true;
var postLimit = 20;
var entriesOffset = postLimit;
var nonWinnerEntryCount = postLimit;
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
  
  $(window).scroll(function() {  
    var totalEntry = $("#winnerCount").val(); 
    if (nonWinnerEntryCount >= totalEntry) {     
      return false;
    }
    if ($(window).scrollTop() == $(document).height() - $(window).height()) { 
      loadNonWinnerEntry();
    }
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

function loadNonWinnerEntry() { 
  var baseUrl = $('#baseUrl').val();
  var winnerWeight = $('#winnerWeight').val();
  var sNo = $("tr:last").find("td:first").html();
  $('#loading-image').show();
  if (flag) {
    flag = false;
    $.ajax({
      type: 'GET',
      url: addWinnerPageUrl,
      dataType: 'json',
      data: {
        offset: entriesOffset
      },
      success: function(resp) {
        $('#loading-image').hide();
        if (resp.success) {
          entriesOffset += postLimit;
          nonWinnerEntryCount += postLimit;
          var data = resp.data.non_winner_entry;          
          if (resp.data.winner_weight != '') {
            winnerWeight = winnerWeight + ',' + resp.data.winner_weight;
            $('#winnerWeight').val(winnerWeight);
          }          
          var html = '';
          for (key in data) {
            sNo++;
            html += '<tr>\n\
                      <td>'+ sNo +'</td>\n\
                      <td>\n\
                        <a href="' + baseUrl + 'contest/entries/'+ resp.data.contest_slug +'/'+ data[key].id +'">\n\
                          <img src="' + baseUrl + data[key].videoImagePath +'" alt="contest image" />\n\
                        </a>\n\
                      </td>\n\
                      <td><a href="' + baseUrl + 'contest/entries/'+ resp.data.contest_slug +'/'+ data[key].id+'">'+ data[key].title +'</a></td>\n\
                      <td>'+ data[key].author.name +'</td>\n\
                      <td id="'+ data[key].id +'" class="add-entry"><a href="#add-prize-modal"  data-toggle="modal">aggiungi</a></td>\n\
                    </tr>';
          }
          $('#winner-row').append(html);
          flag = true;
        } else {
          alert(resp.msg);
        }
      },
      error: function() {
        $('#loading-image').hide();
      }
    });
  }
  return false;
}
