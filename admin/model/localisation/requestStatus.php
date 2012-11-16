<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 30.7.12
 * Time: 19:24
 * To change this template use File | Settings | File Templates.
 */
class ModelLocalisationRequestStatus extends Model
{
    public function getStatus($statusId)
    {
        switch ($statusId)
        {
            case ADD_CREDIT_STATUS_PENDING:
                return "Pending";
            case ADD_CREDIT_STATUS_ACCEPTED:
                return "Approved";
            case ADD_CREDIT_STATUS_REJECTED:
                return "Rejected";
            default:
                return "No status found";
        }
    }
}
