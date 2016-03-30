<?php
namespace model\shipping;
use Log;
use model\DAO;
use model\setting\ExtensionDAO;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 1.7.12
 * Time: 22:37
 * To change this template use File | Settings | File Templates.
 */
class ShippingMethodDAO extends DAO {
    public function __construct($registry) {
        parent::__construct($registry);
        $this->log = new Log('shippingModel.log');
    }

    /**
     * @param string $code
     * @return ShippingMethodBase
     */
    public function getMethod($code) {
        return ExtensionDAO::getInstance()->getExtension('shipping', $code);
    }

    /**
     * @param array $address
     * @return array
     * @throws \Exception
     */
    public function getShippingOptions($address) {
        $logging = new Log('shipping.log');
        $result = array();

//        $logging->write(print_r($address, true));
        $modelSettingExtension = $this->load->model('setting/extension');
        /** @var ShippingMethodBase[] $shippingExtensions */
        $shippingExtensions = ExtensionDAO::getInstance()->getExtensions('shipping', true, true);
        foreach ($shippingExtensions as $shippingExtension) {
            $shippingMethods = $shippingExtension->getMethodData($address);
            if (is_array($shippingMethods)) {
                $result = array_merge($result, $shippingMethods);
            }
        }

        usort($result, function($a, $b) {
            /** @var ShippingMethodBase $a */
            /** @var ShippingMethodBase $b */
            return strcmp($a['title'], $b['title']);
        });
//        $logging->write(print_r($result, true));
        return $result;
    }
}
