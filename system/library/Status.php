<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 2.8.12
 * Time: 22:39
 * To change this template use File | Settings | File Templates.
 */
class Status extends LibraryClass
{
    private static $instance;

    public static function getInstance($registry)
    {
        if (empty(Status::$instance))
            Status::$instance = new Status($registry);
        return Status::$instance;
    }

    public static function getStatus($statusId, $languageId, $isPublic = false)
    {
//        Status::$instance->log->write("$statusId, $languageId, $isPublic");
        $fieldName = $isPublic ? "public_name" : "name";
        while (true)
        {
            $sql = "
                SELECT $fieldName
                FROM " . DB_PREFIX . "statuses
                WHERE group_id << 16 | status_id = " . (int)$statusId . " AND language_id = " . (int)$languageId
            ;
//            Status::$instance->log->write(print_r($sql, true));
            $query = Status::$instance->db->query($sql);
            if ($query->num_rows)
                return $query->row[$fieldName];
            elseif ($languageId != 2)
                $languageId = 2;
            else
                return "";
        }
    }

    public static function getStatuses($statusGroupId, $languageId)
    {
        while (true)
        {
            $query = Status::$instance->db->query("
                SELECT group_id << 16 | status_id as status_id, name
                FROM " . DB_PREFIX . "statuses
                WHERE group_id = " . (int)$statusGroupId . " AND language_id = " . (int)$languageId
            );
//            Status::$instance->log->write(print_r($query->rows, true));
            if ($query->num_rows)
                return $query->rows;
            elseif ($languageId != 2)
                $languageId = 2;
            else
                return array();
        }
    }

    protected function __construct($registry)
    {
        parent::__construct($registry);
    }
}
