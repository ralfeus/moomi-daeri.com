<?php
namespace system\library;

use model\DAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 19:49
 * To change this template use File | Settings | File Templates.
 */
class Messaging extends DAO {
    private function buildFilterString($data = array()) {
//        $this->log->write(print_r($data, true));
        $filter = "";
        if (isset($data['selectedItems']) && count($data['selectedItems']))
            $filter = "m.message_id in (" . implode(', ', $data['selectedItems']) . ")";
        else {
            if (!empty($data['filterCustomerId']))
                $filter .= ($filter ? " AND " : "") . "m.sender_id IN (" . implode(', ', $data['filterCustomerId']) . ")";
            if (!empty($data['filterTimeAdded']))
                $filter .= ($filter ? " AND " : "") . "DATE(m.time_added) = DATE('" . $this->getDb()->escape($data['filterTimeAdded']) . "')";
            if (!empty($data['systemMessageType']))
                $filter .= ($filter ? " AND " : "") . "m.message_type_id = " . (int)$data['systemMessageType'];
        }
        if ($filter)
            $filter = "WHERE $filter";
        return $filter;
    }

//    protected function buildLimitString($data = array()) {
//        $limit = "";
//        if (isset($data['start']) && is_numeric($data['start']) && isset($data['limit']) && is_numeric($data['limit']))
//            $limit = "LIMIT " . $data['start'] . ", " . $data['limit'];
//        return $limit;
//    }


    public function getSystemMessage($messageId) {
        $query = $this->getDb()->query("
            SELECT *
            FROM messages AS m
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

//    public static function getSystemMessages($messageTypeId, $senderId = null, $start = null, $limit = null)
    public function getSystemMessages($data = array()) {
        $filter = $this->buildFilterString($data);
        $limit = $this->buildLimitString($data);
        $sql = "
            SELECT *
            FROM messages AS m
            $filter
            ORDER BY time_added DESC
            $limit
        ";
        $this->log->write($sql);
        $query = $this->getDb()->query($sql);
        if ($query->num_rows) {
            $messages = array();
            foreach ($query->rows as $messageRecord) {
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
        } else
            return array();
    }

    public function getSystemMessagesCount($messageTypeId, $senderId = null) {
        $query = $this->getDb()->query("
            SELECT count(*) AS quantity
            FROM messages
            WHERE
                message_type_id = " . (int)$messageTypeId .
            ($senderId ? " AND sender_id = " . (int)$senderId : '')
        );
        return $query->row['quantity'];
    }

    public function submitSystemMessage($senderId, $recipientId, $messageTypeId, $data) {
        $this->getDb()->query("
            INSERT INTO messages
            SET
                sender_id = " . (int)$senderId . ",
                recipient_id = " . (int)$recipientId . ",
                message_type_id = " . (int)$messageTypeId . ",
                message = '" . $this->getDb()->escape(json_encode($data)) . "',
                time_added = NOW()
        ");
        (new AddCreditRequest($this->getRegistry()))->handleCreate($this->getDb()->getLastId());
    }

    public function updateSystemMessage($messageId, $data) {
//        system\library\$this->log->write(print_r($data, true));
        $message = Messaging::getSystemMessage($messageId);
        $this->getDb()->query("
            UPDATE messages
            SET
                message = '" . $this->getDb()->escape(json_encode($data)) . "'
            WHERE message_id = " . (int)$messageId
        );
        (new AddCreditRequest($this->getRegistry()))->handleUpdate($messageId);
    }
}
