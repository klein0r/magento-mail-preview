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
class MKleine_Mailpreview_Model_Placeholder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('mk_mailpreview/placeholder');
    }

    /**
     * Loads a placeholder by a specific variable
     * @param $varName Name of variable
     * @return $this MKleine_Mailpreview_Model_Placeholder
     */
    public function loadPlaceholderByVariableName($varName)
    {
        $this->load($varName, 'variable');
        return $this;
    }

    /**
     * Validates the current object
     * @return array
     */
    public function validate()
    {
        /** @var $helper MKleine_Mailpreview_Helper_Data */
        $helper = Mage::helper('mk_mailpreview');

        $errors = array();
        if (!Zend_Validate::is($this->getVariable(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the variable name.');
        }
        if (!Zend_Validate::is($this->getReplacement(), 'NotEmpty')) {
            $errors[] = $helper->__('Please enter the replacement.');
        }
        if ($helper->isBlacklistVar($this->getVariable())) {
            $errors[] = $helper->__('This variable name is reserved');
        }

        return $errors;
    }
}