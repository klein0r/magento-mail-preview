<?php

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . DS . 'System' . DS . 'Email' . DS . 'TemplateController.php';

class MKleine_Mailpreview_System_Email_TemplateController extends Mage_Adminhtml_System_Email_TemplateController
{
    public function previewAction()
    {
        $this->loadLayout('mkleine_mailpreview');
        $this->renderLayout();
    }
}