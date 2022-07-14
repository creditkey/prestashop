<?php
/**
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
 */

/**
 * Manages configurable module settings
 */
class AdminCreditKeySettingsController extends ModuleAdminController
{
    /**
     * AdminCardinalCommerceSettingsController constructor.
     */
    public function __construct()
    {
        $this->bootstrap                 = true;
        $this->page_header_toolbar_title = 'Credit Key Settings';
        
        parent::__construct();
        
        $this->initFieldsOptions();
    }
    
    /**
     * Initializes configuration options for module
     */
    private function initFieldsOptions()
    {
        $this->fields_options = [
            'setting' => [
                'title' => $this->l('Credit Key Settings'),
                
                'description' =>
                    $this->l('In order to use this module, required invoice phone number setting must be on'),
                'fields'      => [
                    'CK_PUBLIC_KEY'                    => [
                        'title' => $this->l('Public key'),
                        'type'  => 'text',
                    ],
                    'CK_SHARED_SECRET'                 => [
                        'title' => $this->l('Shared secret'),
                        'type'  => 'text',
                    ],
                    'CK_TEST_MODE'                     => [
                        'title' => $this->l('Test mode'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'CK_SHOW_PROMO_ON_PRODUCT'         => [
                        'title' => $this->l('Show promo message on product page'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'CK_PRODUCT_WIDGET_SELECTOR'       => [
                        'title'   => $this->l('CSS selector for promo on product pages'),
                        'type'    => 'text',
                        'default' => ''
                    ],
                    'CK_MINIMUM_PRODUCT_PRICE'         => [
                        'title' => $this->l('Minimum Product Price'),
                        'type'  => 'price'
                    ],
                    'CK_SHOW_PROMO_ON_CART'            => [
                        'title' => $this->l('Show promo message on cart page'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'CK_CART_CSS_SELECTOR'             => [
                        'title'   => $this->l('CSS selector for promo on cart page'),
                        'type'    => 'text',
                        'default' => ''
                    ],
                    'CK_CART_ALIGNMENT_FOR_DESKTOP'    => [
                        'title'      => $this->l('Cart Page Alignment for Desktop'),
                        'type'       => 'select',
                        'cast'       => 'pSQL',
                        'identifier' => 'id',
                        'list'       => [
                            [
                                'name' => 'Right',
                                'id'   => 'right',
                            ],
                            [
                                'name' => 'Center',
                                'id'   => 'center',
                            ],
                            [
                                'name' => 'Left',
                                'id'   => 'left',
                            ],
                        ]
                    ],
                    'CK_CART_ALIGNMENT_FOR_MOBILE'     => [
                        'title'      => $this->l('Cart Page Alignment for Mobile'),
                        'type'       => 'select',
                        'cast'       => 'pSQL',
                        'identifier' => 'id',
                        'list'       => [
                            [
                                'name' => 'Right',
                                'id'   => 'right',
                            ],
                            [
                                'name' => 'Center',
                                'id'   => 'center',
                            ],
                            [
                                'name' => 'Left',
                                'id'   => 'left',
                            ],
                        ]
                    ],
                    'CK_MINIMUM_CART_PRICE'            => [
                        'title' => $this->l('Minimum Cart Total'),
                        'type'  => 'price'
                    ],
                
                ],
                'submit'      => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }
    
    /**
     * Collect and save payment method selected options
     *
     * @param $paymentMethods
     */
    public function updateOptionCkPaymentMethod($paymentMethods)
    {
        if (false === $paymentMethods) {
            $paymentMethods = [];
        }
        
        Configuration::updateValue('CK_PAYMENT_METHOD', json_encode($paymentMethods));
    }
    
}
