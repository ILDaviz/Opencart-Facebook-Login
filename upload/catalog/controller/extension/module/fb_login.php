<?php
class ControllerExtensionModuleFbLogin extends Controller {

    public function index(){
        $data = array();
        $this->load->language('extension/module/fb_login');
        $data['request'] =  $this->url->link('extension/module/fb_login/login_or_register_user', '', true);
        $data['status_fb_login'] = $this->config->get('fb_login_status');
        $data['app_id'] = $this->config->get('module_fblogin_app_id');
        $data['locale'] = $this->config->get('module_fblogin_app_loc');
        return $this->load->view('extension/module/fb_login', $data);
    }

    private function ajax_sending($url, $request){
        //Set url base
        $curl = curl_init($url);
        //Add par
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        //Response
        $response = curl_exec($curl);
        return $response;
    }

    /**
     * Login or register method.
     * Send request to https://graph.facebook.com/v2.10/ and auto login or register response data.
     */
    public function login_or_register_user(){

        $json = array();
        
        $this->load->language('extension/module/fb_login');
        $this->load->model('account/customer');
        $this->load->model('account/activity');
        
        //Accesso token presence and id user
        $access_token = isset( $_POST['fb_response']['authResponse']['accessToken'] ) ? $_POST['fb_response']['authResponse']['accessToken'] : '';
        $fb_user_id = $_POST['fb_response']['authResponse']['userID'];    
        // Get user from Facebook with given access token
        $fb_url = 'https://graph.facebook.com/v2.10/';
        $fb_url_request = 'fields=id,first_name,last_name,email&' . 'access_token=' . $access_token;
        // Insert app secret in url.
		if( !empty( $this->config->get('module_fblogin_app_id') ) ) {
			$appsecret_proof = hash_hmac('sha256', $access_token, trim( $this->config->get('module_fblogin_app_id') ) );
            $fb_url_request += '&appsecret_proof=' . $appsecret_proof;
		}

        // Data sending
        $fb_response = $this->ajax_sending($fb_url, $fb_url_request);

        //Presence error
        if (isset($fb_response['error'])) {
            $this->log->write('FB_LOGIN :: ' . $fb_response['error']['message'] . ' Code:' . $fb_response['error']['code'] );
            $json['error'][] = 'FB_LOGIN :: ' . $fb_response['error']['message'] . ' Code:' . $fb_response['error']['code'] ;
        }
        //If no presence email
        if(empty($fb_response['email'] ) ) {
            $this->log->write('FB_LOGIN :: We need your email in order to continue. Please try loging again.');
            $json['error'][] = 'FB_LOGIN :: We need your email in order to continue. Please try loging again.';
        }

        // Map our FB response fields to the correct user fields as found
        $user = array(
            'fb_user_id' => $fb_response['id'],
			'first_name' => $fb_response['first_name'],
			'last_name'  => $fb_response['last_name'],
			'user_email' => $fb_response['email'],
			'user_pass'  => $this->token(), //Generate virtual password
        );

        //If no error
        if (!$json) {
            //check presenza email altrimenti registra il cliente.
            $customer_info = $this->model_account_customer->getCustomerByEmail($user['email']);
            //Customer already registered , Only Log in the customer
            if(!empty($customer_info)){
                //Login whit email override method
                if ($customer_info && $this->customer->login($customer_info['email'], '', true)) {
                    // Default Addresses
                    $this->load->model('account/address');
                    if ($this->config->get('config_tax_customer') == 'payment') {
                            $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }
                    if ($this->config->get('config_tax_customer') == 'shipping') {
                            $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }
                }
                //Add activity login
                if ($this->customer->isLogged()) {
                    $this->model_account_activity->addActivity('login',array(
                        'customer_id'   => $customer_info['customer_id'],
                        'Name'          => 'fb - '. $user['first_name'] . ' ' . $user['last_name']
                    ));
                    $json['success'] = 'login';
                } else {
                    $json['error'][] = $this->language->get('error_login');
                }
            } else {
                //Register user
                $data['email'] = $user['email'];
                $data['firstname'] = $user['first_name'];
                $data['lastname'] = $user['last_name'];
                $data['telephone'] = '';
                $data['fax'] = '';
                $data['password'] = '';
                $data['company'] = '';
                $data['address_1'] = '';
                $data['address_2'] = '';
                $data['city'] = '';
                $data['postcode'] = '';
                $data['country_id'] = '';
                $data['zone_id'] = '';
                $data['password'] = $user['user_pass'];
                
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