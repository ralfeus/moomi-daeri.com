<?php
class ModelShopGeneral extends Model {
	public function getHolidays($data) {}

	public function getAllHolidaysForCalendar() {

		$query = "SELECT * FROM shop_holiday";
		$result = $this->getDb()->query($query);
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
        $query = "SELECT * FROM `customer` WHERE customer_id='" . $customer_id . "' AND customer_group_id=6";
		$result = $this->getDb()->query($query);
		if(count($result->rows) >= 1){
			return true;
		}
		else {
			return false;
		}
	}

	public function getPage($page_id = null, $lang = 2) { // Default English
		$children = array();
		if($page_id == null) {
			$query = "
			    SELECT i.information_id AS page_id, title AS page_title, description AS page_content
			    FROM
			        information AS i
			        JOIN information_description AS id ON i.information_id = id.information_id
			    WHERE
			        parent_node_id IS NULL OR parent_node_id = 0
			        AND language_id = " . (int)$lang
            ;
		}
		else {
			$query = "
			    SELECT
                    i.parent_node_id AS parent_page_id,
                    parent_id.title AS parent_page_title,
                    id.title AS page_title,
                    id.description AS page_content
                FROM
                    information AS i
                    JOIN information_description AS id ON i.information_id = id.information_id
                    LEFT JOIN information AS parent ON i.parent_node_id = parent.information_id
                    LEFT JOIN information_description AS parent_id ON parent.information_id = parent_id.information_id AND id.language_id = parent_id.language_id
                WHERE
                    i.information_id = " . (int)$page_id . "
                    AND id.language_id = " . (int)$lang
            ;
		}

		$pages = $this->getDb()->query($query)->rows;

		if($page_id != null) {
			$query = "
			    SELECT i.information_id AS page_id, title AS page_title
			    FROM
			        information AS i
			        JOIN information_description AS id ON i.information_id = id.information_id
                WHERE parent_node_id = " . (int)$page_id . " AND language_id = " . (int)$lang
            ;
			$children = $this->getDb()->query($query)->rows;
		}

		return $result = array('pages' => $pages, "children" => $children);
	}

	public function getAction($data) {
		$query = "SELECT * FROM action WHERE customer_group_id=".(int)$data['customer_group_id']." AND '".$data['current_date']."' BETWEEN start_date AND finish_date LIMIT 1";
		$result = $this->getDb()->query($query);
		return $result->rows;
	}

	public function getOrderProduct($product_id) {
		$query = "SELECT * FROM order_product WHERE product_id=".(int)$product_id;
		$result = $this->getDb()->query($query);
		return $result->rows[0];
	}
}
?>