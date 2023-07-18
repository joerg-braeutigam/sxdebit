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

class SxDebitValidationModuleFrontController extends ModuleFrontController
{
    /**
     * @see FrontController::postProcess()
     */

    private function validateIban($iban = null)
    {
        $iban = Tools::strtolower(str_replace(' ', '', $iban));
        $tmp = '';

        $Countries = array(
            'al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,
            'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,
            'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,
            'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,
            'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,
            'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24 );
        $Chars = array(
            'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,
            'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35
        );

        if (Tools::strlen($iban) != $Countries[ Tools::substr($iban, 0, 2) ]) {
            return false;
        }

        $MovedChar = Tools::substr($iban, 4) . Tools::substr($iban, 0, 4);
        $MovedCharArray = str_split($MovedChar);
        $NewString = "";

        foreach ($MovedCharArray as $k => $v) {
            if (!is_numeric($MovedCharArray[$k])) {
                $MovedCharArray[$k] = $Chars[$MovedCharArray[$k]];
            }
            $NewString .= $MovedCharArray[$k];
            $tmp .= $v;
        }

        $mod = $tmp;
        $x = $NewString;
        $y = "97";
        $take = 5;
        $mod = "";

        do {
            $a = (int)$mod . Tools::substr($x, 0, $take);
            $x = Tools::substr($x, $take);
            $mod = $a % $y;
        } while (Tools::strlen($x));

        return (int)$mod == 1;
    }

    private function validateBic($bic = null)
    {
        $result = preg_match('#^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$#', $bic);
        return $result;
    }

    public function postProcess()
    {
        $cart = $this->context->cart;

        // here can you add a score mechanism
        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'sxdebit') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }

        // $_REQUEST beinhaltet die Nutzdaten
        if ($this->validateIban($_REQUEST['iban'])) {
            if (Tools::strlen($_REQUEST['owner']) > 5) {
                $this->iban =  $_REQUEST['iban'];
                $this->bic =   $_REQUEST['bic'];
                $this->owner = $_REQUEST['owner'];

                $order_state_id = Configuration::get('SEPA_ID_ORDER_STATE');

                $customer = new Customer($cart->id_customer);
                if (!Validate::isLoadedObject($customer)) {
                    Tools::redirect('index.php?controller=order&step=1');
                }

                $currency = $this->context->currency;
                $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

                $this->module->validateOrder(
                    $cart->id,
                    $order_state_id,
                    $total,
                    $this->module->displayName,
                    null,
                    null,
                    (int)$currency->id,
                    false,
                    $customer->secure_key
                );
                $id_order = $this->module->currentOrder;

                // customer group -> id_default_group
                $pay_date = date("Y-m-d", strtotime(Configuration::get(
                    'SEPA_PAYMENT_DAYS_'.$customer->id_default_group,
                    null,
                    null,
                    (int)$cart->id_shop
                ) . " days"));

                $mandat = $this->createMandate();

                Db::getInstance()->execute("insert into "._DB_PREFIX_."order_sxdebit values ('"
                                           .(int)$id_order."', '".pSQL($this->iban)."',
                                           '".pSQL($this->bic)."', '".pSQL($this->owner)."',
                                           '".pSQL($pay_date)."', '".pSQL($mandat)."')");

                Tools::redirect('index.php?controller=order-confirmation&id_cart='
                                .$cart->id.'&id_module='.$this->module->id
                                .'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
            } else {
                Tools::redirect('index.php?controller=order&sx_owner0=0&step=1');
            }
        } else {
            if (Tools::strlen($_REQUEST['owner']) > 5) {
                Tools::redirect('index.php?controller=order&sx_ib0=0&step=1');
            } else {
                Tools::redirect('index.php?controller=order&sx_ib0=0&sx_owner0=0&step=1');
            }
        }
    }

    public function createContentMandate()
    {
        require_once(_PS_ROOT_DIR_.'/modules/sxdebit/classes/HTMLTemplateSEPA.php');
        $sepa = new stdClass();

        $sepa->id_order = $this->module->currentOrder;
        $sepa->iban = $this->iban;
        $sepa->bic = $this->bic;
        $sepa->owner = $this->owner;

        $pdf = new PDF($sepa, 'SEPA', $this->context->smarty);
        return $pdf->render(false);
    }

    public function createMandate()
    {
        // $cart = $this->context->cart;
        $order = new Order($this->module->currentOrder);
        $customer = new Customer($order->id_customer);
        $email = $customer->email;
        $name = $customer->firstname . " " . $customer->lastname;
        $id_language = $order->id_lang;

        $pdf_content = $this->createContentMandate();

        // save pdf local
        $datei = '/modules/sxdebit/mandates/'
                .Tools::strtolower($order->reference)
                .'_'.Tools::strtolower($order->secure_key)
                .'.pdf';
        $fh = fopen(_PS_ROOT_DIR_.$datei, 'w');
        fwrite($fh, $pdf_content);
        fclose($fh);

        // send pdf
        if (Configuration::get('PS_SSL_ENABLED') == 1) {
            $url = "https://";
        } else {
            $url = "http://";
        }
        $url .= $_SERVER['SERVER_NAME'] . $datei;

        $tpl_vars = array(
            '{firstname}'    => $customer->firstname,
            '{lastname}'     => $customer->lastname,
            '{order_name}'   => $order->getUniqReference(),
            '{file}'         => $url,
        );

        $file_attachment = array();
        $file_attachment['content'] = $pdf_content;
        $file_attachment['name']    = $this->l('SEPA-Mandate') . "-" . $order->reference.'.pdf';
        $file_attachment['mime']    = 'application/pdf';

        $template = "sepa_mandat";

        // send pdf mail
        Mail::Send(
            $id_language,
            $template,
            Mail::l('Your SEPA Mandate', $id_language),
            $tpl_vars,
            $email,
            $name,
            null,
            null,
            $file_attachment,
            null,
            _PS_ROOT_DIR_.'/modules/sxdebit/mails/'
        );

        return $datei;
    }
}
