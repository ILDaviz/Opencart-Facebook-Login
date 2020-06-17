<?php
/**
 * Facebook Login Controller for Admin area
 * @author David <david_ev@icloud.com>
 */

class ControllerExtensionModuleFbLogin extends Controller
{

    private $error = array();

    public function index()
    {
        $this->load->language('extension/module/fb_login');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/module');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (!isset($this->request->get['module_id'])) {
                $this->model_setting_module->addModule('fb_login', $this->request->post);
            } else {
                $this->model_setting_module->editModule($this->request->get['module_id'], $this->request->post);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['name'])) {
            $data['error_name'] = $this->error['name'];
        } else {
            $data['error_name'] = '';
        }

        if (isset($this->error['app_id'])) {
            $data['error_app_id'] = $this->error['app_id'];
        } else {
            $data['error_app_id'] = '';
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

        if (!isset($this->request->get['module_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'], true)
            );
        } else {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true)
            );
        }

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/fb_login', 'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        if (isset($this->request->get['module_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
        }

        // Riferimento del modulo
        if (isset($this->request->post['name'])) {
            $data['name'] = $this->request->post['name'];
        } elseif (!empty($module_info)) {
            $data['name'] = $module_info['name'];
        } else {
            $data['name'] = '';
        }

        // Id fornito da Facebook
        if (isset($this->request->post['app_id'])) {
            $data['app_id'] = $this->request->post['app_id'];
        } elseif (!empty($module_info)) {
            $data['app_id'] = $module_info['app_id'];
        } else {
            $data['app_id'] = '';
        }

        // Si inserisce come un blocco o si "attacca ad un determinato pulsante nella pagina"
        if (isset($this->request->post['type_attach'])) {
            $data['type_attach'] = $this->request->post['type_attach'];
        } elseif (!empty($module_info)) {
            $data['type_attach'] = $module_info['type_attach'];
        } else {
            $data['type_attach'] = false;
        }

        // Questo serve a determinare la località es: en_EN o it_IT
        if (isset($this->request->post['loc'])) {
            $data['loc'] = $this->request->post['loc'];
        } elseif (!empty($module_info)) {
            $data['loc'] = $module_info['loc'];
        } else {
            $data['loc'] = 'en_EN';
        }

        if (isset($this->request->post['status'])) {
            $data['status'] = $this->request->post['status'];
        } elseif (!empty($module_info)) {
            $data['status'] = $module_info['status'];
        } else {
            $data['status'] = false;
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/fb_login', $data));
    }

    protected function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/fb_login')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 64)) {
            $this->error['name'] = $this->language->get('error_name');
        }

        if (!isset($this->request->post['app_id']) || strlen($this->request->post['app_id'])){
            $this->error['app_id'] = $this->language->get('error_app_id');
        }

        return !$this->error;
    }

}