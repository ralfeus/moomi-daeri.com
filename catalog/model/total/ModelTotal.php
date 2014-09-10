<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 26.7.12
 * Time: 21:29
 * To change this template use File | Settings | File Templates.
 */
abstract class ModelTotal extends Model
{
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
        $query = $this->db->query("
            SELECT *
            FROM order_total
            WHERE order_id = $orderId AND code = '$extension'
        ");
        if ($query->num_rows)
        {
            $orderTotalData = $query->row;
            if (count($tempTotalData))
            {
                $tempTotalData[0]['value'] += $orderTotalData['value'];
                $tempTotalData[0]['text'] = $this->currency->format($tempTotalData[0]['value']);
            }
            else
                $tempTotalData[] = $orderTotalData;
            $tempTotal += $orderTotalData['value'];

        }
        /// Add to total
        if (count($tempTotalData))
            $totalData[] = $tempTotalData[0];
        $total += $tempTotal;
    }

    protected final function updateOrderExtensionTotal($orderId, $totalData, $extension)
    {
        $this->db->query("
            UPDATE order_total
            SET
                text = '" . $totalData['text'] . "',
                value = " . $totalData['value'] . "
            WHERE order_id = " . (int)$orderId . " AND code = '$extension'
        ");
    }
}
