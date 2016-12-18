<?php
use model\catalog\CategoryDAO;
use model\catalog\ManufacturerDAO;
use model\catalog\ProductDAO;
use system\helper\ImageService;

class ControllerProductProduct extends Controller {
    protected function loadStrings() {
        $this->data['text_select'] = $this->language->get('text_select');
        $this->data['text_manufacturer'] = $this->language->get('text_manufacturer');
        $this->data['text_model'] = $this->language->get('text_model');
        $this->data['text_reward'] = $this->language->get('text_reward');
        $this->data['text_points'] = $this->language->get('text_points');
        $this->data['text_discount'] = $this->language->get('text_discount');
        $this->data['text_stock'] = $this->language->get('text_stock');
        $this->data['text_price'] = $this->language->get('text_price');
        $this->data['text_tax'] = $this->language->get('text_tax');
        $this->data['text_discount'] = $this->language->get('text_discount');
        $this->data['text_option'] = $this->language->get('text_option');
        $this->data['text_qty'] = $this->language->get('text_qty');
        $this->data['text_or'] = $this->language->get('text_or');
        $this->data['text_write'] = $this->language->get('text_write');
        $this->data['text_note'] = $this->language->get('text_note');
        $this->data['text_share'] = $this->language->get('text_share');
        $this->data['text_wait'] = $this->language->get('text_wait');
        $this->data['text_tags'] = $this->language->get('text_tags');

        $this->data['entry_name'] = $this->language->get('entry_name');
        $this->data['entry_review'] = $this->language->get('entry_review');
        $this->data['entry_rating'] = $this->language->get('entry_rating');
        $this->data['entry_good'] = $this->language->get('entry_good');
        $this->data['entry_bad'] = $this->language->get('entry_bad');
        $this->data['entry_captcha'] = $this->language->get('entry_captcha');

        $this->data['button_cart'] = $this->language->get('button_cart');
        $this->data['button_wishlist'] = $this->language->get('button_wishlist');
        $this->data['button_compare'] = $this->language->get('button_compare');
        $this->data['button_upload'] = $this->language->get('button_upload');
        $this->data['button_continue'] = $this->language->get('button_continue');

        $this->data['textAttachPicture'] = $this->language->get('ATTACH_FILE_TO_REVIEW');
        $this->data['textWeight'] = $this->language->get('WEIGHT');
        $this->data['tab_description'] = $this->language->get('tab_description');
        $this->data['tab_attribute'] = $this->language->get('tab_attribute');
        $this->data['tab_related'] = $this->language->get('tab_related');
    }

    /**
     *
     */
    public function index() {
		$this->language->load('product/product');

		if (isset($this->request->get['path'])) {
			$path = '';

			foreach (explode('_', $this->request->get['path']) as $path_id) {
				if (!$path) {
					$path = $path_id;
				} else {
					$path .= '_' . $path_id;
				}

				$category = CategoryDAO::getInstance()->getCategory($path_id);

        		if ($category) {
                    #kabantejay synonymizer start
                    $razdel = $category->getDescription()->getName();
                    #kabantejay synonymizer end

                    $this->setBreadcrumbs([[
        		        'text' => $category->getDescription()->getName(),
                        'route' => 'product/category&path=' . $path
                    ]]);
				}
			}
		}

		if (isset($this->request->get['manufacturer_id'])) {
			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_brand'),
				'href'      => $this->getUrl()->link('product/manufacturer'),
				'separator' => $this->language->get('text_separator')
			);

			$manufacturer_info = ManufacturerDAO::getInstance()->getManufacturer($this->request->get['manufacturer_id']);

			if ($manufacturer_info) {
				$this->data['breadcrumbs'][] = array(
					'text'	    => $manufacturer_info['name'],
					'href'	    => $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $this->request->get['manufacturer_id']),
					'separator' => $this->language->get('text_separator')
				);
			}
		}

		if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_tag'])) {
			$url = '';

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}

			if (isset($this->request->get['filter_tag'])) {
				$url .= '&filter_tag=' . $this->request->get['filter_tag'];
			}

			if (isset($this->request->get['filter_description'])) {
				$url .= '&filter_description=' . $this->request->get['filter_description'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $this->language->get('text_search'),
				'href'      => $this->getUrl()->link('product/search', $url),
				'separator' => $this->language->get('text_separator')
			);
		}

		if (isset($this->request->get['product_id'])) {
			$productId = $this->request->get['product_id'];
		} else {
			$productId = 0;
		}

        try {
            $product = ProductDAO::getInstance()->getProduct($productId, false, true);
            if ($product->getStatus() == false) {
                throw new InvalidArgumentException("The product is disabled");
            }
            $description = is_null($product->getDescription($this->getLanguage()->getId()))
                ? new \model\localization\Description($this->getLanguage()->getId(), '')
                : $product->getDescription($this->getLanguage()->getId());
            $this->data['product_info'] = $product;
//print_r($product_info);exit;
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}

			if (isset($this->request->get['filter_tag'])) {
				$url .= '&filter_tag=' . $this->request->get['filter_tag'];
			}

			if (isset($this->request->get['filter_description'])) {
				$url .= '&filter_description=' . $this->request->get['filter_description'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

			$this->data['breadcrumbs'][] = array(
				'text'      => $product->getName(),
				'href'      => $this->getUrl()->link('product/product', $url . '&product_id=' . $this->request->get['product_id']),
				'separator' => $this->language->get('text_separator')
			);

			if (!empty($description->getSeoTitle())) {
				$this->document->setTitle($description->getSeoTitle());
			} else {
				$this->document->setTitle($product->getName());
			}

			$this->document->setDescription($description->getMetaDescription());
			$this->document->setKeywords($description->getMetaKeyword());
			//$this->document->addLink($this->getUrl()->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');

			$this->data['seo_h1'] = $description->getSeoH1();

			$this->data['heading_title'] = $product->getName();
            $this->data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product->getMinimum());

			$this->getLoader()->model('catalog/review');

			$this->data['tab_review'] = sprintf($this->language->get('tab_review'), $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']));

			$this->data['product_id'] = $product->getId();
			$this->data['manufacturer'] = $product->getManufacturer()->getName();
			$this->data['manufacturers'] = $this->getUrl()->link('product/manufacturer/product', 'manufacturer_id=' . $product->getManufacturer()->getId());
			$this->data['model'] = $product->getModel();
			$this->data['reward'] = $product->getRewards();
			$this->data['points'] = $product->getPoints();

			if ($product->getQuantity() <= 0) {
				$this->data['stock'] = $product->getStockStatusId();
			} elseif ($this->getConfig()->get('config_stock_display')) {
				$this->data['stock'] = $product->getQuantity();
			} else {
				$this->data['stock'] = $this->language->get('text_instock');
			}

			if ($product->getImagePath()) {
				$this->data['popup'] = ImageService::getInstance()->resize($product->getImagePath(), $this->getConfig()->get('config_image_popup_width'), $this->getConfig()->get('config_image_popup_height'));
			} else {
				$this->data['popup'] = '';
			}

			$results = ProductDAO::getInstance()->getProductImages($this->request->get['product_id']);

            if ($results) {
                $this->data['thumb'] = ImageService::getInstance()->resize($results[0]->getImagePath(), $this->getConfig()->get('config_image_thumb_width'), $this->getConfig()->get('config_image_thumb_height'));
            } else if ($product->getImagePath()) {
				$this->data['thumb'] = ImageService::getInstance()->resize($product->getImagePath(), $this->getConfig()->get('config_image_thumb_width'), $this->getConfig()->get('config_image_thumb_height'));
			} else {
				$this->data['thumb'] = '';
			}

			$this->data['images'] = array();

			foreach ($results as $tag) {
				$this->data['images'][] = array(
					'popup' => ImageService::getInstance()->resize($tag->getImagePath(), $this->getConfig()->get('config_image_popup_width'), $this->getConfig()->get('config_image_popup_height')),
					'thumb' => ImageService::getInstance()->resize($tag->getImagePath(), $this->getConfig()->get('config_image_additional_width'), $this->getConfig()->get('config_image_additional_height'))
				);
			}

			if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
				$this->data['price'] = $this->getCurrentCurrency()->format($product->getPrice());
			} else {
				$this->data['price'] = false;
			}

			if ((float)$product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId())) {
				$this->data['special'] = $this->getCurrentCurrency()->format($product->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId()));
			} else {
				$this->data['special'] = false;
			}

//			if ($this->getConfig()->get('config_tax')) {
//				$this->data['tax'] = $this->getCurrentCurrency()->format((float)$product_info->getSpecial'] ? $product_info['special'] : $product_info['price());
//			} else {
//				$this->data['tax'] = false;
//			}

			$discounts = ProductDAO::getInstance()->getProductDiscounts($product->getId());

			$this->data['discounts'] = array();

			foreach ($discounts as $discount) {
				$this->data['discounts'][] = array(
					'quantity' => $discount['quantity'],
					'price'    => $this->getCurrentCurrency()->format($discount['price'])
				);
			}

			$this->data['options'] = array();

			foreach (ProductDAO::getInstance()->getProductOptions($this->request->get['product_id']) as $productOption) {
				if ($productOption->getOption()->isMultiValueType()) {
					$option_value_data = array();

					foreach ($productOption->getValue() as $option_value) {
					    if (is_null($option_value->getOptionValue())) {
					        continue;
                        }
						if (!$option_value->getSubtract() || ($option_value->getQuantity() > 0)) {
							$option_value_data[] = array(
								'product_option_value_id' => $option_value->getId(),
								'option_value_id'         => $option_value->getOptionValue()->getId(),
								'name'                    => $option_value->getOptionValue()->getName(),
								'image'                   => ImageService::getInstance()->resize($option_value->getOptionValue()->getImage(), 50, 50),
								'price'                   => (float)$option_value->getPrice() ? $this->getCurrentCurrency()->format($option_value->getPrice()) : false,
								'price_prefix'            => $option_value->getPrice() < 0 ? '-' : '+'
							);
						}
					}

					$this->data['options'][] = array(
						'product_option_id' => $productOption->getId(),
						'option_id'         => $productOption->getOption()->getId(),
						'name'              => $productOption->getOption()->getName(),
						'type'              => $productOption->getType(),
						'option_value'      => $option_value_data,
						'required'          => $productOption->isRequired()
					);
				} elseif ($productOption->getOption()->isSingleValueType()) {
					$this->data['options'][] = array(
						'product_option_id' => $productOption->getId(),
						'option_id'         => $productOption->getOption()->getId(),
						'name'              => $productOption->getOption()->getName(),
						'type'              => $productOption->getType(),
						'option_value'      => $productOption->getValue(),
						'required'          => $productOption->isRequired()
					);
				}
			}

			if ($product->getMinimum()) {
				$this->data['minimum'] = $product->getMinimum();
			} else {
				$this->data['minimum'] = 1;
			}


			$date_added = getdate(strtotime($product->getDateAdded()));
			$date_added = mktime(0, 0, 0, $date_added['mon'], $date_added['mday'], $date_added['year']);

			$this->data['review_status'] = $this->getConfig()->get('config_review_status');
			$this->data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product->getReviewsCount());
			$this->data['rating'] = (int)$product->getRating();
			$this->data['description'] = html_entity_decode($description->getDescription(), ENT_QUOTES, 'UTF-8');
			$this->data['image_description'] = html_entity_decode($product->getImageDescription(), ENT_QUOTES, 'UTF-8');
			$this->data['attribute_groups'] = $product->getAttributes();
			$this->data['hot'] = $date_added + 86400 * $this->getConfig()->get('config_product_hotness_age') > time();
			$this->data['weight'] = $this->weight->format($product->getWeight()->getWeight(), $product->getWeight()->getUnit()->getId());


			$this->data['products'] = array();

            #kabantejay synonymizer start
            if (!is_null($product->getManufacturer())) {
                $brand = '';
            } else {
                $brand = $product->getManufacturer();
            }
            if (!isset($razdel)) {
                $razdel = '';
            }
            if (!isset($category)) {
                $syncat = '';
            } else {
                $syncat = $category->getDescription()->getName();
            }
            if (!is_null($product->getModel())) {
                $synmod = '';
            } else {
                $synmod = $product->getModel();
            }
            if ($this->data['special'] == false) {
                $synprice = $this->data['price'];
            } else {
                $synprice = $this->data['special'];
            }
            $syntext=array(
            array("%H1%",$product->getName()),
            array("%BRAND%",$brand),
            array("%RAZDEL%",$razdel),
            array("%CATEGORY%",$syncat),
            array("%MODEL%",$synmod),
            array("%PRICE%",$synprice)
            );

            for ($it=0; $it<6; $it++)  {
                $this->data['description'] = str_replace($syntext[$it][0],$syntext[$it][1],$this->data['description']);
            }
            $this->data['description'] = preg_replace_callback('/\{  (.*?)  \}/xs', function ($m) {$ar = explode("|", $m[1]);return $ar[array_rand($ar, 1)];}, $this->data['description']);
            #kabantejay synonymizer end

    //			$results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);
    //        $results = $product_info->getRelated();

            foreach ($product->getRelated() as $tag) {
                if ($tag->getImagePath()) {
                    $image = ImageService::getInstance()->resize($tag->getImagePath(), $this->getConfig()->get('config_image_related_width'), $this->getConfig()->get('config_image_related_height'));
                } else {
                    $image = false;
                }

                if (($this->getConfig()->get('config_customer_price') && $this->customer->isLogged()) || !$this->getConfig()->get('config_customer_price')) {
                    $price = $this->getCurrentCurrency()->format($tag->getPrice());
                } else {
                    $price = false;
                }

                if ((float)$tag->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId())) {
                    $special = $this->getCurrentCurrency()->format($tag->getSpecialPrice($this->getCurrentCustomer()->getCustomerGroupId()));
                } else {
                    $special = false;
                }

                if ($this->getConfig()->get('config_review_status')) {
                    $rating = (int)$tag->getRating();
                } else {
                    $rating = false;
                }


                $this->data['products'][] = array(
                    'product_id' => $tag->getId(),
                    'thumb'   	 => $image,
                    'name'    	 => $tag->getName(),
                    'price'   	 => $price,
                    'special' 	 => $special,
                    'rating'     => $rating,
                    'reviews'    => sprintf($this->language->get('text_reviews'), (int)$tag->getReviewsCount()),
                    'href'    	 => $this->getUrl()->link('product/product', 'product_id=' . $tag->getId()),

                );
            }

            $this->data['tags'] = array();

//			$results = $this->model_catalog_product->getProductTags($this->request->get['product_id']);

            foreach ($product->getTags() as $tag) {
                $this->data['tags'][] = array(
                    'tag'  => $tag,
                    'href' => $this->getUrl()->link('product/search', 'filter_tag=' . $tag)
                );
            }

            ProductDAO::getInstance()->updateViewed($product->getId());

            $this->setBreadcrumbs();
            $this->children = array(
                'common/header',
                'common/column_left',
                'common/column_right',
                'common/content_top',
                'common/content_bottom',
                'common/footer'

            );

            $templateFile = '/template/product/product.tpl';
            $templateDir = file_exists(DIR_TEMPLATE . $this->getConfig()->get('config_template') . $templateFile)
                ? $this->getConfig()->get('config_template')
                : 'default';
            $this->getResponse()->setOutput($this->render($templateDir . $templateFile));
		} catch (InvalidArgumentException $exc) {
			$url = '';

			if (isset($this->request->get['path'])) {
				$url .= '&path=' . $this->request->get['path'];
			}

			if (isset($this->request->get['manufacturer_id'])) {
				$url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
			}

			if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . $this->request->get['filter_name'];
			}

			if (isset($this->request->get['filter_tag'])) {
				$url .= '&filter_tag=' . $this->request->get['filter_tag'];
			}

			if (isset($this->request->get['filter_description'])) {
				$url .= '&filter_description=' . $this->request->get['filter_description'];
			}

			if (isset($this->request->get['filter_category_id'])) {
				$url .= '&filter_category_id=' . $this->request->get['filter_category_id'];
			}

      		$this->data['breadcrumbs'][] = array(
        		'text'      => $this->language->get('text_error'),
				'href'      => $this->getUrl()->link('product/product', $url . '&product_id=' . $productId),
        		'separator' => $this->language->get('text_separator')
      		);

      		$this->document->setTitle($this->language->get('text_error'));

      		$this->data['heading_title'] = $this->language->get('text_error');

      		$this->data['text_error'] = $this->language->get('text_error');

      		$this->data['button_continue'] = $this->language->get('button_continue');

      		$this->data['continue'] = $this->getUrl()->link('common/home');

			$this->children = array(
				'common/header',
                'common/column_left',
				'common/column_right',
				'common/content_top',
				'common/content_bottom',
				'common/footer'				
			);

			$this->getResponse()->setOutput($this->render($this->getConfig()->get('config_template') . '/template/error/not_found.tpl'));
    	}
  	}

    protected function initParameters()
    {
       $this->parameters['imageFilePath'] = empty($_REQUEST['imageFilePath']) || !is_array($_REQUEST['imageFilePath']) ?
           array() :
           $_REQUEST['imageFilePath'];
    }

	public function review() {
    	$this->language->load('product/product');

		$this->getLoader()->model('catalog/review');

		$this->data['text_no_reviews'] = $this->language->get('text_no_reviews');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->data['reviews'] = array();

		$review_total = $this->model_catalog_review->getTotalReviewsByProductId($this->request->get['product_id']);

		$results = $this->model_catalog_review->getReviewsByProductId($this->request->get['product_id'], ($page - 1) * 5, 5);

		foreach ($results as $result) {
        	$this->data['reviews'][] = array(
        		'author'     => $result['author'],
				'text'       => strip_tags($result['text']),
				'rating'     => (int)$result['rating'],
        		'reviews'    => sprintf($this->language->get('text_reviews'), (int)$review_total),
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                'images' => $this->model_catalog_review->getReviewImages($result['review_id'])
        	);
      	}
//        $this->log->write(print_r($this->data, true));

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 5;
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = $this->getUrl()->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

		$this->data['pagination'] = $pagination->render();

        $this->getResponse()->setOutput($this->render($this->getConfig()->get('config_template') . '/template/product/review.tpl'));
	}

	public function write() {
//        $this->log->write(print_r($_REQUEST, true));
		$this->language->load('product/product');

		$this->getLoader()->model('catalog/review');

		$json = array();

//		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
//			$json['error'] = $this->language->get('error_name');
//		}
//
//		if ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000)) {
//			$json['error'] = $this->language->get('error_text');
//		}

		if (!$this->request->post['rating']) {
			$json['error'] = $this->language->get('error_rating');
		}

//		if (!isset($this->session->data['captcha']) || ($this->session->data['captcha'] != $this->request->post['captcha'])) {
//			$json['error'] = $this->language->get('error_captcha');
//		}
        $this->request->post = array_merge($this->request->post, $this->parameters);

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !isset($json['error'])) {
			$this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->getResponse()->setOutput(json_encode($json));
	}

	public function captcha() {
		$this->getLoader()->library('captcha');

		$captcha = new Captcha();

		$this->session->data['captcha'] = $captcha->getCode();

		$captcha->showImage();
	}

	public function upload() {
		$this->language->load('product/product');

		$json = array();

		if (!empty($this->request->files['file']['name'])) {
			$filename = basename(html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8'));

			if ((strlen($filename) < 3) || (strlen($filename) > 128)) {
        		$json['error'] = $this->language->get('error_filename');
	  		}

			$allowed = array();

			$filetypes = explode(',', $this->getConfig()->get('config_upload_allowed'));

			foreach ($filetypes as $filetype) {
				$allowed[] = trim($filetype);
			}

			if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
				$json['error'] = $this->language->get('error_filetype');
       		}

			if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
				$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
			}
		} else {
			$json['error'] = $this->language->get('error_upload');
		}

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && !isset($json['error'])) {
			if (is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
				$file = basename($filename) . '.' . md5(rand());

				// Hide the uploaded file name sop people can not link to it directly.
				$this->getLoader()->library('encryption');

				$encryption = new Encryption($this->getConfig()->get('config_encryption'));

				$json['file'] = $encryption->encrypt($file);

				move_uploaded_file($this->request->files['file']['tmp_name'], DIR_DOWNLOAD . $file);
			}

			$json['success'] = $this->language->get('text_upload');
		}

		$this->getResponse()->setOutput(json_encode($json));
	}

    public function uploadImage()
    {
        $this->log->write(print_r($_FILES, true));
        $json = array();
        foreach ($_FILES as $file)
            if (is_uploaded_file($file['tmp_name']))
            {
                if (!file_exists(DIR_IMAGE . 'upload/' . session_id()))
                    mkdir(DIR_IMAGE . 'upload/' . session_id());
                $fileName = time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
                move_uploaded_file($file['tmp_name'], DIR_IMAGE . 'upload/' . session_id() . '/' . $fileName);
                $json['filePath'] = 'upload/' . session_id() . '/' . $fileName;
            }
        $this->getResponse()->setOutput(json_encode($json));
    }
}