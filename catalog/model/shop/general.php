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
		if(count($result->rows) >= 2){
			return true;
		}
		else {
			return false;
		}
	}

	public function getPage($page_id = null, $lang = 'en') {
		$pages = array();
		$children = array();
		if($page_id == null) {
			$query = "SELECT page_id, page_name_" . $lang . " AS page_title, page_content_" . $lang . " AS page_content FROM page WHERE parent_page_id IS NULL OR parent_page_id = 0";
		}
		else {
			$query = "SELECT p.parent_page_id, page.page_name_" . $lang . " AS parent_page_title, p.page_name_" . $lang . " AS page_title, p.page_content_" . $lang . " AS page_content FROM page AS p LEFT JOIN page ON p.parent_page_id = page.page_id WHERE p.page_id = " . $page_id;
		}

		$pages = $this->db->query($query)->rows;

		if($page_id != null) {
			$query = "SELECT page_id, page_name_" . $lang . " AS page_title FROM page WHERE parent_page_id = " . $page_id;
			$children = $this->db->query($query)->rows;
		}

		return $result = array('pages' => $pages, "children" => $children);
	}

	public function getAction($data) {
		$query = "SELECT * FROM " . DB_PREFIX . "action WHERE customer_group_id=".(int)$data['customer_group_id']." AND '".$data['current_date']."' BETWEEN start_date AND finish_date LIMIT 1";
		$result = $this->db->query($query);
		return $result->rows;
	}
}
?>