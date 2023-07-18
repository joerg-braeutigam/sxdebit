{*
*  @author Saxtec <prestashop@saxtec.com>
*  @copyright  2007-2019 Saxtec
*  @license    Paid Licence
*}
    {if $show_iban_error == 1}
        <div class="help-block">
        <ul>
             <li class="alert alert-danger">{l s='IBAN not valid' mod='sxdebit'}</li>
        </ul>
        </div>
    {/if}
    {if $show_owner_error == 1}
        <div class="help-block">
        <ul>
             <li class="alert alert-danger">{l s='You have to insert the bank owner' mod='sxdebit'}</li>
        </ul>
        </div>
    {/if}
<form action="{$action|escape:'htmlall':'UTF-8'}" id="payment-form" method="post" class="additional-information">
  <p>
    <label>{l s='IBAN' mod='sxdebit'}</label>
    <input type="text" class="form-control" name="iban">
  </p>
  <p>
    <label>{l s='BIC' mod='sxdebit'}</label>
    <input type="text" class="form-control" autocomplete="off" name="bic"> optional
  </p>

  <p>
    <label>{l s='Bank Owner' mod='sxdebit'}</label>
    <input type="text" class="form-control" name="owner">
  </p>

  <p>
    <label>{l s='Ich erteile hiermit ein SEPA Lastschrift Mandat. Dieses wird als PDF zugestellt.' mod='sxdebit'}</label>
  </p>

</form>
