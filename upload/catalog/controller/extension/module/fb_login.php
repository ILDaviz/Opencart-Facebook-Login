<?php
/**
 * Facebook Login 
 * @author David <david_ev@icloud.com>
 */
class ControllerExtensionModuleFbLogin extends Controller {

    public function index(){
        $data = array();
        $this->load->language('extension/module/fb_login');
        $data['request'] =  $this->url->link('extension/module/fb_login/fblogin', '', true);
        $data['status_fb_login'] = $this->config->get('module_fb_login_status');
        $data['app_id'] = $this->config->get('module_fb_login_app_id');
        $data['location_code'] = $this->config->get('module_fb_login_app_loc');

        return $this->load->view('extension/module/fb_login', $data);
    }

    public function fblogin(){

        $this->load->language('extension/module/fb_login');
        $this->load->model('account/customer');
        $this->load->model('account/activity');
        
        $json = array();
        $data = array();

        if (!isset($this->request->get['email'])) {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login whit facebook missing email');
        }

        if ($this->request->get['email'] == 'undefined') {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login whit facebook undefined email');
        }

        if (!filter_var($this->request->get['email'], FILTER_VALIDATE_EMAIL)) {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login facebook email not valid');
        }

        if (!isset($this->request->get['fname'])) {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login whit facebook missing firstname');
        }
        
        if (!isset($this->request->get['lname'])) {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login whit facebook missing lastname');
        }
        
        if (!isset($this->request->get['fb_id'])) {
            $json['error'][] = $this->language->get('error_access');
            $this->log->write('Error login whit facebook missing facebook id');
		}

        if (!$json) {

            $customer_info = $this->model_account_customer->getCustomerByEmail($this->request->get['email']);
            if(!empty($customer_info)){ //Customer already registered , Only Log in the customer
                if ($customer_info && $this->customer->login($customer_info['email'],$this->request->get['fb_id'])) {
                    // Default Addresses
                    $this->load->model('account/address');

                    if ($this->config->get('config_tax_customer') == 'payment') {
                            $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }

                    if ($this->config->get('config_tax_customer') == 'shipping') {
                            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }
                }
                if ($this->customer->isLogged()) {
                    $this->model_account_activity->addActivity('login',array(
                        'customer_id'   => $customer_info['customer_id'],
                        'Name'          => 'fb - '. $this->request->get['fname'] . ' ' . $this->request->get['lname']
                    ));
                    $json['success'] = 'login';
                } else {
                    $json['error'][] = $this->language->get('error_login');
                }
            } else{

                $data['email'] = $this->request->get['email'];
                $data['firstname'] = $this->request->get['fname'];
                $data['lastname'] = $this->request->get['lname'];
                $data['telephone'] = '';
                $data['fax'] = '';
                $data['company'] = '';
                $data['address_1'] = '';
                $data['address_2'] = '';
                $data['city'] = '';
                $data['postcode'] = '';
                $data['country_id'] = '';
                $data['zone_id'] = '';
                $data['password'] = $this->request->get['fb_id'];
                
                $customer_id = $this->model_account_customer->addCustomer($data);

                if ($customer_id && $this->customer->login($data['email'], '', true)) {
                    // Default Addresses
                    $this->load->model('account/address');

                    if ($this->config->get('config_tax_customer') == 'payment') {
                            $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }

                    if ($this->config->get('config_tax_customer') == 'shipping') {
                            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }
                }

                if ($this->customer->isLogged()) {
                    $json['success'] = 'register';
                } else{
                    $json['error'][] = $this->language->get('error_register');
                }

            }
        }

        $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
    }
}