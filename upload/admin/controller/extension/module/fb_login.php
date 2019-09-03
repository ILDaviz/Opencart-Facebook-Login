<?php
class ControllerExtensionModuleFbLogin extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/fb_login');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('fb_login', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
        }
        
        if (isset($this->error['app_id'])) {
			$data['error_app_id'] = $this->error['app_id'];
		} else {
			$data['error_app_id'] = '';
        }
        
        if (isset($this->error['loc'])) {
			$data['error_loc'] = $this->error['loc'];
		} else {
			$data['error_loc'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		if (isset($this->request->post['fb_login_status'])) {
			$data['fb_login_status'] = $this->request->post['fb_login_status'];
		} else {
			$data['fb_login_status'] = $this->config->get('fb_login_status');
        }

        if (isset($this->request->post['fb_login_app_id'])) {
			$data['fb_login_app_id'] = $this->request->post['fb_login_app_id'];
		} else {
			$data['fb_login_app_id'] = $this->config->get('fb_login_app_id');
        }

        if (isset($this->request->post['fb_login_app_loc'])) {
			$data['fb_login_app_loc'] = $this->request->post['fb_login_app_loc'];
		} else {
			$data['fb_login_app_loc'] = $this->config->get('fb_login_app_loc');
        }

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/fb_login', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/fb_login')) {
			$this->error['warning'] = $this->language->get('error_permission');
        }
        
        if (!$this->request->post['fb_login_app_id']) {
			$this->error['app_id'] = $this->language->get('error_app_id');
        }
        
        if (!$this->request->post['fb_login_app_loc']) {
			$this->error['loc'] = $this->language->get('error_loc');
		}

		return !$this->error;
	}

	public function logout(){
		$data = array();
		echo $this->load->view('extension/module/fb_logout', $data);
	}
}