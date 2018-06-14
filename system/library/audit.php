<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 14.1.13
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */
namespace system\library;
use system\engine\OpenCartBase;
use system\engine\Registry;

require_once('auditConstants.php');

class Audit extends OpenCartBase implements ILibrary {
    /** @var  Audit */
    private static $instance;

    private function addEntry($userId, $userType, $eventId, $data) {
        $this->getDb()->query("
            INSERT INTO audit
                (data, date_added, event_id, user_id, user_ip, user_type)
                VALUES(:data, NOW(), :eventId, :userId, :userIp, :userType)
            ", [
            ':data' => json_encode($data),
            ':eventId' => $eventId,
            ':userId' => $userId,
            ':userIp' => $_SERVER['REMOTE_ADDR'],
            ':userType' => $userType
        ], false, true
        );
    }

    public function addAdminEntry($adminId, $eventId, $data) {
        $this->addEntry($adminId, 'admin', $eventId, $data);
    }

    public function addUserEntry($userId, $eventId, $data) {
        $this->addEntry($userId, 'user', $eventId, $data);
    }

    /**
     * @param Registry $registry
     * @return Audit
     */
    public static function getInstance($registry) {
        if (empty(Audit::$instance))
            Audit::$instance = new Audit($registry);
        return Audit::$instance;
    }
}
