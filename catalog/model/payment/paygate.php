<?php

/**
 *
 * PayGate Opencart Plugin
 *
 * @author  Collmax Technologies
 * @author info@collmaxtech.co.za
 * @version 1.0.0
 * @package Opencart
 * @subpackage payment
 * @copyright Copyright (C) 2013 Collmax Technologies  - All rights reserved.
 * changed the currencies array from all sort to only ZAR - gordon
 */
 
class ModelPaymentPaygate extends Model
{
   	public function getMethod($address, $total)
  	{
		    $this->load->language('payment/paygate');

		    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('paygate_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		    if ($this->config->get('paygate_total') > $total)
			      $status = false;
		    elseif(!$this->config->get('paygate_geo_zone_id'))
			      $status = true;
		    elseif($query->num_rows)
			      $status = true;
		    else 
            $status = false;

		    $currencies = array(
			      'ZAR'
		    );

		    if (!in_array(strtoupper($this->currency->getCode()), $currencies))
			      $status = true;

		    $method_data = array();

		    if ($status) {
            $method_data = array(
                'code' => 'paygate',
        	      'title' => $this->language->get('text_title'),
				        'sort_order' => $this->config->get('paygate_sort_order')
      	    );
    	  }

    	  return $method_data;
  	}
}

?>
