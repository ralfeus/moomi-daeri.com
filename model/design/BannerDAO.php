<?php
/**
 * Created by PhpStorm.
 * User: ralfeus
 * Date: 5/10/2016
 * Time: 8:12 PM
 */

namespace model\design;

use model\DAO;

class BannerDAO extends DAO {
    public function getBanner($bannerId) {
        $banner = $this->getCache()->get('banner.' . $bannerId);
        if (is_null($banner)) {
            $query = $this->getDb()->query("
                SELECT * 
                FROM 
                    banner_image AS bi 
                    LEFT JOIN banner_image_description AS bid ON bi.banner_image_id  = bid.banner_image_id 
                WHERE bi.banner_id = :bannerId AND bid.language_id = :languageId
            ", [
                ':bannerId' => $bannerId,
                ':languageId' => $this->config->get('config_language_id')
            ]);

            $banner = $query->rows;
            $this->getCache()->set('banner.' . $bannerId, $banner);
        }
        return $banner;
    }
}