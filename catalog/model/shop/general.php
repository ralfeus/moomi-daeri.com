<?php
class ModelShopGeneral extends Model {
	public function getHolidays($data) {
		
		$this->db->query("SELECT * FROM " . DB_PREFIX . "shop_holiday");

    $holiday = array();
    foreach ($query->rows as $row) {
      
    }
	
	}

	public function getAllHolidaysForCalendar() {

		$query = "SELECT * FROM " . DB_PREFIX . "shop_holiday";
		$result = $this->db->query($query);
		$holidays = $result->rows;

		$arrAllHolidays = array();

		foreach ($holidays as $holiday) {
		  $phpStart = strtotime($holiday['start']);
		  $phpEnd = strtotime($holiday['end']);
		  $numberOfDays = ($phpEnd - $phpStart) / (60 * 60 * 24);
		  $arrHolidays = array();
		  array_push($arrHolidays, date("d-m-Y", $phpStart));
		  array_push($arrAllHolidays, array(date("Y", $phpStart), date("m", $phpStart), date("d", $phpStart), $holiday['name']));
		  if($numberOfDays > 1) {
		    for ($i = 1; $i <= $numberOfDays ; $i++) {
		      $tomorrow = mktime(0, 0, 0, date("m", $phpStart), date("d", $phpStart)+$i, date("y", $phpStart));
		      array_push($arrHolidays, date("d-m-Y", $tomorrow));
		      array_push($arrAllHolidays, array(date("Y", $tomorrow), date("m", $tomorrow), date("d", $tomorrow), $holiday['name']));
		    }
		  }
		  else {
		    array_push($arrHolidays, date("d-m-Y", $phpEnd));
		    array_push($arrAllHolidays, array(date("Y", $phpEnd), date("m", $phpEnd), date("d", $phpEnd), $holiday['name']));
		  }
		  //echo $holiday['start'] . "   -    " . $holiday['end'] . "     " . print_r($arrHolidays, true) . "<br />";
		}

		$response['success'] = true;
		$response['holidays'] = $arrAllHolidays;

		return json_encode($response);
	}

	public function isVip($customer_id) {
		$today = date("Y-m-d");
		$lastMonth = date("Y-m-d", mktime(0, 0, 0, date("m")-1, date("d"),   date("Y")));
		$query = "SELECT * FROM `" . DB_PREFIX . "order` WHERE customer_id='" . $customer_id . "' AND date_added BETWEEN '" . $lastMonth . "' AND '" . $today . "'";
		$result = $this->db->query($query);
		if(count($result->rows) >= 1){
			return true;
		}
		else {
			return false;
		}
	}
}
?>