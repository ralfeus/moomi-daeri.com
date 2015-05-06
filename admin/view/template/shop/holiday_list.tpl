<?php echo $header; ?>
<script type="text/javascript">
  
  var token = '<?= $token ?>';
  /*function approveVotes() {
    var length = $("input[class^='checkboxVote']:checked").length;
    if(length > 0) {
      var vote_ids = new Array();
      $("input[class^='checkboxVote']:checked").each(function(index, item) {
        vote_ids.push($(item).val());
      });

      var url = "<?php echo $this->url->link('gallery/admin/approveVotes&token='. $token); ?>";
      var postdata = {
        arr : JSON.stringify(vote_ids)
      }
      
      $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          url = "<?php echo $this->url->link('gallery/admin/adminVote&token='. $token); ?>";
          window.location = url;
        }
        else {
          alert('Error. Please try later');
        }
      });
    }
  }*/

  function addHoliday() {

    var start = $('#newHolidayStart').val();
    var end = $('#newHolidayEnd').val();
    var name = $('#newHolidayName').val();
    var postdata = {
      'start' : start,
      'end' : end,
      'name' : name
    };
    var url = "<?= $this->url->link('shop/admin/addHoliday', 'token='. $token, 'SSL') ?>";
    $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          var htmlRow = '';
          htmlRow += '<tr id="' + response['holiday_id'] + '">';
          htmlRow += '  <td>';
          htmlRow += '    ' + response['start'];
          htmlRow += '  </td>';
          htmlRow += '  <td>';
          htmlRow += '    ' + response['end'];
          htmlRow += '  </td>';
          htmlRow += '  <td>';
          htmlRow += '    ' + response['name'];
          htmlRow += '  </td>';
          htmlRow += '  <td style="text-align: center;"><a href="" style="text-decoration: none; color: red; font-weight: bold;" title="delete" onclick="deleteHoliday(';
          htmlRow += response['holiday_id'];
          htmlRow += '); return false;">X</a>';
          htmlRow += '  </td>';
          htmlRow += '</tr>';
          $('#holidayTable tr:last').after(htmlRow);
        }
    });
  }

  function deleteHoliday(holiday_id) {
    var postdata = {
      'holiday_id' : holiday_id
    };

    var url = "<?php echo $this->url->link('shop/admin/deleteHoliday&token='. $token); ?>";

    $.post(url, postdata, function(response) {
        response = $.parseJSON(response);
        if(response['success']) {
          $('#' + holiday_id).remove();
        }
    });

  }

  $(document).ready(function() {

    $("#newHolidayStart").datepicker({dateFormat: 'yy-mm-dd'});
    $("#newHolidayEnd").datepicker({dateFormat: 'yy-mm-dd'});

  });

</script>
<div id="content">
  <div class="breadcrumb">
    <!--<?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>-->
  </div>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/product.png" alt="" /> <?php echo $heading_title_holiday; ?></h1>

    </div>
    <div class="content">
      <div class="holiday">
        <table id="holidayTable" border=1>
          <tr>
            <th style="width: 200px; text-align: left;"> Start </th>
            <th style="width: 200px; text-align: left;"> End </th>
            <th style="width: 200px; text-align: left;"> Name </th>
            <th style="width: 100px; text-align: center;"> Action </th>
          </tr>
          <?php foreach ($holidays as $holiday) {
            echo '
            <tr id="' . $holiday["holiday_id"] . '">
              <td> ' . $holiday["start"] . ' </td>
              <td> ' . $holiday["end"] . ' </td>
              <td> ' . $holiday["name"] . ' </td>
              <td style="text-align: center;"><a href="" style="text-decoration: none; color: red; font-weight: bold;" title="delete" onclick="deleteHoliday(' . $holiday["holiday_id"] . '); return false;">X</a></td>
            </tr>';
          } ?>
          
        </table>
      </div>
      <br />
      <br />
      <br />
      <div>
        <table>
          <tr>
            <td> Start: <input id="newHolidayStart" type="text" readonly="readonly" /> </td>
            <td> End: <input id="newHolidayEnd" type="text" readonly="readonly" /> </td>
            <td> Name: <input id="newHolidayName" type="text" /> </td>
            <td> <button onclick="addHoliday()">Add new holidays</button> </td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>