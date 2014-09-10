<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 23:11
 * To change this template use File | Settings | File Templates.
 */
class ModelToolCommunication extends Model
{
    public function sendMessage($customerId, $message, $messageTypeId)
    {
        $this->db->query("
            INSERT INTO messages
            SET
                sender_id = 0,
                recipient_id = " . (int)$customerId . ",
                message = '" . $this->db->escape($message) . "',
                time_added = NOW(),
                message_type_id = " . (int)$messageTypeId
        );
    }
}
