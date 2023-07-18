{*
*  @author Saxtec <prestashop@saxtec.com>
*  @copyright  2007-2019 Saxtec
*  @license    Paid Licence
*}

<div style="font-size: 10px;">
	<table style="width: 100%">
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td style="width: 5%" rowspan="11">&nbsp;</td>
			<td style="width: 95%" colspan="2"><h2>{l s='SEPA Direct Debit Mandate' mod='sxdebit'}</h2>
				<strong>{l s='for SEPA Direct Debit Payment (one time)' mod='sxdebit'}</strong>
			</td>
		</tr>
		<tr>
			<td style="width: 95%" colspan="2"> &nbsp; 			</td>
		</tr>
		<tr>
			<td style="width: 95%" colspan="2"><strong>{l s='Creditor’s Name & adress' mod='sxdebit'}</strong></td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Creditor’s Name' mod='sxdebit'}:</td>
			<td style="width: 70%">{$shop_name|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Street name and number' mod='sxdebit'}:</td>
			<td style="width: 70%">{$shop_address1|escape:'htmlall':'UTF-8'}<br>{$shop_address2|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Postcode / City' mod='sxdebit'}:</td>
			<td style="width: 70%">{$shop_postcode|escape:'htmlall':'UTF-8'} {$shop_city|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Country' mod='sxdebit'}:</td>
			<td style="width: 70%">{$shop_country|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%"> </td>
			<td style="width: 70%"> &nbsp; </td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Creditor identifier' mod='sxdebit'}:</td>
			<td style="width: 70%">{$sepa_number|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Mandate reference' mod='sxdebit'}:</td>
			<td style="width: 70%">{$mandate_number|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>

	<table style="width: 100%">
		<tr>
			<td style="width: 5%" rowspan="5">&nbsp;</td>
			<td style="width: 95%" colspan="2"><strong>{l s='Order from' mod='sxdebit'} {$order_date|escape:'htmlall':'UTF-8'}</strong></td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Order reference' mod='sxdebit'}:</td>
			<td style="width: 70%">{$order_reference|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Order total' mod='sxdebit'}:</td>
			<td style="width: 70%">{$order_total|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Date of Payment' mod='sxdebit'}:</td>
			<td style="width: 70%">{$sepa_date|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>



	<table style="width: 100%">
		<tr>
			<td style="width: 5%">&nbsp;</td>
			<td style="width: 95%">
				{l s='By signing this mandate form, you authorise the creditor to send instructions to your bank to debit your account and your bank to debit your account in accordance with the instructions from the creditor' mod='sxdebit'}
			</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td style="width: 5%">&nbsp;</td>
			<td style="width: 95%">
				{l s='As part of your rights, you are entitled to a refund from your bank under the terms and conditions of your agreement with your bank. A refund must be claimed within 8 weeks starting from the date on which your account was debited.' mod='sxdebit'}
			</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td style="width: 5%" rowspan="8">&nbsp;</td>
			<td style="width: 25%">{l s='Name of debitor' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_owner|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Company' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_company|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Street name and number' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_address|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Postcode / City' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_postcode|escape:'htmlall':'UTF-8'} {$customer_city|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Country' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_country|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='E-Mail' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_email|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Swift BIC' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_bic|escape:'htmlall':'UTF-8'}</td>
		</tr>
		<tr>
			<td style="width: 25%">{l s='Account number-IBAN' mod='sxdebit'}:</td>
			<td style="width: 70%">{$customer_iban|escape:'htmlall':'UTF-8'}</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
	<table style="width: 100%">
		<tr>
			<td style="width: 5%">&nbsp;</td>
			<td style="width: 95%">{$customer_city|escape:'htmlall':'UTF-8'}, {$date_now|escape:'htmlall':'UTF-8'}, {$customer_owner|escape:'htmlall':'UTF-8'}</td>
		</tr>
	</table>

	<table style="width: 100%">
		<tr>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
	</table>
</div>
