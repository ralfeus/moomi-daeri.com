<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 19:49
 * To change this template use File | Settings | File Templates.
 */
class Messaging extends LibraryClass
{
    private static $instance;
    protected function __construct($registry)
    {
        parent::__construct($registry);
    }

    public static function getInstance($registry)
    {
        if (!isset($instance))
            Messaging::$instance = new Messaging($registry);
        return Messaging::$instance;
    }

    public static function getSystemMessage($messageId)
    {
        $query = Messaging::$instance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "messages
            WHERE message_id = " . (int)$messageId
        );
        if ($query->num_rows)
            return array(
                'messageId' => $query->row['message_id'],
                'messageTypeId' => $query->row['message_type_id'],
                'senderId' => $query->row['sender_id'],
                'recipientId' => $query->row['recipient_id'],
                'timeAdded' => $query->row['time_added'],
                'data' => json_decode($query->row['message'])
            );
        else
            return null;
    }

    public static function getSystemMessages($messageTypeId, $senderId = null, $start = null, $limit = null)
    {
        $query = Messaging::$instance->db->query("
            SELECT *
            FROM " . DB_PREFIX . "messages
            WHERE
                message_type_id = " . (int)$messageTypeId .
                ($senderId ? " AND sender_id = " . (int)$senderId : '') . "
            ORDER BY time_added DESC " .
            (is_numeric($start) && is_numeric($limit) ? "LIMIT $start, $limit" : '')
        );
        if ($query->num_rows)
        {
            $messages = array();
            foreach ($query->rows as $messageRecord)
            {
                $messages[] = array(
                    'messageId' => $messageRecord['message_id'],
                    'messageTypeId' => $query->row['message_type_id'],
                    'senderId' => $messageRecord['sender_id'],
                    'recipientId' => $messageRecord['recipient_id'],
                    'timeAdded' => $messageRecord['time_added'],
                    'data' => json_decode($messageRecord['message'])
                );
            }
            return $messages;
        }
        else
            return array();
    }

    public static function  getSystemMessagesCount($messageTypeId, $senderId = null)
    {
        $query = Messaging::$instance->db->query("
            SELECT count(*) as quantity
            FROM " . DB_PREFIX . "messages
            WHERE
                message_type_id = " . (int)$messageTypeId .
                ($senderId ? " AND sender_id = " . (int)$senderId : '')
        );
        return $query->row['quantity'];
    }

    public static function submitSystemMessage($senderId, $recipientId, $messageTypeId, $data)
    {
        Messaging::$instance->db->query("
            INSERT INTO " . DB_PREFIX . "messages
            SET
                sender_id = " . (int)$senderId . ",
                recipient_id = " . (int)$recipientId . ",
                message_type_id = " . (int)$messageTypeId . ",
                message = '" . Messaging::$instance->db->escape(json_encode($data)) . "',
                time_added = NOW()
        ");
        Messaging::$instance->load->library('SystemMessageClassFactory');
        SystemMessageClassFactory::createInstance($messageTypeId, Messaging::$instance->load)->handleCreate(Messaging::$instance->db->getLastId());
    }

    public static function updateSystemMessage($messageId, $data)
    {
//        Messaging::$instance->log->write(print_r($data, true));
        $message = Messaging::getSystemMessage($messageId);
        Messaging::$instance->db->query("
            UPDATE " . DB_PREFIX . "messages
            SET
                message = '" . Messaging::$instance->db->escape(json_encode($data)) . "'
            WHERE message_id = " . (int)$messageId
        );
        Messaging::$instance->load->library('SystemMessageClassFactory');
        SystemMessageClassFactory::createInstance($message['messageTypeId'], Messaging::$instance->load)->handleUpdate($messageId);
    }
}
