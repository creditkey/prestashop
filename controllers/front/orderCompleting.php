<?php
/*
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */
class CreditKeyOrderCompletingModuleFrontController extends ModuleFrontController
{
    private $order;
    
    /**
     * @see FrontController::postProcess()
     */
    public function postProcess()
    {
        $order_id       = Tools::getValue('order_id');
        $transaction_id = Tools::getValue('id');
        
        $complete_checkout = CreditKey\Checkout::completeCheckout($transaction_id);
        
        if ($complete_checkout) {
            $order           = new Order($order_id);
            $credit_key_data = $this->module->getOrderData($order->id_cart);
            
            $order->setCurrentState(_PS_OS_PREPARATION_);
            
            CreditKey\Orders::update($transaction_id, 'processing', strval($order_id), $credit_key_data['order_items'], $credit_key_data['charges'], $credit_key_data['shipping_address']);
            
            Db::getInstance()->insert('credit_key', array(
                'id_cart'        => (int)$order->id_cart,
                'id_order'       => (int)$order_id,
                'transaction_id' => $transaction_id,
                'grand_total'    => $credit_key_data['charges']->getGrandTotal(),
            ));
            
            Tools::redirect($this->context->link->getPageLink(
                'order-confirmation',
                true,
                $this->context->language->id,
                [
                    'id_cart'   => $order->id_cart,
                    'id_module' => $this->module->id,
                    'id_order'  => $order->id,
                    'key'       => $order->getCustomer()->secure_key,
                ]
            ));
        }
        
    }
}