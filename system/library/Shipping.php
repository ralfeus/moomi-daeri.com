<?php
/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 21.7.12
 * Time: 23:32
 * To change this template use File | Settings | File Templates.
 * @deprecated
 */
//class Shipping {
////    public static function getCost($orderItems, $shippingMethod, $ext = null, $registry = null) {
////        $shipping_method = explode(".", $shippingMethod);
////        $shippingModel = $registry->get('load')->model("shipping/" . $shipping_method[0]);
////        return $shippingModel->getCost($shipping_method[1], $orderItems, $ext);
////    }
//
////    public static function getName($shippingMethod, $registry = null)
////    {
////        $shippingMethodComponents = explode('.', $shippingMethod);
////        $shippingMethodCode = $shippingMethodComponents[0];
////        $language = new Language($registry->get('language')->directory);
////        $language->load("shipping/$shippingMethodCode");
////        $shippingName = $language->get('headingTitle');
////        return $shippingName;
////    }
//
////    public static function getShippingMethods($address, $registry = null)
////    {
////        $logging = new Log('shipping.log');
////        $result = array();
////        if (!$registry)
////            return $result;
////
//////        $logging->write(print_r($address, true));
////        $shippingExtensions = \model\setting\ExtensionDAO::getInstance()->getExtensions('shipping', true, true);
////        /** @var \model\shipping\ShippingMethodBase $shippingExtension */
////        foreach ($shippingExtensions as $shippingExtension)
////        {
////            $methodData = $shippingExtension->getMethodData($address);
//////            $logging->write(print_r($methodData, true));
////            if (is_array($methodData))
////                foreach ($methodData as $methodDataEntry)
////                {
////                    $result[] = $methodDataEntry;
////                    $name[] = $methodDataEntry['shippingMethodName'];
////                }
////        }
////        array_multisort($name, $result);
//////        $logging->write(print_r($result, true));
////        return $result;
////    }
//}
