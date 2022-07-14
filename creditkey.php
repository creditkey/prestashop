<?php

class CreditKey extends PaymentModule
{
    
    private $api_url;
    private $public_key;
    private $shared_secret;
    private $test_mode;
    
    public function __construct()
    {
        $this->name          = 'creditkey';
        $this->tab           = 'payments_gateways';
        $this->version       = '1.0.0';
        $this->author        = 'OnePix';
        $this->controllers   = array('validation');
        $this->bootstrap     = true;
        $this->displayName   = $this->l('Credit Key');
        $this->description   = $this->l('Payment Gateway by Credit Key');
        $this->test_mode     = Configuration::get('CK_TEST_MODE');
        $this->public_key    = Configuration::get('CK_PUBLIC_KEY');
        $this->shared_secret = Configuration::get('CK_SHARED_SECRET');
        $this->api_url       = $this->test_mode == 1 ? 'https://staging.creditkey.com/app' : 'https://www.creditkey.com/app';
        
        parent::__construct();
        
        if ( ! count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->l('No currency has been set for this module.');
        }
        
        require_once $this->getLocalPath() . 'vendor/autoload.php';
        
        CreditKey\Api::configure($this->api_url, $this->public_key, $this->shared_secret);
    }
    
    /**
     * Provide PrestaShop with new payment options that are supported by module
     *
     * @param array $params
     *
     * @return PaymentOption[]
     */
    public function hookPaymentOptions(array $params)
    {
        if ( ! $this->isDisplayedInCheckout() ||
             ! $this->checkCurrency() ||
             ! $this->checkCountry()) {
            return;
        }
        
        $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paymentOption
            ->setModuleName($this->name)
            ->setCallToActionText($this->l('Credit Key  '))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/assets/img/ck_50.png'));
        
        return [$paymentOption];
    }
    
    /**
     * Install module, create database tables and register hooks
     *
     * @return bool
     */
    public function install()
    {
        if ( ! parent::install()) {
            return false;
        }
        
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            
            return false;
        }
        
        require(dirname(__FILE__) . '/sql/install.php');
        
        return $this->registerHook('paymentOptions') &&
               $this->registerHook('header') &&
               $this->registerHook('actionFrontControllerSetMedia') &&
               $this->registerHook('actionOrderSlipAdd') &&
               $this->registerHook('actionOrderStatusUpdate');
    }
    
    /**
     * Redirect to module configuration in Back Office
     */
    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCreditKeySettings'));
    }
    
    public function getOrderData($cart_id = null)
    {
        $cart       = $cart_id == null ? $this->context->cart : new Cart($cart_id);
        $customer   = $cart_id == null ? $this->context->customer : new Customer($cart->id_customer);
        $order_id   = Order::getIdByCartId((int)$cart->id);
        $order      = new Order($order_id);
        $address    = new Address(intval($cart->id_address_delivery));
        $cart_items = array();
        
        $ck_address = new CreditKey\Models\Address(
            $address->firstname,
            $address->lastname,
            $address->company,
            $customer->email,
            $address->address1,
            $address->address2,
            $address->city,
            $address->id_state,
            $address->postcode,
            $address->phone
        );
        
        foreach ($order->getProducts() as $product) {
            $quantity = empty($product['product_quantity_refunded']) ? $product['product_quantity'] : $product['product_quantity'] - $product['product_quantity_refunded'];
            
            $cart_items[] = new CreditKey\Models\CartItem(
                $product['id_product'],
                $product['product_name'],
                $product['price'],
                $product['reference'],
                $quantity,
                null,
                null
            );
        }
        
        $charges = $this->getCartCharges($cart);
        
        return array(
            'order_items'      => $cart_items,
            'billing_address'  => $ck_address,
            'shipping_address' => $ck_address,
            'charges'          => $charges,
            'order_id'         => $order_id,
        );
    }
    
    public function getCartCharges(CartCore $cart)
    {
        $totals = $cart->getSummaryDetails();
        
        $sub_total       = number_format($totals['total_tax'], 2, '.', '');
        $shipping_price  = number_format($totals['total_shipping'], 2, '.', '');
        $tax_price       = number_format($totals['total_price_without_tax'], 2, '.', '');
        $total_price     = number_format($totals['total_price'], 2, '.', '');
        $total_discounts = number_format($totals['total_discounts'], 2, '.', '');
        
        return new CreditKey\Models\Charges($sub_total, $shipping_price, $tax_price, $total_discounts, $total_price);
    }
    
    public function checkCurrency()
    {
        global $cookie;
        $currency = new CurrencyCore($cookie->id_currency);
        
        return $currency->iso_code == 'USD';
    }
    
    public function checkCountry()
    {
        $cart        = $this->context->cart;
        $address     = new Address(intval($cart->id_address_delivery));
        $country_iso = Country::getIsoById($address->id_country);
        
        return $country_iso == 'US';
    }
    
    public function getRedirectURL()
    {
        $orderData = $this->getOrderData();
        
        return CreditKey\Checkout::beginCheckout(
            $orderData['order_items'],
            $orderData['billing_address'],
            $orderData['shipping_address'],
            $orderData['charges'],
            $orderData['order_id'],
            $this->context->customer->id,
            $this->context->link->getModuleLink($this->name, 'orderCompleting', [], true) . '?order_id=' . $orderData['order_id'] . '&id=%CKKEY%',
            $this->context->link->getBaseLink(),
            'redirect'
        );
    }
    
    public function isDisplayedInCheckout()
    {
        $credit_key_data = $this->getOrderData();
        
        return CreditKey\Checkout::isDisplayedInCheckout($credit_key_data['order_items'], 0);
    }
    
    public function hookActionFrontControllerSetMedia($params)
    {
        $isProduct = 'product' === $this->context->controller->php_self;
        $isCart    = 'cart' === $this->context->controller->php_self;
        $data      = array(
            'public_key' => $this->public_key,
            'mode'       => $this->test_mode == 1 ? 'staging' : 'production',
        
        );
        
        if ($isProduct) {
            $product = new Product(Tools::getValue('id_product'));
            
            $data['product_widget_selector'] = Configuration::get('CK_PRODUCT_WIDGET_SELECTOR');
            $data['minimum_product_price']   = Configuration::get('CK_MINIMUM_PRODUCT_PRICE');
            $data['show_promo_on_product']   = Configuration::get('CK_SHOW_PROMO_ON_PRODUCT');
            $data['price']                   = $product->getPrice();
        }
        
        if ($isCart) {
            $cart = $this->context->cart;
            
            $data['cart_css_selector']     = Configuration::get('CK_CART_CSS_SELECTOR');
            $data['minimum_cart_total']    = Configuration::get('CK_MINIMUM_CART_PRICE');
            $data['show_promo_on_cart']    = Configuration::get('CK_SHOW_PROMO_ON_CART');
            $data['alignment_for_desktop'] = Configuration::get('CK_CART_ALIGNMENT_FOR_DESKTOP');
            $data['alignment_for_mobile']  = Configuration::get('CK_CART_ALIGNMENT_FOR_MOBILE');
            $data['total']                 = $cart->getOrderTotal();
        }
        
        if ($isProduct || $isCart) {
            
            $script = $isCart ? 'cartWidget' : 'productWidget';
            
            $this->context->controller->registerJavascript(
                'creditkey-js',
                'https://unpkg.com/@credit-key/creditkey-js@latest/umd/creditkey-js.js',
                ['position' => 'bottom', 'priority' => 150, 'server' => 'remote']
            );
            
            $this->context->controller->registerJavascript(
                'creditkey-cartWidget-js',
                'modules/' . $this->name . '/views/js/' . $script . '.js',
                ['position' => 'bottom', 'priority' => 200]
            );
            
            Media::addJsDef([
                'creditkey' => $data,
            ]);
        }
    }
    
    function hookActionOrderSlipAdd($params)
    {
        /**
         * Process only if the module is active and order was paid via this module
         */
        if ( ! $this->active) {
            return;
        }
        
        $order           = $params['order'];
        $credit_key_data = $this->getOrderData($order->id_cart);
        
        if ($this->name != $order->module) {
            return;
        }
        
        $returned_status = Db::getInstance()->getValue('SELECT `refund_amount` FROM ' . _DB_PREFIX_ . 'credit_key WHERE `id_order`=' . $order->id, 1);
        
        if ($returned_status == 'returned') {
            return;
        }
        
        $transaction_id = Db::getInstance()->getValue('SELECT `transaction_id` FROM ' . _DB_PREFIX_ . 'credit_key WHERE `id_order`=' . $order->id, 1);
        $refund_amount  = 0;
        
        foreach ($params['productList'] as $product) {
            $refund_amount += $product['amount'];
        }
        
        if (isset($_POST['cancel_product']['shipping_amount'])) {
            $refund_amount += $_POST['cancel_product']['shipping_amount'];
        }
        
        try {
            $refund_response = CreditKey\Orders::refund(
                $transaction_id,
                $refund_amount
            );
            
            if ($refund_response->getStatus() == 'returned') {
                Db::getInstance()->update('credit_key',
                    array(
                        'refund_status' => 'returned',
                    ), '`id_cart`=' . (int)$order->id_cart);
                
                return;
            }
            
            Db::getInstance()->update('credit_key',
                array(
                    'refund_amount' => $refund_amount,
                ), '`id_cart`=' . (int)$order->id_cart);
            
            $qtyList     = [];
            $order_items = [];
            
            foreach ($order->getProducts() as $product) {
                if (isset($params['qtyList'][$product['id_order_detail']])) {
                    $qtyList[$product['id_product']] = $params['qtyList'][$product['id_order_detail']];
                }
            }
            
            
            foreach ($refund_response->getItems() as $item) {
                $quantity = isset($qtyList[$item->getMerchantId()]) ? $item->getQuantity() - $qtyList[$item->getMerchantId()] : $item->getQuantity();
                
                $order_items[] = new CreditKey\Models\CartItem(
                    $item->getMerchantId(),
                    $item->getName(),
                    $item->getPrice(),
                    $item->getSku(),
                    $quantity,
                    null,
                    null
                );
            }
            
            sleep(2);
            
            CreditKey\Orders::update($transaction_id, null, strval($order->id), $order_items, null, null);
            
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
    }
    
    function hookActionOrderStatusUpdate($params)
    {
        /**
         * Process only if the module is active and order was paid via this module
         */
        if ( ! $this->active) {
            return;
        }
        
        $order           = new Order($params['id_order']);
        $credit_key_data = $this->getOrderData($order->id_cart);
        $transaction_id  = Db::getInstance()->getValue('SELECT `transaction_id` FROM ' . _DB_PREFIX_ . 'credit_key WHERE `id_order`=' . $order->id, 1);
        
        try {
            $status = $params['newOrderStatus']->name;
            
            if ($params['newOrderStatus']->id == _PS_OS_REFUND_) {
                $status = 'refunded';
            }
            
            if ($params['newOrderStatus']->id == _PS_OS_SHIPPING_) {
                $status = 'shipped';
                CreditKey\Orders::confirm($transaction_id, strval($params['id_order']), $status, $credit_key_data['order_items'], $credit_key_data['charges']);
                
                Db::getInstance()->update('credit_key',
                    array(
                        'confirmed' => 1,
                    ), '`id_cart`=' . (int)$order->id_cart);
            }
            
            if ($params['newOrderStatus']->id == _PS_OS_CANCELED_) {
                $status = 'canceled';
                
                CreditKey\Orders::cancel($transaction_id);
                
                Db::getInstance()->update('credit_key',
                    array(
                        'cancel_status' => 1,
                    ), '`id_cart`=' . (int)$order->id_cart);
            }
            
            CreditKey\Orders::update($transaction_id, $status, strval($params['id_order']), $credit_key_data['order_items'], $credit_key_data['charges'], $credit_key_data['shipping_address']);
            
        } catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
        }
        
    }
}