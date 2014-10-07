<?php
namespace model\total;

use model\checkout\CartDAO;

class LocalShippingDAO extends TotalBaseDAO {
    private function checkLocalShipping($supplierId, $orderTotal, &$suppliers) {
        if (is_null($supplierId)) {
            return;
        }
        if ($orderTotal >= $suppliers[$supplierId]['freeShippingThreshold']) {
            $suppliers[$supplierId]['shippingCost'] = 0;
        }
    }

    public function getOrderTotal(&$totalData, &$total, &$taxes, $orderId, $chosenOnes = false) {
        /// Calculate local shipping cost of the cart
        $tempTotalData = array();
        $tempTotal = 0;
        $tempTaxes = 0;
        $this->getTotal($tempTotalData, $tempTotal, $tempTaxes, $chosenOnes);

        /// Calculate local shipping cost of order items in the database
        $this->load->language('total/localShipping');

        $localShippingTotal = $this->getDb()->queryScalar("
            SELECT sum(shipping) AS total
            FROM order_product
            WHERE order_id = ?
            ", array("i:$orderId")
        );
        if ($localShippingTotal === false) {
            $localShippingTotal = 0;
        }

        $tempOrderTotalData = array(
            'code'       => 'localShipping',
            'title'      => $this->getLanguage()->get('LOCAL_SHIPPING'),
            'text'       => $this->getCurrency()->format($localShippingTotal),
            'value'      => $localShippingTotal,
            'sort_order' => $this->config->get('localShipping_sort_order')
        );

        /// Calculate total subtotal
        $tempTotalData[0]['value'] += $tempOrderTotalData['value'];
        $tempTotalData[0]['text'] = $this->getCurrency()->format($tempTotalData[0]['value']);
        $tempTotal += $tempOrderTotalData['value'];
        /// Pass total subtotal to reference variables
        $totalData[] = $tempTotalData[0];
        $total += $tempTotal;
        $taxes += $tempTaxes;
    }

    public function getTotal(&$totalData, &$total, &$taxes, $chosenOnes = false) {
        /// Checking order totals and defining local shipping cost per supplier
        $suppliers = array();  $currentSupplierId = null; $currentSupplierOrderTotal = 0;
        foreach (CartDAO::getInstance()->getProducts($chosenOnes) as $product) {
            /// Show local shipping cost
            if ($currentSupplierId != $product['supplierId']) {
                $this->checkLocalShipping($currentSupplierId, $currentSupplierOrderTotal, $suppliers);
                $currentSupplierId = $product['supplierId'];
                $suppliers[$currentSupplierId]['shippingCost'] = $product['supplierShippingCost'];
                $suppliers[$currentSupplierId]['freeShippingThreshold'] = $product['supplierFreeShippingThreshold'];
                $currentSupplierOrderTotal = $product['total'];
            } else {
                $currentSupplierOrderTotal += $product['total'];
            }
        }
        $this->checkLocalShipping($currentSupplierId, $currentSupplierOrderTotal, $suppliers);

        /// Summing up the local shipping cost
        $localShippingTotal = 0;
        foreach ($suppliers as $supplier) {
            $localShippingTotal += $supplier['shippingCost'];
        }

        /// Filling out result
        $this->load->language('total/localShipping');
        $totalData[] = array(
            'code'       => 'localShipping',
            'title'      => $this->getLanguage()->get('LOCAL_SHIPPING'),
            'text'       => $this->getCurrency()->format($localShippingTotal),
            'value'      => $localShippingTotal,
            'sort_order' => $this->config->get('localShipping_sort_order')
        );

        $total += $localShippingTotal;
    }

    public function updateOrderTotal($orderId, $totalData) {
        $this->updateOrderExtensionTotal($orderId, $totalData, 'localShipping');
    }
}