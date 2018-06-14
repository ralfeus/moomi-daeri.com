<?php
namespace system\library;
use system\engine\Registry;

final class User {
    private $user_id;
    private $username;
    private $permission = array();

    /* [webme] deny order deletions by specified user_group - begin */
    private $usergroup_id;

    /* [webme] deny order deletions by specified user_group - end */

    public static function getInstance() {

    }

    /**
     * User constructor.
     * @param Registry $registry
     */
    public function __construct($registry) {
        $this->db = $registry->get('db');
        $this->request = $registry->get('request');
        $this->session = $registry->get('session');

        if (isset($this->session->data['user_id'])) {
            $user_query = $this->db->query("SELECT * FROM user WHERE user_id = '" . (int)$this->session->data['user_id'] . "' AND STATUS = '1'");

            if ($user_query->num_rows) {
                $this->user_id = $user_query->row['user_id'];
                $this->username = $user_query->row['username'];

                /* [webme] deny order deletions by specified user_group - begin */
                $this->usergroup_id = $user_query->row['user_group_id'];
                /* [webme] deny order deletions by specified user_group - end */

                $this->db->query("
					UPDATE user 
					SET ip = :ip
					WHERE user_id = :userId
					", [
                    ':ip' => $this->request->server['REMOTE_ADDR'],
                    ':userId' => $this->session->data['user_id']
                ], false, true
                );

                $user_group_query = $this->db->query("SELECT permission FROM user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

                $permissions = unserialize($user_group_query->row['permission']);

                if (is_array($permissions)) {
                    foreach ($permissions as $key => $value) {
                        $this->permission[$key] = $value;
                    }
                }
            } else {
                $this->logout();
            }
        }
    }

    public function login($username, $password) {
        $user_query = $this->db->query("SELECT * FROM user WHERE username = '" . $this->db->escape($username) . "' AND password = '" . $this->db->escape(md5($password)) . "' AND status = '1'");

        if ($user_query->num_rows) {
            $this->session->data['user_id'] = $user_query->row['user_id'];

            $this->user_id = $user_query->row['user_id'];
            $this->username = $user_query->row['username'];

            /* [webme] deny order deletions by specified user_group - begin */
            $this->usergroup_id = $user_query->row['user_group_id'];
            /* [webme] deny order deletions by specified user_group - end */

            $user_group_query = $this->db->query("SELECT permission FROM user_group WHERE user_group_id = '" . (int)$user_query->row['user_group_id'] . "'");

            $permissions = unserialize($user_group_query->row['permission']);

            if (is_array($permissions)) {
                foreach ($permissions as $key => $value) {
                    $this->permission[$key] = $value;
                }
            }

            return true;
        } else {
            return false;
        }
    }

    public function logout() {
        unset($this->session->data['user_id']);

        $this->user_id = '';
        $this->username = '';

        session_destroy();
    }

    public function hasPermission($key, $value) {
        if (isset($this->permission[$key])) {
            return in_array($value, $this->permission[$key]);
        } else {
            return false;
        }
    }

    public function isLogged() {
        return $this->user_id;
    }

    public function getId() {
        return $this->user_id;
    }

    public function getUserName() {
        return $this->username;
    }

    /* [webme] deny order deletions by specified user_group - begin */
    public function getUsergroupId() {
        return $this->usergroup_id;
    }
    /* [webme] deny order deletions by specified user_group - end */
}