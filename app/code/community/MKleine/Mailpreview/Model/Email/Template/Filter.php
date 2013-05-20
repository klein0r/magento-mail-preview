<?php
/**
 * MKleine - (c) Matthias Kleine
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mkleine.de so we can send you a copy immediately.
 *
 * @category    MKleine
 * @package     MKleine_Mailpreview
 * @copyright   Copyright (c) 2013 Matthias Kleine (http://mkleine.de)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MKleine_Mailpreview_Model_Email_Template_Filter extends Mage_Core_Model_Email_Template_Filter
{
    /**
     * Overrides the default Store function
     * @return int|mixed
     */
    public function getStoreId()
    {
        // Use mail preview configuration in admin mode
        if (Mage::app()->getStore()->isAdmin()) {
            return Mage::getStoreConfig('mailpreview/admin_settings/preview_store');
        }

        return parent::getStoreId();
    }

    /**
     * Returns a list of variables in the delivered text
     * @param $text
     * @return array
     */
    public function getPlaceholderList($text)
    {
        $vars = array();

        $matches = array();
        if (preg_match_all(Varien_Filter_Template::CONSTRUCTION_PATTERN, $text, $matches, PREG_SET_ORDER))
        {
            foreach($matches as $index => $construction) {

                $match = $construction[0];
                $directiveType = $construction[1];
                $variableName = $construction[2];

                $callback = array($this, $directiveType.'Directive');

                if(!is_callable($callback)) {
                    continue;
                }

                $tokenizer = new Varien_Filter_Template_Tokenizer_Variable();
                $tokenizer->setString($variableName);
                $params = $tokenizer->tokenize();

                if (!$this->containsMethod($params)) {

                    if (in_array($directiveType, array('var'))) {
                        $vars[] = trim($variableName);
                    }

                    $vars = array_merge($vars, $this->getVariableNamesOf($variableName));
                }
            }
        }

        // Every variable just one time
        $vars = array_unique($vars);

        // dispatch event with found vars
        Mage::dispatchEvent('mk_mailpreview_found_mail_preview_vars', array(
            'sender' => $this,
            'vars'  => $vars
        ));

        return $vars;
    }

    /**
     * Checks if the parameter contains a method
     * @param $params
     */
    protected function containsMethod($params)
    {
        foreach ($params as $param) {
            if ($param['type'] == 'method') {
                return true;
            }
        }

        return false;
    }

    protected function getVariableNamesOf($value)
    {
        $vars = array();

        $tokenizer = new Varien_Filter_Template_Tokenizer_Parameter();
        $tokenizer->setString($value);
        $params = $tokenizer->tokenize();

        foreach ($params as $key => $value) {
            if (substr($value, 0, 1) === '$') {
                $vars[] = substr($value, 1);
            }
        }

        return $vars;
    }

    /**
     * Returns a list of directives by using reflection
     * @return array
     */
    protected function getDirectiveList()
    {
        $directiveList = array();

        $class = Zend_Server_Reflection::reflectClass(__CLASS__);
        /** @var $method Zend_Server_Reflection_Method */
        foreach ($class->getMethods() as $method) {
            if (strrpos($method->getName(), 'Directive')) {
                $directiveList[] = $method->getName();
            }
        }

        sort($directiveList);

        return $directiveList;
    }
}