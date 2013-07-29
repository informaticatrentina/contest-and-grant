var postLimit = 20;
var entriesOffset = postLimit;
var flag = true;
var count = postLimit;

$(document).ready(function() {
  $("#winner-row").masonry({
    itemSelector: '#winner-row',
    isAnimated: true,
    columnWidth: function() {
      var width = $(window).width();
      var col = 200;
      if (width < 1200 && width >= 980) {
        col = 160;
      }
      else if (width < 980 && width >= 768) {
        col = 8;
      }
     return col;
    }
  });
  $(window).scroll(function() {
    var baseUrl = $('#baseUrl').val();
    var totalEntry = $("#winnerCount").val(); 
    if (count >= totalEntry) {
      return false;
    }
    var sNo = $("tr:last").find("td:first").html();
    if ($(window).scrollTop() == $(document).height() - $(window).height()) {
      $('#loading-image').show();
      var pagrUrl = $('#winnerPageUrl').val();
      var imageUrl = '';
      if (flag) {
        flag = false;
        $.ajax({
          type: 'GET',
          url: pagrUrl,
          dataType: 'json',
          data: {
            offset: entriesOffset
          },
          success: function(resp) {
            $('#loading-image').hide();
            if (resp.success) {
              entriesOffset += postLimit;
              var data = resp.data.entry;
              var category = resp.data.category;
              var html = '';
              count +=  postLimit;
              for (key in data) {                
                sNo++;
                html += '<tr>';
                html += '<td>' + sNo + '</td>';
                imageUrl = baseUrl + data[key].image;
                html += '<td><img src="'+ imageUrl +'" width="100" height="500" alt="contest image" /></td>';
                html += '<td>' + data[key].title + '</td>';
                html += '<td>' + data[key].author + '</td>';
                html += "<td tag='" + data[key].tag + "' id='" + data[key].id + "' category= '" + category + "' class='add-entry'>\n\
                          <a href='#add-entry-in-category'>aggiungi</a>\n\
                        </td>";
              }
              $('#winner-row').append(html).masonry('reload');
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
  });
});