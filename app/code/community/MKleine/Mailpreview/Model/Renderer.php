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
class MKleine_Mailpreview_Model_Renderer extends Mage_Core_Model_Abstract
{
    /**
     * Core email template
     * @var Mage_Core_Model_Email_Template
     */
    protected $_templateModel = null;

    /**
     * List of replacement objects
     * @var array
     */
    protected $_replacementObjects = array();

    /**
     * List of replacements for specific vars
     * @var array
     */
    protected $_replacementCache = array();

    /**
     * Never replaced variables
     * @var array
     */
    protected $_blackListVars = array('logo_url', 'logo_alt', 'subscriber');

    /**
     * @param $templateId Specific template id
     */
    protected function initWithId($templateId)
    {
        $this->_templateModel = $this->getModel()->load($templateId);
    }

    protected function initWith($type, $text, $styles)
    {
        $this->_templateModel = $this->getModel()
            ->setTemplateText($type)
            ->setTemplateText($text)
            ->setTemplateStyles($styles);
    }

    /**
     * Returns an instance of the core model email template
     * @return Mage_Core_Model_Email_Template
     */
    protected function getModel()
    {
        return Mage::getModel('core/email_template');
    }

    /**
     * Return the current template model
     * @return Mage_Core_Model_Email_Template|null
     */
    public function getTemplate()
    {
        if (is_null($this->_templateModel)) {

            $id = (int)$this->getParam('id');
            if ($id) {
                $this->initWithId($id);
            } else {
                $this->initWith($this->getParam('type'), $this->getParam('text'), $this->getParam('styles'));
            }

            if ($this->_templateModel) {
                /* @var $filter Mage_Core_Model_Input_Filter_MaliciousCode */
                $filter = Mage::getSingleton('core/input_filter_maliciousCode');

                $this->_templateModel->setTemplateText(
                    $filter->filter($this->_templateModel->getTemplateText())
                );
            }
        }

        return $this->_templateModel;
    }

    /**
     * Loads a specific Request parameter
     * @param $param
     * @return mixed
     */
    private function getParam($param)
    {
        return Mage::app()->getRequest()->getParam($param);
    }

    public function getProcessedTemplate()
    {
        Varien_Profiler::start("email_template_proccessing");

        $processedTemplate = $this->getTemplate()->getProcessedTemplate($this->getTemplateVars(), true);

        Varien_Profiler::stop("email_template_proccessing");

        return $processedTemplate;
    }

    public function getTemplateVars()
    {
        $vars = array();

        foreach ($this->getCurrentReplacements() as $var => $rep)
        {
            // Do not add blacklist entries
            if (!in_array($var, $this->_blackListVars)) {

                $replacement = $this->getReplacementFor($var);

                if (strrpos($var, ".")) {
                    list($objName, $subVar) = explode('.', $var);
                    $obj = $this->getReplacerObject($objName);
                    $obj->setData($subVar, $replacement);

                    // Add the object one time
                    if (!isset($vars[$objName])) {
                        $vars[$objName] = $obj;
                    }
                }
                else {
                    // Normal variable
                    $vars[$var] = $replacement;
                }
            }
        }

        return $vars;
    }

    /**
     * Returns an object of a list of Varien_Object
     * @param $name
     * @return Varien_Object
     */
    protected function getReplacerObject($name)
    {
        if (!isset($this->_replacementObjects[$name])) {
            $this->_replacementObjects[$name] = new Varien_Object();
        }
        return $this->_replacementObjects[$name];
    }

    /**
     * Return the replacement for the specified variable
     * @param $var
     * @return string
     */
    protected function getReplacementFor($var)
    {
        if (!isset($this->_replacementCache[$var])) {
            /** @var $phModel MKleine_Mailpreview_Model_Placeholder */
            $phModel = Mage::getModel('mk_mailpreview/placeholder');
            $phModel->loadPlaceholderByVariableName($var);

            $this->_replacementCache[$var] = $phModel->getReplacement();
        }

        return $this->_replacementCache[$var];
    }

    public function getCurrentReplacements()
    {
        $template = $this->getTemplate();

        /** @var $filter MKleine_Mailpreview_Model_Email_Template_Filter */
        $filter = $template->getTemplateFilter();

        return $filter->getCurrentReplacements($template->getTemplateText());
    }

}