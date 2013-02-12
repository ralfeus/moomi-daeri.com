<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 14.1.13
 * Time: 20:43
 * To change this template use File | Settings | File Templates.
 */
require_once('auditConstants.php');
class Audit extends OpenCartBase implements ILibrary
{
    private static $instance;

    private function addEntry($userId, $userType, $eventId, $data)
    {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "audit
            SET
                data = '" . json_encode($data) . "',
                date_added = NOW(),
                event_id = " . (int)$eventId . ",
                user_id = " . (int)$userId . ",
                user_ip = '" . $_SERVER['REMOTE_ADDR'] . "',
                user_type = '" . $this->db->escape($userType) . "'
        ");
    }

    public function addAdminEntry($eventId, $data)
    {
        $userId = $this->user->isLogged() ? $this->user->getId() : 0;
        $this->addEntry($userId, 'admin', $eventId, $data);
    }

    public function addUserEntry($eventId, $data)
    {
        $userId = $this->customer->isLogged() ? $this->customer->getId() : 0;
        $this->addEntry($userId, 'user', $eventId, $data);
    }

    public static function getInstance($registry)
    {
        if (empty(Audit::$instance))
            Audit::$instance = new Audit($registry);
        return Audit::$instance;
    }
}