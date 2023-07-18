{*
*  @author Saxtec <prestashop@saxtec.com>
*  @copyright  2007-2019 Saxtec
*  @license    Paid Licence
*}
<div id="SepaDebit" class="panel">
    <div class="panel-heading">
        <i class="icon-university"></i>
        {l s='Sepa Debit Payment' mod='sxdebit'}
    </div>
    <div class="input-group">
        <strong>{l s='IBAN:' mod='sxdebit'} </strong> {$iban|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='BIC:' mod='sxdebit'} </strong> {$bic|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Owner:' mod='sxdebit'} </strong> {$owner|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Day to make payment:' mod='sxdebit'} </strong> {$sepa_date|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Mandate:' mod='sxdebit'} </strong> <a href="{$mandat_url|escape:'htmlall':'UTF-8'}" target="_new">Download</a>
    </div>
</div>
