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

class HTMLTemplateSEPA extends HTMLTemplate
{
    public $order;

    public function getBulkFilename()
    {
        return 'sepa_mandates.pdf';
    }
    public function getFilename()
    {
        return 'sepa_mandate.pdf';
    }

    public function __construct($sepa, $smarty)
    {
        $this->sepa = $sepa;
        $this->smarty = $smarty;

        $this->module = Module::getInstanceByName('sxdebit');

        $this->title = "SEPA Mandat";

        $this->order = new Order((int)$this->sepa->id_order);
        $this->customer = new Customer($this->order->id_customer);
        $this->address = new Address($this->order->id_address_invoice);
        $this->shop = new Shop((int)$this->order->id_shop);
    }

    /**
     * Returns the template's HTML content
     * @return string HTML content
     */
    public function getContent()
    {
        $this->smarty->assign(array(
            'customer_iban'           => $this->sepa->iban,
            'customer_bic'            => $this->sepa->bic,
            'customer_owner'          => $this->sepa->owner,

            'customer_email'          => $this->customer->email,

            'customer_company'        => $this->address->company,
            'customer_address'        => $this->address->address1,
            'customer_postcode'       => $this->address->postcode,
            'customer_city'           => $this->address->city,
            'customer_country'        => $this->address->country,

            'shop_name'           => Configuration::get('PS_SHOP_NAME', null, null, (int)$this->order->id_shop),
            'shop_address1'       => Configuration::get('PS_SHOP_ADDR1', null, null, (int)$this->order->id_shop),
            'shop_address2'       => Configuration::get('PS_SHOP_ADDR2', null, null, (int)$this->order->id_shop),
            'shop_postcode'       => Configuration::get('PS_SHOP_CODE', null, null, (int)$this->order->id_shop),
            'shop_city'           => Configuration::get('PS_SHOP_CITY', null, null, (int)$this->order->id_shop),
            'shop_country'        => Configuration::get('PS_SHOP_COUNTRY', null, null, (int)$this->order->id_shop),

            'mandate_number'      => "SEPA-".$this->order->reference,
            'sepa_number'         => Configuration::get('SEPA_ID_CREDITOR', null, null, (int)$this->order->id_shop),

            'order_reference'     => $this->order->reference,
            'order_date'          => Tools::displayDate($this->order->date_add),
            'sepa_date'           => Tools::displayDate(date("Y-m-d", strtotime(Configuration::get(
                'SEPA_PAYMENT_DAYS_'.$this->customer->id_default_group,
                null,
                null,
                (int)$this->order->id_shop
            ) . " days")), $this->order->id_lang, false),
            'order_total'         => Tools::displayPrice($this->order->total_paid),
            'date_now'            => Tools::displayDate(date('Y-m-d'), $this->order->id_lang, false),
        ));


        return $this->smarty->fetch($this->getTemplate('sepa_debit_mandat'));
    }

    protected function getTemplate($template_name)
    {
        $template = false;
        $default_template = _PS_PDF_DIR_.'/'.$template_name.'.tpl';
        $overriden_template = _PS_THEME_DIR_.'pdf/'.$template_name.'.tpl';
        $module_template = _PS_MODULE_DIR_.'sxdebit/views/templates/front/'.$template_name.'.tpl';

        if (file_exists($module_template)) {
            $template = $module_template;
        } elseif (file_exists($overriden_template)) {
            $template = $overriden_template;
        } elseif (file_exists($default_template)) {
            $template = $default_template;
        }

        return $template;
    }
}
