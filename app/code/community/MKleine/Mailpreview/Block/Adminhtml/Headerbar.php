<?php

class MKleine_Mailpreview_Block_Adminhtml_Headerbar extends Mage_Adminhtml_Block_Abstract
{


    public function getTemplateModel()
    {
        return Mage::getModel('core/email_template');
    }
}