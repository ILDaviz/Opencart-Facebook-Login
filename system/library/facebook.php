<?php

require(DIR_SYSTEM.'library/facebook/vendor/autoload.php');

class Facebook {

	private $fb;
	private $app_id;
	private $urlCallback;

	public function __construct($registry) {
		$this->log = $registry->get('log');
	}

	private function log($type,$string){
        $log = new log('facebook.log');
        $log->write($type.':'.$string);
	}

	public function SetFacebookLogin($app_id,$appSecret){
		$this->setAppId($app_id);
		$fb = new \Facebook\Facebook([
			'app_id' => $this->app_id,
			'app_secret' => $appSecret,
			'default_graph_version' => 'v6.0'
		]);
		$this->fb = $fb;
	}

	private function setAppId($app_id){
		$this->app_id = $app_id;
	}

	public function SetCallbeck($urlCallback){
		$this->urlCallback = $urlCallback;
	}

	public function getURLLogin(){
		$helper = $this->fb->getRedirectLoginHelper();
		$permissions = ['email','public_profile']; // Optional permissions
		$loginUrl = $helper->getLoginUrl($this->urlCallback, $permissions);
		return $loginUrl;
	}

	public function Callbeck(){
		$response = array();
		//Get Helper
		$helper = $this->fb->getRedirectLoginHelper();
		try {
			$accessToken = $helper->getAccessToken();
		} catch(Facebook\Exceptions\FacebookResponseException $e) {
			// When Graph returns an error
			$this->log('Error','Graph returned an error: ' . $e->getMessage());
			$response['connected'] = false;
			return $response;
		} catch(Facebook\Exceptions\FacebookSDKException $e) {
			// When validation fails or other local issues
			$this->log('Error','Facebook SDK returned an error: ' . $e->getMessage());
			$response['connected'] = false;
			return $response;
		}

		if (!isset($accessToken)) {
			if ($helper->getError()) {
				$this->log('Error','Unauthorized Token: ' . $helper->getError());
				$response['error_html_code'] = 401;
				$response['connected'] = false;
				$response['error'] = $helper->getError();
				$response['error_code'] = $helper->getErrorCode();
				$response['error_reason'] = $helper->getErrorReason();
				$response['error_description'] = $helper->getErrorDescription();
				return $response;
			} else {
				$this->log('Error','Bad Request');
				$response['connected'] = false;
				$response['error_html_code'] = 400;
				return $response;
			}
		}
		$oAuth2Client = $this->fb->getOAuth2Client();
		$tokenMetadata = $oAuth2Client->debugToken($accessToken);
		$tokenMetadata->validateAppId($this->app_id);
		// If you know the user ID this access token belongs to, you can validate it here
		//$tokenMetadata->validateUserId('123');
		$tokenMetadata->validateExpiration();
		$response['connected'] = true;
		$response['fb_access_token'] = (string) $accessToken;
		return $response;
	}

	public function getData($token){
		$res = $fb->get('/me', $token );
		return $res;
	}
}