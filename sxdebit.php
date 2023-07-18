<?php
/**
 * PrestaShop module created by Saxtec, a prestashop certificated agency
 *
 * @author    Saxtec https://www.saxtec.com
 * @copyright 2008-2019 Saxtec
 * @license   This program is not free software and you can't resell and redistribute it
 *
 * CONTACT WITH DEVELOPER prestashop@saxtec.com
 */

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class SxDebit extends PaymentModule
{
    protected $html = '';
    protected $postErrors = array();

    public $details;
    public $owner;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = 'sxdebit';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.9';
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->author = 'Saxtec';
        $this->module_key = 'b5eacfaeb2025d8157c8aa4269adaf60';
        $this->controllers = array('validation');
        $this->is_eu_compatible = 1;

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('SEPA - Direct Debit (Lastschrift)');
        $this->description = $this->l('SX SEPA Direct Debit Payment');

        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
    }

    public function install()
    {
        if (!parent::install()
            || !$this->registerHook('paymentOptions')
            || !$this->registerHook('paymentReturn')
            || !$this->registerHook('displayAdminOrder')) {
            return false;
        }

        // Datenbank Tabelle anlegen
        $_table = "CREATE TABLE IF NOT EXISTS `"._DB_PREFIX_."order_sxdebit` ( "
                . " `id_order` int(10) unsigned DEFAULT NULL, "
                . " `iban` varchar(50) DEFAULT NULL, "
                . " `bic` varchar(50) DEFAULT NULL, "
                . " `owner` varchar(255) DEFAULT NULL, "
                . " `sepa_date` date DEFAULT NULL, "
                . " `mandate` varchar(255) DEFAULT NULL ) ";
        Db::getInstance()->execute($_table);

        Configuration::updateValue('SEPA_ID_ORDER_STATE', $this->createOrderState());
        Configuration::updateValue('SEPA_PAYMENT_DAYS', null);
        Configuration::updateValue('SEPA_ID_CREDITOR', null);

        return true;
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function createOrderState()
    {
        $order_state = new OrderState();
        $order_state->name = array();

        foreach (Language::getLanguages() as $language) {
            $order_state->name[$language['id_lang']] = $this->l('SEPA Payment');
        }

        $order_state->module_name = $this->name;
        $order_state->hidden      = false;

        $order_state->color       = 'Orange';

        $order_state->invoice     = false;
        $order_state->paid        = false;
        $order_state->delivery    = false;
        $order_state->shipped     = false;

        $order_state->send_email  = false;
        $order_state->template    = false;

        $order_state->unremovable = false;
        $order_state->logable     = true;

        if ($order_state->add()) {
            return $order_state->id;
        }

        return false;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }


        $payment_options = array(
            $this->getEmbeddedPaymentOption(),
        );

        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getEmbeddedPaymentOption()
    {
        $_tpl = "module:sxdebit/views/templates/front/payment_infos.tpl";
        $embeddedOption = new PaymentOption();
        $embeddedOption->setCallToActionText($this->l('Pay with SEPA Debit'))
                       ->setForm($this->generateForm())
                       ->setAdditionalInformation($this->context->smarty->fetch($_tpl));

        return $embeddedOption;
    }

    protected function generateForm()
    {
        if (isset($_REQUEST['sx_ib0']) && ($_REQUEST['sx_ib0'] == 0)) {
            $show_iban_error = 1;
        } else {
            $show_iban_error = 0;
        }


        if (isset($_REQUEST['sx_owner0']) && ($_REQUEST['sx_owner0'] == 0)) {
            $show_owner_error = 1;
        } else {
            $show_owner_error = 0;
        }

        $this->context->smarty->assign(array(
            'action' => $this->context->link->getModuleLink($this->name, 'validation', array(), true),
            'show_iban_error' => $show_iban_error,
            'show_owner_error' => $show_owner_error,
        ));

        return $this->context->smarty->fetch('module:sxdebit/views/templates/front/payment_form.tpl');
    }

    protected function displaySepaInfo()
    {
        return $this->display(__FILE__, 'infos.tpl');
    }

    protected function postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('sepa_payment_days_1')) {
                $this->postErrors[] = $this->l('Payment period after the order are required.');
            } elseif (!Tools::getValue('sepa_id_creditor')) {
                $this->postErrors[] = $this->l('Creditor Identifier are required.');
            }
        }
    }

    protected function postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $sql_get_customer_group = "select id_group "
                . " from "._DB_PREFIX_."group_lang "
                . " where id_lang = '".$this->context->language->id."'";
            $query = Db::getInstance()->executeS($sql_get_customer_group);
            foreach ($query as $key) {
                $id_group = $key['id_group'];
                $value = Tools::getValue('sepa_payment_days_'.$id_group);
                Configuration::updateValue('SEPA_PAYMENT_DAYS_'.$id_group, $value);
            }
            Configuration::updateValue('SEPA_ID_CREDITOR', Tools::getValue('sepa_id_creditor'));
        }
        $this->html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Customization'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Creditor Identifier'),
                        'name' => 'sepa_id_creditor',
                        'desc' => $this->l('Together with the unique Mandate reference assigned by the direct
                                           debit creditor this Creditor Identifier is forwarded by the banking
                                           industry in the SEPA dataset all the way from the creditor to the debtor of
                                           the SEPA direct debit. Used in combination with the Creditor
                                           Identifier the unique Mandate reference makes it possible to
                                           identify a mandate clearly with the effect that when SEPA direct debit
                                           is presented to a debtor, he is able to check the
                                           effective existence of the mandate or alternativley that the
                                           Debtor Bank is able to offer him such a service optionaly.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save', 'Admin.Actions'),
                )
            ),
        );

        $sql_get_customer_group = "select id_group, name "
            . " from "._DB_PREFIX_."group_lang "
            . " where id_lang = '".$this->context->language->id."'";
        $query = Db::getInstance()->executeS($sql_get_customer_group);
        foreach ($query as $key) {
            $fields_form['form']['input'][] = array(
                'type' => 'text',
                'values' => '13',
                'label' => $this->l('Payment period after the order') . $this->l(', Group: ') . $key['name'],
                'desc' => $this->l('Customer Group: ') . $key['name'] . ' - '
                    . $this->l('Number of days where the payment will be started'),
                'name' => 'sepa_payment_days_'.$key['id_group'],
            );
        }

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
            .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form));
    }

    public function getConfigFieldsValues()
    {
        $ret = array(
            'sepa_payment_days' => Tools::getValue('sepa_payment_days', Configuration::get('SEPA_PAYMENT_DAYS')),
            'sepa_id_creditor' => Tools::getValue('sepa_id_creditor', Configuration::get('SEPA_ID_CREDITOR')),
        );

        $sql_get_customer_group = "select id_group "
            . " from "._DB_PREFIX_."group_lang "
            . " where id_lang = '".$this->context->language->id."'";
        $query = Db::getInstance()->executeS($sql_get_customer_group);
        foreach ($query as $key) {
            $id_group = $key['id_group'];
            $ret['sepa_payment_days_'.$id_group] = Configuration::get('SEPA_PAYMENT_DAYS_'.$id_group);
        }
        return $ret;
    }

    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit')) {
            $this->postValidation();
            if (!count($this->postErrors)) {
                $this->postProcess();
            } else {
                foreach ($this->postErrors as $err) {
                    $this->html .= $this->displayError($err);
                }
            }
        } else {
            $this->html .= '<br />';
        }

        $this->html .= $this->displaySepaInfo();
        $this->html .= $this->renderForm();

        return $this->html;
    }

    public function hookDisplayAdminOrder($params)
    {
        $order = new Order($params['id_order']);

        $sepa_details = "select iban, bic, owner, sepa_date, mandate
                        from "._DB_PREFIX_."order_sxdebit
                        where id_order = '".$order->id."'";
        $result = Db::getInstance()->executeS($sepa_details);
        if (count($result) != 1) {
            return;
        } else {
            $iban   = $result[0]['iban'];
            $bic    = $result[0]['bic'];
            $owner  = $result[0]['owner'];
            $sepa_date   = $result[0]['sepa_date'];
            $file   = $result[0]['mandate'];
        }

        $this->context->smarty->assign(array(
             'mandat_url'   => $file,
             'iban'         => $iban,
             'bic'          => $bic,
             'owner'        => $owner,
             'sepa_date'     => Tools::displayDate($sepa_date, $this->context->language->id, false),
        ));

        return $this->display(__FILE__, 'AdminOrder.tpl');
    }
}
