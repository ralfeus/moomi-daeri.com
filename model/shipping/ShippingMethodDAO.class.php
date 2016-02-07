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
}
