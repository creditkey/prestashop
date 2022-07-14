{**
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author INVERTUS UAB www.invertus.eu  <support@invertus.eu>
* @copyright CardinalCommerce
* @license   Addons PrestaShop license limitation
*}

<div class="row mb-1">
    {foreach from=$payment_method_images item=paymentOption}
        <img src="{$paymentOption['logo']|escape:'htmlall':'UTF-8'}" alt="{$paymentOption['name']|escape:'htmlall':'UTF-8'}" width="38">
    {/foreach}
</div>

{if $is_invoice_phone_set eq false}
    <div class="row">
        <p class="alert alert-danger">
            {l s="The billing address phone number is required to use this payment option" mod="cardinalcommerce"}
        </p>
    </div>
{/if}
