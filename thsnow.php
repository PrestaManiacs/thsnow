<?php
/**
 * 2006-2021 THECON SRL
 *
 * NOTICE OF LICENSE
 *
 * DISCLAIMER
 *
 * YOU ARE NOT ALLOWED TO REDISTRIBUTE OR RESELL THIS FILE OR ANY OTHER FILE
 * USED BY THIS MODULE.
 *
 * @author    THECON SRL <contact@thecon.ro>
 * @copyright 2006-2021 THECON SRL
 * @license   Commercial
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Thsnow extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'thsnow';
        $this->tab = 'administration';
        $this->version = '1.1.0';
        $this->author = 'Thecon';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Snow effect');
        $this->description = $this->l('Add a snowing effect on your website.');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */

    public function install()
    {
        if (!parent::install() || !$this->registerHooks() || ! $this->installDemo()) {
            return false;
        }

        return true;
    }

    private function installDemo()
    {
        Configuration::updateValue('THSNOW_LIVE_MODE', false);
        Configuration::updateValue('THSNOW_FLAKE_ROTATION', true);
        Configuration::updateValue('THSNOW_FLAKE_WIND', true);
        Configuration::updateValue('THSNOW_FLAKE_COUNT', 50);
        Configuration::updateValue('THSNOW_FLAKE_COLOR', '#5ECDEF');
        Configuration::updateValue('THSNOW_FLAKE_MIN_OPACITY', 0.1);
        Configuration::updateValue('THSNOW_FLAKE_MAX_OPACITY', 0.95);
        Configuration::updateValue('THSNOW_FLAKE_MIN_SIZE', 2);
        Configuration::updateValue('THSNOW_FLAKE_MAX_SIZE', 5);
        Configuration::updateValue('THSNOW_FLAKE_ZINDEX', 9999);
        Configuration::updateValue('THSNOW_FLAKE_SPEED', 2);

        return true;
    }

    public function registerHooks()
    {
        if (!$this->registerHook('header') ||
            !$this->registerHook('actionFrontControllerSetMedia') ||
            !$this->registerHook('actionAdminControllerSetMedia')) {
            return false;
        }

        return true;
    }

    public function uninstall()
    {
        $form_values = $this->getConfigFormValues();
        foreach (array_keys($form_values) as $key) {
            Configuration::deleteByName($key);
        }

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $message = '';
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitThsnowModule')) == true) {
            $this->postProcess();

            if (count($this->_errors)) {
                $message = $this->displayError($this->_errors);
            } else {
                $message = $this->displayConfirmation($this->l('Successfully saved!'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $message.$output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
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
        $helper->submit_action = 'submitThsnowModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'THSNOW_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'th_title',
                        'name' => 'Schedule the Snowing Effect'
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('Date From:'),
                        'name' => 'THSNOW_DATE_FROM',
                    ),
                    array(
                        'type' => 'datetime',
                        'label' => $this->l('Date To:'),
                        'name' => 'THSNOW_DATE_TO',
                    ),
                    array(
                        'type' => 'th_title',
                        'name' => 'Flakes Customization'
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Rotation'),
                        'name' => 'THSNOW_FLAKE_ROTATION',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Wind'),
                        'name' => 'THSNOW_FLAKE_WIND',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'type' => 'color',
                        'label' => 'Color:',
                        'name' => 'THSNOW_FLAKE_COLOR',
                        'desc' => 'Default value: #5ECDEF'
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Count:',
                        'name' => 'THSNOW_FLAKE_COUNT',
                        'desc' => 'Default value: 50',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Min opacity:',
                        'name' => 'THSNOW_FLAKE_MIN_OPACITY',
                        'desc' => 'Default value: 0.1. From 0.1 to 1',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Max opacity:',
                        'name' => 'THSNOW_FLAKE_MAX_OPACITY',
                        'desc' => 'Default value: 1. From 0.1 to 1',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Min size:',
                        'name' => 'THSNOW_FLAKE_MIN_SIZE',
                        'desc' => 'Default value: 2',
                        'suffix' => 'px',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Max size:',
                        'name' => 'THSNOW_FLAKE_MAX_SIZE',
                        'desc' => 'Default value: 5',
                        'suffix' => 'px',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'Speed',
                        'name' => 'THSNOW_FLAKE_SPEED',
                        'desc' => 'Default value: 2',
                        'col' => 3
                    ),
                    array(
                        'type' => 'text',
                        'label' => 'zIndex',
                        'name' => 'THSNOW_FLAKE_ZINDEX',
                        'desc' => 'Default value: 9999',
                        'col' => 3
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'THSNOW_LIVE_MODE' => Tools::getValue('THSNOW_LIVE_MODE', Configuration::get('THSNOW_LIVE_MODE')),
            'THSNOW_FLAKE_COLOR' => Tools::getValue('THSNOW_FLAKE_COLOR', Configuration::get('THSNOW_FLAKE_COLOR')),
            'THSNOW_DATE_FROM' => Tools::getValue('THSNOW_DATE_FROM', Configuration::get('THSNOW_DATE_FROM')),
            'THSNOW_DATE_TO' => Tools::getValue('THSNOW_DATE_TO', Configuration::get('THSNOW_DATE_TO')),
            'THSNOW_FLAKE_ROTATION' => Tools::getValue('THSNOW_FLAKE_ROTATION', Configuration::get('THSNOW_FLAKE_ROTATION')),
            'THSNOW_FLAKE_WIND' => Tools::getValue('THSNOW_FLAKE_WIND', Configuration::get('THSNOW_FLAKE_WIND')),
            'THSNOW_FLAKE_COUNT' => Tools::getValue('THSNOW_FLAKE_COUNT', Configuration::get('THSNOW_FLAKE_COUNT')),
            'THSNOW_FLAKE_MIN_OPACITY' => Tools::getValue('THSNOW_FLAKE_MIN_OPACITY', Configuration::get('THSNOW_FLAKE_MIN_OPACITY')),
            'THSNOW_FLAKE_MAX_OPACITY' => Tools::getValue('THSNOW_FLAKE_MAX_OPACITY', Configuration::get('THSNOW_FLAKE_MAX_OPACITY')),
            'THSNOW_FLAKE_MIN_SIZE' => Tools::getValue('THSNOW_FLAKE_MIN_SIZE', Configuration::get('THSNOW_FLAKE_MIN_SIZE')),
            'THSNOW_FLAKE_MAX_SIZE' => Tools::getValue('THSNOW_FLAKE_MAX_SIZE', Configuration::get('THSNOW_FLAKE_MAX_SIZE')),
            'THSNOW_FLAKE_SPEED' => Tools::getValue('THSNOW_FLAKE_SPEED', Configuration::get('THSNOW_FLAKE_SPEED')),
            'THSNOW_FLAKE_ZINDEX' => Tools::getValue('THSNOW_FLAKE_ZINDEX', Configuration::get('THSNOW_FLAKE_ZINDEX')),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        if (Tools::getValue('THSNOW_DATE_FROM') && Tools::getValue('THSNOW_DATE_TO') &&
            Tools::getValue('THSNOW_DATE_FROM') > Tools::getValue('THSNOW_DATE_TO')) {
            $this->_errors[] = $this->l('The end date cannot be lower then start date!');
            return false;
        }

        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }

        return true;
    }

    public function hookActionAdminControllerSetMedia()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookActionFrontControllerSetMedia()
    {
        if (!$this->isVisible()) {
            return false;
        }

        $this->context->controller->addJS($this->_path.'/views/js/snowflakes.min.js');
        $this->context->controller->addJS($this->_path.'/views/js/front.js');

        $prop = array();
        if ($temp = Configuration::get('THSNOW_FLAKE_ZINDEX')) {
            $prop['zIndex'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_COLOR')) {
            $prop['color'] = $temp;
        }

        $prop['wind'] = Configuration::get('THSNOW_FLAKE_WIND');
        $prop['rotation'] = Configuration::get('THSNOW_FLAKE_ROTATION');

        if ($temp = Configuration::get('THSNOW_FLAKE_COUNT')) {
            $prop['count'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_MIN_OPACITY')) {
            $prop['minOpacity'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_MAX_OPACITY')) {
            $prop['maxOpacity'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_MIN_SIZE')) {
            $prop['minSize'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_MAX_SIZE')) {
            $prop['maxSize'] = $temp;
        }

        if ($temp = Configuration::get('THSNOW_FLAKE_SPEED')) {
            $prop['speed'] = $temp;
        }

        Media::addJsDef(array(
            'THSNOW_FLAKE_PROP' => $prop,
        ));

        return true;
    }

    public function hookHeader()
    {
        if (!$this->isVisible()) {
            return false;
        }

        return $this->context->smarty->fetch(_PS_MODULE_DIR_.$this->name.'/views/templates/front/snow.tpl');
    }

    public function isVisible()
    {
        $current_date = date('Y-m-d H:i:s');
        if (!Configuration::get('THSNOW_LIVE_MODE') ||
            (Configuration::get('THSNOW_DATE_FROM') &&
                Configuration::get('THSNOW_DATE_TO') &&
                ($current_date < Configuration::get('THSNOW_DATE_FROM') || $current_date > Configuration::get('THSNOW_DATE_TO')))) {
            return false;
        }

        return true;
    }
}
