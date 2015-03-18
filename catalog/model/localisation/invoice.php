<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 23:31
 * To change this template use File | Settings | File Templates.
 */
class ModelLocalisationInvoice extends Model
{
    public function getInvoiceStatus($statusId, $languageId = 2)
    {
        $query = $this->getDb()->query("
            SELECT * FROM invoice_statuses
            WHERE
                invoice_status_id = " . (int)$statusId . "
                AND language_id = " . (int)$languageId
        );
        if ($query->num_rows)
            return $query->row['name'];
        else
            return "NO STATUS FOUND";
    }
}
