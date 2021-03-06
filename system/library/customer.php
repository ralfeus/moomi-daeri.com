<?php
use system\engine\Registry;
use system\library\Config;
use system\library\Currency;

class Customer {
    /** @var Config */
    private $config;
	private $customer_id;
	private $firstname;
	private $lastname;
	private $nickname;
	private $email;
    /** @var Registry */
    private $registry;
	private $telephone;
	private $fax;
	private $newsletter;
	private $customer_group_id;
	private $address_id;
    /** @var Currency $baseCurrency */
    private $baseCurrency;
    private $balance;
    private $affiliate_id;

    /**
     * Customer constructor.
     * @param Registry $registry
     */
    public function __construct($registry, $clientIP) {
        $this->registry = $registry;
		$this->config = $registry->get('config');
		$this->db = $registry->get('db');
		$this->request = $registry->get('request');
		$this->session = $registry->get('session');
        ///$this->baseCurrency = new Currency($registry);

          if (isset($this->session->data['customer_id'])) {
			$customer_query = $this->db->query("SELECT * FROM customer WHERE customer_id = '" . (int)$this->session->data['customer_id'] . "' AND status = '1'");

			if ($customer_query->num_rows) {
				$this->customer_id = $customer_query->row['customer_id'];
				$this->firstname = $customer_query->row['firstname'];
				$this->lastname = $customer_query->row['lastname'];
				$this->nickname = $customer_query->row['nickname'];
				$this->email = $customer_query->row['email'];
				$this->telephone = $customer_query->row['telephone'];
				$this->fax = $customer_query->row['fax'];
				$this->newsletter = $customer_query->row['newsletter'];
				$this->customer_group_id = $customer_query->row['customer_group_id'];
				$this->address_id = $customer_query->row['address_id'];
                $this->balance = $customer_query->row['balance'];
                $this->affiliate_id = $customer_query->row['affiliate_id'];
                $this->getBaseCurrency()->set($customer_query->row['base_currency_code']);
//                $purgeCart = empty($customer_query->row['purge_cart']) ? 0 : $customer_query->row['purge_cart'];
//
//                if ($purgeCart)
//                {
//                    $this->session->data['cart'] = null;
//                    $this->db->query("
//                        UPDATE customer
//                        SET cart = NULL, purge_cart = 0
//                        WHERE customer_id = " . (int)$this->session->data['customer_id']
//                    );
//                }

      			$this->db->query("
                    UPDATE customer 
                    SET 
                        cart = :cart, 
                        wishlist = :wishlist, 
                        ip = :ip 
                    WHERE customer_id = :customerId
                ", [
                    ':cart' => $this->db->escape(isset($this->session->data['cart']) ? serialize($this->session->data['cart']) : ''),
                    ':wishlist' => $this->db->escape(isset($this->session->data['wishlist']) ? serialize($this->session->data['wishlist']) : ''),
                    ':ip' => $clientIP,
                    ':customerId' => (int)$this->session->data['customer_id']
                ]);

				$query = $this->db->query("
                    SELECT * 
                    FROM customer_ip 
                    WHERE customer_id = :customerId
                ", [ ':customerId' => (int)$this->session->data['customer_id']]);

				if (!$query->num_rows) {
					$this->db->query("INSERT INTO customer_ip SET customer_id = '" . (int)$this->session->data['customer_id'] . "', ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', date_added = NOW()");
				}
			} else {
				$this->logout();
			}
  		}
	}

  	public function login($email, $password, $override = false) {
		if ($override) {
			$customer_query = $this->db->query("SELECT * FROM customer where LOWER(email) = '" . $this->db->escape(strtolower($email)) . "' AND status = '1'");
		} elseif (!$this->config->get('config_customer_approval')) {
			$customer_query = $this->db->query("SELECT * FROM customer WHERE LOWER(email) = '" . $this->db->escape(strtolower($email)) . "' AND password = '" . $this->db->escape(md5($password)) . "' AND status = '1'");
		} else {
			$customer_query = $this->db->query("SELECT * FROM customer WHERE LOWER(email) = '" . $this->db->escape(strtolower($email)) . "' AND password = '" . $this->db->escape(md5($password)) . "' AND status = '1' AND approved = '1'");
		}

		if ($customer_query->num_rows) {
			$this->session->data['customer_id'] = $customer_query->row['customer_id'];

			if ($customer_query->row['cart'] && is_string($customer_query->row['cart'])) {
				$cart = unserialize($customer_query->row['cart']);

				foreach ($cart as $key => $value) {
					if (!array_key_exists($key, $this->session->data['cart'])) {
						$this->session->data['cart'][$key] = $value;
					} else {
						$this->session->data['cart'][$key] += $value;
					}
				}
			}

			if ($customer_query->row['wishlist'] && is_string($customer_query->row['wishlist'])) {
				if (!isset($this->session->data['wishlist'])) {
					$this->session->data['wishlist'] = array();
				}

				$wishlist = unserialize($customer_query->row['wishlist']);

				foreach ($wishlist as $product_id) {
					if (!in_array($product_id, $this->session->data['wishlist'])) {
						$this->session->data['wishlist'][] = $product_id;
					}
				}
			}

			$this->customer_id = $customer_query->row['customer_id'];
			$this->firstname = $customer_query->row['firstname'];
			$this->lastname = $customer_query->row['lastname'];
			$this->nickname = $customer_query->row['nickname'];
			$this->email = $customer_query->row['email'];
			$this->telephone = $customer_query->row['telephone'];
			$this->fax = $customer_query->row['fax'];
			$this->newsletter = $customer_query->row['newsletter'];
			$this->customer_group_id = $customer_query->row['customer_group_id'];
			$this->address_id = $customer_query->row['address_id'];
            $this->balance = $customer_query->row['balance'];
            $this->affiliate_id = $customer_query->row['affiliate_id'];
            $this->getBaseCurrency()->set($customer_query->row['base_currency_code']);

			$this->db->query("UPDATE customer SET ip = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "' WHERE customer_id = '" . (int)$customer_query->row['customer_id'] . "'");
			$this->setAffiliateId();
	  		return true;
    	} else {
      		return false;
    	}
  	}

	public function logout() {
		unset($this->session->data['customer_id']);

		$this->customer_id = '';
		$this->firstname = '';
		$this->lastname = '';
		$this->nickname = '';
		$this->email = '';
		$this->telephone = '';
		$this->fax = '';
		$this->newsletter = '';
		$this->customer_group_id = '';
		$this->address_id = '';
        $this->balance = 0;
        $this->baseCurrency = null;
  	}

  	public function isLogged() {
    	return $this->customer_id;
  	}

    public function getBaseCurrency() {
        if (empty($this->baseCurrency)) {
            $this->baseCurrency = new Currency($this->registry);
        }
        return $this->baseCurrency;
    }

    public function getBalance() {
        return $this->balance;
    }

    public function getId() {
    	return $this->customer_id;
  	}

  	public function getFirstName() {
		return $this->firstname;
  	}

  	public function getLastName() {
		return $this->lastname;
  	}

  	public function getNickName() {
		return $this->nickname;
  	}

  	public function getEmail() {
		return $this->email;
  	}

  	public function getTelephone() {
		return $this->telephone;
  	}

  	public function getFax() {
		return $this->fax;
  	}

  	public function getNewsletter() {
		return $this->newsletter;
  	}

    /**
     * @return int
     */
    public function getCustomerGroupId() {
        if ($this->isLogged()) {
            return $this->customer_group_id;
        } else {
            return $this->config->get('config_customer_group_id');
        }
  	}

	public function getAffiliateId() {

		return $this->affiliate_id;

	}

	public function setAffiliateId() {

		if (!empty($this->customer_id) && !$this->affiliate_id) {
			if (isset($this->request->cookie['tracking'])) {
				$this->registry->get('load')->model('affiliate/affiliate');
				$affiliate_info = $this->registry->get('model_affiliate_affiliate')->getAffiliateByCode($this->request->cookie['tracking']);
				if ($affiliate_info) {
					$this->affiliate_id = $affiliate_info['affiliate_id'];
				}
				$this->db->query("UPDATE customer SET affiliate_id = '" . (int)$this->affiliate_id . "' WHERE customer_id = '" . (int)$this->customer_id . "'");
			}
		}

	}



  	public function getAddressId() {
		return $this->address_id;
  	}

  	public function getRewardPoints() {
		$query = $this->db->query("SELECT SUM(points) AS total FROM customer_reward WHERE customer_id = '" . (int)$this->customer_id . "'");

		return $query->row['total'];
  	}

    public function setBalance($value)
    {
        $this->db->query('
            UPDATE ' . DB_PREFIX . 'customer
            SET balance = ' . (float)$value . '
            WHERE customer_id = ' . $this->customer_id
        );
    }
}