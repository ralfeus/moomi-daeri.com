<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 20:56
 * To change this template use File | Settings | File Templates.
 */
include_once("SystemMessageBase.php");
class AddCreditRequest extends SystemMessageBase
{
    private static $instance;

    static function getInstance($registry)
    {
        if (!isset(AddCreditRequest::$instance))
            AddCreditRequest::$instance = new AddCreditRequest($registry);
        return AddCreditRequest::$instance;
    }

    public function handleCreate($messageId)
    {
        // TODO: Implement handleCreate() method.
    }

    public function handleUpdate($messageId)
    {
//        AddCreditRequest::$instance->log->write('Starting');
        $request = Messaging::getSystemMessage($messageId);
//        AddCreditRequest::$instance->log->write(print_r($request, true));
        if ($request['data']->status == ADD_CREDIT_STATUS_ACCEPTED)
        {
            $this->load->library("Transaction");
            Transaction::addCredit($request['senderId'], $request['data']->amount, $this->registry, $request['data']->comment);
        }
    }

    public function handleDelete($messageId)
    {
        // TODO: Implement handleDelete() method.
    }
}
