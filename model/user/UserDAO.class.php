<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 01.07.2016
 * Time: 13:54
 */
namespace model\user;
use model\DAO;

class UserDAO extends DAO {
    /**
     * @param int $userId
     * @return array
     */
    public function getUserById($userId) {
        $query = $this->getDb()->query("SELECT * FROM `user` WHERE user_id = :userId", [ ':userId' => $userId ]);

        return $query->row;
    }
}
