<?php

/**
 * 2007-2025 PrestaShop.
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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
include dirname(__FILE__) . '/sql/energyclasssql.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Energylabeleu extends Module
{
    /**
     * Labels for energy class from A to G and N/A.
     *
     * @var array
     */
    protected $energy_class = ['N/A', 'A', 'B', 'C', 'D', 'E', 'F', 'G'];

    public function __construct()
    {
        $this->name = 'energylabeleu';
        $this->tab = 'content_management';
        $this->version = '1.0.0';
        $this->author = 'Michał Przysiężny';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Energy Label EU');
        $this->description = $this->l('Show Energy Efficiency on Page');

        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    public function install()
    {
        Configuration::updateValue('ENERGYLABELEU_STYLE_NAME', 'energylabel');
        Configuration::updateValue('ENERGY_WIDHT', 100);
        Configuration::updateValue('ENERGY_HEIGHT', 100);

        include dirname(__FILE__) . '/sql/install.php';

        return parent::install()
            && $this->registerHook('displayEnergyInfo')
            && $this->registerHook('displayProductAdditionalInfo')
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('actionProductUpdate')
            && $this->registerHook('actionProductSave')
            && $this->registerHook('actionProductDelete');
    }

    public function uninstall()
    {
        Configuration::deleteByName('ENERGYLABELEU_STYLE_NAME');
        Configuration::deleteByName('ENERGY_WIDHT');
        Configuration::deleteByName('ENERGY_HEIGHT');

        include dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall();
    }

    public function getContent()
    {
        /*
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitEnergylabeleuModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Add Extra field in product form in Admin Panel.
     */
    public function hookDisplayAdminProductsExtra($params)
    {
        $getClassEnergy = EnergyClassSql::getEnergyClass($params['id_product']);
        $energyClass = $getClassEnergy ? $getClassEnergy[0]['class'] : 'N/A';

        $this->context->smarty->assign([
            'energy_class' => $energyClass,
            'energy_classes' => $this->energy_class,
        ]);

        return $this->display(__FILE__, 'views/templates/admin/product.tpl');
    }

    /**
     * Custom hook to display energy label on product page.
     */
    public function hookDisplayEnergyInfo($params)
    {
        return $this->showEnergyLabel($params);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        return $this->showEnergyLabel($params);
    }

    /**
     * Function to display energy label on product page.
     */
    public function showEnergyLabel($params)
    {
        $product = $params['product'];
        $id_product = $product->id;

        $width = Configuration::get('ENERGY_WIDHT');
        $height = Configuration::get('ENERGY_HEIGHT');
        $css = Configuration::get('ENERGYLABELEU_STYLE_NAME');

        $getClassEnergy = EnergyClassSql::getEnergyClass($id_product);
        if ($getClassEnergy) {
            $energyClass = $getClassEnergy[0]['class'];
            $this->context->smarty->assign([
                'energy_class' => strtolower($energyClass),
                'width' => $width,
                'height' => $height,
                'css' => $css,
            ]);

            return $this->display(__FILE__, 'views/templates/front/product.tpl');
        }

        return '';
    }

    /**
     * Hook to update energy class in database
     * when product is updated.
     *
     * @param mixed $params id_product from params
     *
     * @return void
     */
    public function hookActionProductUpdate($params)
    {
        $productId = (int) $params['id_product'];

        $energyClass = Tools::getValue('energy_class', 'N/A');

        EnergyClassSql::deleteEnergyClass($productId);

        if (!empty($energyClass) && 'N/A' !== $energyClass) {
            EnergyClassSql::insertEnergyClass($productId, $energyClass);
        }
    }

    /**
     * Hook to save energy class in database when product is saved.
     *
     * @param mixed $params from params
     *
     * @return void
     */
    public function hookActionProductSave($params)
    {
        $productId = (int) $params['id_product'];

        $energyClass = Tools::getValue('energy_class', 'N/A');

        EnergyClassSql::deleteEnergyClass($productId);

        if (!empty($energyClass) && 'N/A' !== $energyClass) {
            EnergyClassSql::insertEnergyClass($productId, $energyClass);
        }
    }

    /**
     * Function to delete energy class for product from database.
     *
     * @return void
     */
    public function hookActionProductDelete($params)
    {
        $productId = (int) $params['id_product'];

        EnergyClassSql::deleteEnergyClass($productId);
    }

    /**
     * Form to configure module settings.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitEnergylabeleuModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Structure of form to configure module settings.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-css3"></i>',
                        'desc' => $this->l('Enter a name of CSS class'),
                        'name' => 'ENERGYLABELEU_STYLE_NAME',
                        'label' => $this->l('CSS Class'),
                    ],
                    [
                        'type' => 'text',
                        'desc' => $this->l('Type widht of Energy Label'),
                        'name' => 'ENERGY_WIDHT',
                        'label' => $this->l('Width'),
                    ],
                    [
                        'type' => 'text',
                        'desc' => $this->l('Type height of Energy Label'),
                        'name' => 'ENERGY_HEIGHT',
                        'label' => $this->l('Height'),
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Get values from form.
     *
     * @return array{ENERGYLABELEU_STYLE_NAME: mixed, ENERGY_HEIGHT: mixed, ENERGY_WIDHT: mixed}
     */
    protected function getConfigFormValues()
    {
        return [
            'ENERGYLABELEU_STYLE_NAME' => Configuration::get('ENERGYLABELEU_STYLE_NAME', null, null, null, 'energylabel'),
            'ENERGY_WIDHT' => Configuration::get('ENERGY_WIDHT'),
            'ENERGY_HEIGHT' => Configuration::get('ENERGY_HEIGHT'),
        ];
    }

    /**
     * Process form values.
     *
     * @return void
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }
}
