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
 * @copyright Copyright (C) 2012 Collmax Technologies  - All rights reserved.
 */

class ControllerPaymentPaygate extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('payment/paygate');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate())
		{
			$this->model_setting_setting->editSetting('paygate', $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');
		$this->data['text_authorization'] = $this->language->get('text_authorization');
		$this->data['text_sale'] = $this->language->get('text_sale');

		$this->data['entry_email'] = $this->language->get('entry_email');
		$this->data['entry_test'] = $this->language->get('entry_test');
		$this->data['entry_transaction'] = $this->language->get('entry_transaction');
		$this->data['entry_debug'] = $this->language->get('entry_debug');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_canceled_reversal_status'] = $this->language->get('entry_canceled_reversal_status');
		$this->data['entry_completed_status'] = $this->language->get('entry_completed_status');
		$this->data['entry_denied_status'] = $this->language->get('entry_denied_status');
		$this->data['entry_expired_status'] = $this->language->get('entry_expired_status');
		$this->data['entry_failed_status'] = $this->language->get('entry_failed_status');
		$this->data['entry_pending_status'] = $this->language->get('entry_pending_status');
		$this->data['entry_processed_status'] = $this->language->get('entry_processed_status');
		$this->data['entry_refunded_status'] = $this->language->get('entry_refunded_status');
		$this->data['entry_reversed_status'] = $this->language->get('entry_reversed_status');
		$this->data['entry_voided_status'] = $this->language->get('entry_voided_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->load->model('localisation/order_status');
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
 		if (isset($this->error['warning']))
			$this->data['error_warning'] = $this->error['warning'];
		else
			$this->data['error_warning'] = '';

 		if (isset($this->error['email']))
			$this->data['error_email'] = $this->error['email'];
		else
			$this->data['error_email'] = '';

		$this->data['breadcrumbs'] = array();

 		$this->data['breadcrumbs'][] = array(
   		'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      'separator' => false
   	);

   	$this->data['breadcrumbs'][] = array(
    	'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
    	'separator' => ' :: '
   	);

 		$this->data['breadcrumbs'][] = array(
   		'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/paygate', 'token=' . $this->session->data['token'], 'SSL'),
   		'separator' => ' :: '
		);

		$this->data['action'] = $this->url->link('payment/paygate', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		$this->data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
		$this->data['entry_completed_status'] = $this->language->get('entry_completed_status');
		$this->data['entry_denied_status'] = $this->language->get('entry_denied_status');
		$this->data['entry_failed_status'] = $this->language->get('entry_failed_status');

		if (isset($this->request->post['paygate_completed_status_id']))
			  $this->data['paygate_completed_status_id'] = $this->request->post['paygate_completed_status_id'];
		else 
        $this->data['paygate_completed_status_id'] = $this->config->get('paygate_completed_status_id');

		if (isset($this->request->post['paygate_denied_status_id']))
			  $this->data['paygate_denied_status_id'] = $this->request->post['paygate_denied_status_id'];
		else 
        $this->data['paygate_denied_status_id'] = $this->config->get('paygate_denied_status_id');

		if (isset($this->request->post['paygate_failed_status_id']))
			  $this->data['paygate_failed_status_id'] = $this->request->post['paygate_failed_status_id'];
		else 
        $this->data['paygate_failed_status_id'] = $this->config->get('paygate_failed_status_id');

		if (isset($this->request->post['paygate_merchant_id']))
			  $this->data['paygate_merchant_id'] = $this->request->post['paygate_merchant_id'];
		else 
        $this->data['paygate_merchant_id'] = $this->config->get('paygate_merchant_id');

		if (isset($this->request->post['paygate_secret_key']))
			  $this->data['paygate_secret_key'] = $this->request->post['paygate_secret_key'];
		else 
        $this->data['paygate_secret_key'] = $this->config->get('paygate_secret_key');

		if (isset($this->request->post['paygate_sort_order']))
			  $this->data['paygate_sort_order'] = $this->request->post['paygate_sort_order'];
		else 
        $this->data['paygate_sort_order'] = $this->config->get('paygate_sort_order');

		if (isset($this->request->post['paygate_status']))
			  $this->data['paygate_status'] = $this->request->post['paygate_status'];
		else 
        $this->data['paygate_status'] = $this->config->get('paygate_status');

		$this->template = 'payment/paygate.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'payment/paygate'))
			  $this->error['warning'] = $this->language->get('error_permission');

		if (!$this->request->post['paygate_merchant_id'])
			  $this->error['paygate_merchant_id'] = $this->language->get('error_merchant_id');

		if (!$this->request->post['paygate_secret_key'])
			  $this->error['paygate_secret_key'] = $this->language->get('error_secret_key');
			  
		if (!$this->error)
			  return true;
		else 
        return false;
	}
}
?>
