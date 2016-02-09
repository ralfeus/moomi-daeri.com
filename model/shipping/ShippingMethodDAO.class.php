<?php
namespace model\shipping;
use Log;
use model\DAO;
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
        $className = "model\\shipping\\" . ucfirst($code);
        return new $className($this->registry);
    }

    public function getMethods($address) {
        $logging = new Log('shipping.log');
        $result = array();

//        $logging->write(print_r($address, true));
        $modelSettingExtension = $this->load->model('setting/extension');
        $shippingExtensions = $modelSettingExtension->getExtensions('shipping', true, true);
        foreach ($shippingExtensions as $shippingExtension) {
            $methodData = $this->getMethod($shippingExtension)->getMethodData($address);
//            $logging->write(print_r($methodData, true));
            if (is_array($methodData))
                foreach ($methodData as $methodDataEntry) {
                    $result[] = $methodDataEntry;
                    $name[] = $methodDataEntry['shippingMethodName'];
                }
        }
        array_multisort($name, $result);
//        $logging->write(print_r($result, true));
        return $result;
    }
}
