<?php
class ControllerGalleryAdmin extends Controller {

	public function index() {

		$this->load->language('gallery/admin');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('gallery/photo');

		$this->getList();

	}

	private function getList() {

		$this->data['breadcrumbs'] = array();

 		$this->data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('text_home'),
				'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => false
 		);

 		$this->data['breadcrumbs'][] = array(
     		'text'      => $this->language->get('heading_title'),
				'href'      => $this->url->link('gallery/admin', 'token=' . $this->session->data['token'], 'SSL'),
    		'separator' => ' :: '
 		);

		$this->data['reviews'] = array();

		/*$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);*/

		$totalPhotos = $this->model_gallery_photo->getTotalPhotos();

		$allPhotos = $this->model_gallery_photo->getPhotos();

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_number_of_credits'] = $this->language->get('text_number_of_credits');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['photos'] = $allPhotos;

		$this->data['token'] = $this->session->data['token'];
		$this->data['column_photo'] = $this->language->get('column_photo');
		$this->data['column_user_nickname'] = $this->language->get('column_user_nickname');
		$this->data['column_photo_name'] = $this->language->get('column_photo_name');
		$this->data['column_photo_description'] = $this->language->get('column_photo_description');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_approve'] = $this->language->get('button_approve');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->template = 'gallery/photo_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function approvePhotos() {
		$jsonArr = $_POST['arr'];
		$credits = $_POST['credits'];
		$photo_ids = json_decode($jsonArr);

		$this->load->model('gallery/photo');
		$this->load->language('gallery/admin');

		$desc = $this->language->get('text_gallery_transaction');

		$this->model_gallery_photo->approvePhotos($photo_ids, $credits, $desc);

		$response['success'] = true;
		$this->response->setOutput(print_r(json_encode($response), true));
	}

	public function removePhotos() {
		$jsonArr = $_POST['arr'];
		$photo_ids = json_decode($jsonArr);
		$today = date("Y-m-d");
		$query = "SELECT * FROM gallery_photo WHERE photo_id IN (" . implode(",", $photo_ids) . ")";
		$result = $this->db->query($query);
		foreach ($result->rows as $row) {
			$pathToUnlink = DIR_IMAGE . $row["path"];
			unlink($pathToUnlink);
			$query = "DELETE FROM gallery_photo WHERE photo_id = '" . $row['photo_id'] . "'";
			$this->db->query($query);
		}
		$response['success'] = true;
		$this->response->setOutput(print_r(json_encode($response), true));
	}

	public function adminVote() {
		$this->load->language('gallery/admin');

		$this->data['breadcrumbs'] = array();

 		$this->data['breadcrumbs'][] = array(
   		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
  		'separator' => false
 		);

 		$this->data['breadcrumbs'][] = array(
   		'text'      => $this->language->get('heading_title_vote'),
			'href'      => $this->url->link('gallery/admin/adminVote', 'token=' . $this->session->data['token'], 'SSL'),
  		'separator' => ' :: '
 		);

		$this->document->setTitle($this->language->get('heading_title_vote'));
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['heading_title_voting'] = $this->language->get('heading_title_vote');

		$this->data['token'] = $this->session->data['token'];
		$this->data['column_photo'] = $this->language->get('column_photo');
		$this->data['column_photo_vote'] = $this->language->get('column_photo_vote');
		$this->data['column_photo_comment'] = $this->language->get('column_photo_comment');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_date_added'] = $this->language->get('column_date_added');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_approve'] = $this->language->get('button_approve');
		$this->data['button_delete'] = $this->language->get('button_delete');

		$this->data['votes'] = array();
		$this->load->model('gallery/photo');
		$this->data['votes'] = $this->model_gallery_photo->getVoteList();

		$this->template = 'gallery/voting_list.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());

	}

	public function removeVotes() {
		$jsonArr = $_POST['arr'];
		$vote_ids = json_decode($jsonArr);
		$today = date("Y-m-d");
		$query = "DELETE FROM gallery_photo_voting WHERE vote_id IN (" . implode(",", $vote_ids) . ")";
		$result = $this->db->query($query);
		$response['success'] = true;
		$this->response->setOutput(print_r(json_encode($response), true));
	}

	public function approveVotes() {
		$jsonArr = $_POST['arr'];
		$vote_ids = json_decode($jsonArr);
		$today = date("Y-m-d");
		$query = "UPDATE gallery_photo_voting SET approved_at ='" . $today . "' WHERE vote_id IN (" . implode(",", $vote_ids) . ")";
		$this->db->query($query);
		$response['success'] = true;
		$this->response->setOutput(print_r(json_encode($response), true));
	}

}

?>