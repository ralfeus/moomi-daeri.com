<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 10.07.2018
 * Time: 10:00
 */

namespace system\library;


use test\system\Test;

class MessagingTest extends Test {
    /**
     * @test
     * @covers Messaging::getSystemMessagesCount()
     */
    public function getSystemMessagesCount() {
        $result = Messaging::getInstance()->getSystemMessagesCount(SYS_MSG_ADD_CREDIT);
        self::assertTrue(!is_null($result));
        $result = Messaging::getInstance()->getSystemMessagesCount(SYS_MSG_INVOICE_CREATED);
        self::assertTrue(!is_null($result));
    }
}
