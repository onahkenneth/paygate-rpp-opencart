<?php
/**
 *
 * PayGate Opencart Plugin
 *
 * @author Collmax Technologies
 * @author info@collmaxtech.co.za
 * @version 1.0.0
 * @package Opencart
 * @subpackage payment
 * @copyright Copyright (C) 2013 Collmax Technologies  - All rights reserved.
 */
 
class ControllerPaymentPaygate extends Controller
{
	protected function index()
	{
		$this->language->load('payment/paygate');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->load->model('checkout/order');
		$this->data['action'] = 'https://www.paygate.co.za/paywebv2/process.trans';

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		if ($order_info)
		{
			$this->data['item_name'] = html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8');

			$this->data['products'] = array();

			foreach ($this->cart->getProducts() as $product)
			{
				$option_data = array();

				foreach ($product['option'] as $option)
				{
					if ($option['type'] != 'file')
						$value = $option['option_value'];
					else
					{
						$filename = $this->encryption->decrypt($option['option_value']);
						$value = utf8_substr($filename, 0, utf8_strrpos($filename, '.'));
					}

					$option_data[] = array(
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$this->data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'price'    => $this->currency->format($product['price'], $order_info['currency_code'], false, false),
					'quantity' => $product['quantity'],
					'option'   => $option_data,
					'weight'   => $product['weight']
				);
			}

			$this->data['discount_amount_cart'] = 0;

			$total = $this->currency->format($order_info['total'] - $this->cart->getSubTotal(), $order_info['currency_code'], false, false);

			if ($total > 0)
			{
				$this->data['products'][] = array(
					'name'     => $this->language->get('text_total'),
					'model'    => '',
					'price'    => $total,
					'quantity' => 1,
					'option'   => array(),
					'weight'   => 0
				);
			}
			else $this->data['discount_amount_cart'] -= $total;

			$this->data['paygate_merchant_id'] =  $this->config->get('paygate_merchant_id');
			$this->data['currency_code'] = $order_info['currency_code'];
			$this->data['first_name'] = html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$this->data['last_name'] = html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$this->data['address1'] = html_entity_decode($order_info['payment_address_1'], ENT_QUOTES, 'UTF-8');
			$this->data['address2'] = html_entity_decode($order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$this->data['city'] = html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$this->data['zip'] = html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$this->data['country'] = $order_info['payment_iso_code_2'];
			$this->data['transaction_date'] = gmstrftime("%Y-%m-%b %H:%M");
			$this->data['email'] = $order_info['email'];
			$this->data['invoice'] = $this->session->data['order_id'] . ' - ' . html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8') . ' ' . html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$this->data['lc'] = $this->session->data['language'];
			$this->data['return'] = $this->url->link('checkout/success');
			$this->data['notify_url'] = $this->url->link('payment/paygate/callback', '', 'SSL');
			$this->data['cancel_return'] = $this->url->link('checkout/checkout', '', 'SSL');

			$this->data['paymentaction'] = 'sale';
			$this->data['encryption_key'] = $this->config->get('paygate_secret_key');
			$this->data['custom'] = substr($this->encryption->encrypt($this->session->data['order_id']), 0, 12);
						
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate.tpl'))
				$this->template = $this->config->get('config_template') . '/template/payment/paygate.tpl';
			else $this->template = 'default/template/payment/paygate.tpl';

			$this->render();
		}
	}

	public function callback()
	{		
		$order_id = $this->session->data['order_id'];

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($order_id);
		
		$this->request->post['route'] = null;

		if ($order_info) {
			$this->language->load('payment/paygate');
	
			$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
			
			if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
				$this->data['base'] = HTTP_SERVER;
			} else {
				$this->data['base'] = HTTPS_SERVER;
			}
			
			$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));	
			
			$this->data['text_response'] = $this->language->get('text_response');
			$this->data['text_success'] = $this->language->get('text_success');
			$this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
			$this->data['text_failure'] = $this->language->get('text_failure');
			$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart'));

			if(isset($this->request->post['TRANSACTION_STATUS']))
				$this->data['transaction_status'] = $this->request->post['TRANSACTION_STATUS'];
			if(isset($this->request->post['RESULT_CODE']))
				$this->data['result_code'] = $this->request->post['RESULT_CODE'];
			if(isset($this->request->post['RESULT_DESC']))
				$this->data['result_desc'] = $this->request->post['RESULT_DESC'];
			if(isset($this->request->post['AUTH_CODE']))
				$this->data['auth_code'] = $this->request->post['AUTH_CODE'];
			if(isset($this->request->post['TRANSACTION_ID'])) 
				$this->data['transaction_id'] = $this->request->post['TRANSACTION_ID'];
			if(isset($this->request->post['CHECKSUM']))
				$this->data['checksum'] = $this->request->post['CHECKSUM'];
			if(isset($this->request->post['RISK_INDICATOR']))
				$this->data['risk_indicator'] = $this->request->post['RISK_INDICATOR'];
			if(isset($this->request->post['PAYGATE_ID']))
				$this->data['paygate_merchant_id'] = $this->request->post['PAYGATE_ID'];
			if(isset($this->request->post['REFERENCE']))
				$this->data['reference'] = $this->request->post['REFERENCE'];
			if(isset($this->request->post['AMOUNT']))
				$this->data['amount'] = $this->request->post['AMOUNT'];

			$this->data['encryption_key'] = $this->config->get('paygate_secret_key');
		
			$checksum_source = $this->data['paygate_merchant_id'].'|';
			$checksum_source.= $this->data['reference'].'|';
			$checksum_source.= $this->data['transaction_status'].'|';
			$checksum_source.= $this->data['result_code'].'|';
			$checksum_source.= $this->data['auth_code'].'|';
			$checksum_source.= $this->data['amount'].'|';
			$checksum_source.= $this->data['result_desc'].'|';
			$checksum_source.= $this->data['transaction_id'].'|';
			$checksum_source.= $this->data['risk_indicator'].'|';
			$checksum_source.= $this->data['encryption_key'];
		
			$test_checksum = md5($checksum_source);

		
			$verified = '';
			$string = '';
			
			// Validate the request is from PayGate
			if($this->data['checksum'] == $test_checksum) {
				$verified = true;
			} else {
				$verified = false;
			}
			
			if($verified)
			{
				if($this->data['transaction_status'] == 1 && $this->data['result_code'] == 990017 && $this->data['auth_code'] !== null) {
					$this->load->model('checkout/order');
					
					$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
					
					$order_status_id = $this->config->get('paygate_completed_status_id');
					
					$this->model_checkout_order->update($order_id, $order_status_id, $this->data['result_desc'], true);
					
					$this->data['continue'] = $this->url->link('checkout/success');
	
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate_success.tpl')) {
						$this->template = $this->config->get('config_template') . '/template/payment/paygate_success.tpl';
					} else {
						$this->template = 'default/template/payment/paygate_success.tpl';
					}
	
					$this->response->setOutput($this->render());
				}
				elseif($this->data['transaction_status'] == 2 && $this->data['result_code'] == 900003) {
					$this->load->model('checkout/order');
					
					$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
					
					$order_status_id = $this->config->get('paygate_denied_status_id');
					
					$this->model_checkout_order->update($order_id, $order_status_id, $this->data['result_desc'], true);
					
					$this->data['continue'] = $this->url->link('checkout/cart');
					
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate_failure.tpl')) {
						$this->template = $this->config->get('config_template') . '/template/payment/paygate_failure.tpl';
					} else {
						$this->template = 'default/template/payment/paygate_failure.tpl';
					}
	
					$this->response->setOutput($this->render());
				}
				elseif($this->data['transaction_status'] == 2 && $this->data['result_code'] == 900007) {
					$this->load->model('checkout/order');
					
					$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
					
					$order_status_id = $this->config->get('paygate_denied_status_id');
					
					$this->model_checkout_order->update($order_id, $order_status_id, $this->data['result_desc'], true);
					
					$this->data['continue'] = $this->url->link('checkout/cart');
					
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate_failure.tpl')) {
						$this->template = $this->config->get('config_template') . '/template/payment/paygate_failure.tpl';
					} else {
						$this->template = 'default/template/payment/paygate_failure.tpl';
					}
	
					$this->response->setOutput($this->render());
				}
				elseif($this->data['transaction_status'] == 2 && $this->data['result_code'] == 900004) {
					$this->load->model('checkout/order');
					
					$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
					
					$order_status_id = $this->config->get('paygate_denied_status_id');
					
					$this->model_checkout_order->update($order_id, $order_status_id, $this->data['result_desc'], true);
					
					$this->data['continue'] = $this->url->link('checkout/cart');
					
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate_failure.tpl')) {
						$this->template = $this->config->get('config_template') . '/template/payment/paygate_failure.tpl';
					} else {
						$this->template = 'default/template/payment/paygate_failure.tpl';
					}
	
					$this->response->setOutput($this->render());
				}
				elseif($this->data['transaction_status'] == 0 && $this->data['result_code'] == 990028) {
					$this->redirect($this->url->link('checkout/checkout'));
				}	
				else { 
					$this->load->model('checkout/order');
					
					$this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
					
					$order_status_id = $this->config->get('paygate_failed_status_id');
					
					$this->model_checkout_order->update($order_id, $order_status_id, $this->data['result_desc'], true);
					
					$this->data['continue'] = $this->url->link('checkout/cart');
					
					if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/paygate_failure.tpl')) {
						$this->template = $this->config->get('config_template') . '/template/payment/paygate_failure.tpl';
					} else {
						$this->template = 'default/template/payment/paygate_failure.tpl';
					}
	
					$this->response->setOutput($this->render());
				}
			}
			
		 $this->model_checkout_order->confirm($order_id, $this->config->get('config_order_status_id'));
		}
	}
	
	public function getResultCodes($string) {
		$codes = array(
			'900001' =>	'Call for Approval',
			'900002' =>	'Card Expired',
			'900003' =>	'Insufficient Funds',
			'900004' => 'Invalid Card Number',
			'900005' => 'Bank Interface Timeout',
			'900006' => 'Invalid Card',
			'900007' => 'Declined',
			'900009' => 'Lost Card',
			'900010' => 'Invalid Card Length',
			'900011' => 'Suspected Fraud',
			'900012' => 'Card Reported As Stolen',
			'900013' => 'Restricted Card',
			'900014' => 'Excessive Card Usage',
			'900015' => 'Card Blacklisted',
			'900207' => 'Declined Authenticated Failed',
			'900220' => 'Incorrect PIN',
			'990020' => 'Auth Declined',
			'991001' => 'Invalid Expiry Date',
			'991002' => 'Invalid Amount',
			'990017' => 'Auth Done',
			'900205' => 'Unexpected Authentication Result',
			'900206' => 'Unexpected Authentication Result',
			'990001' => 'Could Not Insert into Database',
			'990022' => 'Bank Not Available',
			'990029' => 'Transaction Not Completed',
			'990053' => 'Error Processing Transaction',
			'900209' => 'Transaction Verification Failed',
			'900210' => 'Authentication Complete Trasanction Must be Restarted',
			'990024' => 'Duplicate Transaction Detected',
			'990028' => 'Transaction Cancelled',
		);
		
		foreach($codes as $result_code) {
			
		}
	}
	
	public function getTransactionStatus($string) {
		$codes = array(
			'0' => 'Not Done',
			'1' => 'Approved',
			'2' => 'Declined',
		);
		
		foreach($codes as $trans_code) {
			
		}
	}
	
	public function getRiskIndicator($string) {
		$codes = array(
			'N' => 'Not Authenticated',
			'A' => 'Authenticated',
			'X' => 'Not Applicable',
		);
		
		foreach($codes as $risk_indct) {
			
		}
	}
}
?>
