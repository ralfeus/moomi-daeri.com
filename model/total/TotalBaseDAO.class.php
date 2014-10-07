<?php
namespace model\total;
use model\DAO;

abstract class TotalBaseDAO extends DAO {
    public abstract function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false);
    public abstract function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false);
    public abstract function updateOrderTotal($orderId, $totalData);

    protected final function getOrderExtensionTotal(&$totalData, &$total, &$taxes, $orderId, $extension, $chosenOnes = false)
    {
        $tempTotalData = array();
        $tempTotal = 0;
        $tempTaxes = 0;
        $this->getTotal($tempTotalData, $tempTotal, $tempTaxes, $chosenOnes);
        $this->log->write(print_r($tempTotalData, true));
        $this->log->write($extension);

        /// Order total (from database)
        $query = $this->getDb()->query("
            SELECT *
            FROM order_total
            WHERE order_id = ? AND code = ?
            ", array("i:$orderId", "s:$extension")
        );
        if ($query->num_rows) {
            $orderTotalData = $query->row;
            if (count($tempTotalData)) {
                $tempTotalData[0]['value'] += $orderTotalData['value'];
                $tempTotalData[0]['text'] = $this->currency->format($tempTotalData[0]['value']);
            } else {
                $tempTotalData[] = $orderTotalData;
            }
            $tempTotal += $orderTotalData['value'];

        }
        /// Add to total
        if (count($tempTotalData))
            $totalData[] = $tempTotalData[0];
        $total += $tempTotal;
    }

    protected final function updateOrderExtensionTotal($orderId, $totalData, $extension)
    {
        $this->getDb()->query("
            UPDATE order_total
            SET
                text = ?,
                value = ?
            WHERE order_id = ? AND code = ?
            ", array('s:' . $totalData['text'], 'f:' . $totalData['value'], "i:$orderId", "s:$extension")
        );
    }
}
