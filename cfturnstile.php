<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CfTurnstile extends Module
{
    public function __construct()
    {
        $this->name = 'cfturnstile';
        $this->tab = 'front_office_features';
        $this->version = '1.2.1';
        $this->author = 'Brian Tafoya - Tafoya Ventures';
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        parent::__construct();

        $this->displayName = $this->l('Cloudflare Turnstile Integration');
        $this->description = $this->l('Protects login, registration, and contact forms with Cloudflare Turnstile captcha.');
        $this->confirmUninstall = $this->l('Do you really want to uninstall this module?');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook('displayHeader')
            && $this->registerHook('displayCustomerAccountForm')
            && $this->registerHook('displayCustomerLoginFormBefore')
            && $this->registerHook('displayContactFormBefore')
            && $this->registerHook('actionBeforeSubmitAccount')
            && $this->registerHook('actionAuthenticationBefore')
            && $this->registerHook('actionContactFormSubmitBefore')
            && Configuration::updateValue('CF_TURNSTILE_SITE_KEY', '')
            && Configuration::updateValue('CF_TURNSTILE_SECRET_KEY', '');
    }

    public function hookDisplayCustomerAccountForm() { return $this->displayCaptcha(); }
    public function hookDisplayCustomerLoginFormBefore() { return $this->displayCaptcha(); }
    public function hookDisplayContactFormBefore() { return $this->displayCaptcha(); }

    private function displayCaptcha()
    {
        $this->context->smarty->assign('turnstile_key', Configuration::get('CF_TURNSTILE_SITE_KEY'));
        return $this->display(__FILE__, 'views/templates/hook/cfturnstile.tpl');
    }

    public function validateCaptcha()
    {
        $token = Tools::getValue('cf-turnstile-response');
        if (!$token) return false;

        $response = file_get_contents("https://challenges.cloudflare.com/turnstile/v0/siteverify", false, stream_context_create([
            'http' => ['method' => 'POST', 'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query(['secret' => Configuration::get('CF_TURNSTILE_SECRET_KEY'), 'response' => $token])]
        ]));

        return json_decode($response, true)['success'] ?? false;
    }
}
