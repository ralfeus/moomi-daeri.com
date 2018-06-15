<?php
namespace system\library;

use system\engine\Registry;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 2.8.12
 * Time: 22:39
 * To change this template use File | Settings | File Templates.
 */
class Status extends LibraryClass {
    private static $instance;

    /**
     * @param Registry $registry
     * @return Status
     */
    public static function getInstance($registry) {
        if (empty(Status::$instance))
            Status::$instance = new Status($registry);
        return Status::$instance;
    }

    public static function getStatus($statusId, $languageId, $isPublic = false) {
//        status::$instance->log->write("$statusId, $languageId, $isPublic");
        $fieldName = $isPublic ? "public_name" : "name";
        while (true) {
            $sql = "
                SELECT $fieldName
                FROM statuses
                WHERE group_id << 16 | status_id = " . (int)$statusId . " AND language_id = " . (int)$languageId;

            $query = Status::$instance->db->query($sql);

            if ($query->num_rows)
                return $query->row[$fieldName];
            elseif ($languageId != 2)
                $languageId = 2;
            else
                return "";
        }
    }

    public static function getStatuses($statusGroupId, $languageId, $public = false) {
        while (true) {
            $sql = "
                SELECT group_id << 16 | status_id as status_id, " . ($public ? 'public_name' : 'name') . " AS name
                FROM statuses
                WHERE group_id = " . (int)$statusGroupId . " AND language_id = " . (int)$languageId . "
                ORDER BY name";
            $query = Status::$instance->db->query($sql);

            if ($query->num_rows) {
                $result = array();
                foreach ($query->rows as $row) {
                    $result[$row['status_id']] = $row['name'];
                }
                return $result;
            } elseif ($languageId != 2) {
                $languageId = 2; /// English
            } else
                return array();
        }
    }

    protected function __construct($registry) {
        parent::__construct($registry);
    }
}
