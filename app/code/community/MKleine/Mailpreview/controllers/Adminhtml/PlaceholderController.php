<?php

class MKleine_Mailpreview_Adminhtml_PlaceholderController extends Mage_Adminhtml_Controller_Action
{

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('mk_mailpreview/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function indexAction() {
        $this->_initAction()
            ->renderLayout();
    }

    public function editAction() {

        $id     = $this->getRequest()->getParam('id');
        $model  = Mage::getModel('mk_mailpreview/placeholder')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);
            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('carousel_data', $model);

            $this->loadLayout();
            $this->_setActiveMenu('mk_mailpreview/items');

            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item Manager'), Mage::helper('adminhtml')->__('Item Manager'));
            $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Item News'), Mage::helper('adminhtml')->__('Item News'));

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('mk_mailpreview/adminhtml_carousel_edit'))
                ->_addLeft($this->getLayout()->createBlock('mk_mailpreview/adminhtml_carousel_edit_tabs'));

            $this->renderLayout();

        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mk_mailpreview')->__('Item does not exist'));
            $this->_redirect('*/*/');
        }

    }

    public function newAction() {
        $this->_forward('edit');
    }

    public function saveAction() {

        if ($data = $this->getRequest()->getPost()) {

            try {
                $model = Mage::getModel('mk_mailpreview/placeholder');
                $model->setData($data)->setId($this->getRequest()->getParam('id'));

                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Item was successfully saved'));
                Mage::getSingleton('adminhtml/session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }

                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setFormData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }

        }

        Mage::getSingleton('adminhtml/session')->addError($this->__('Unable to find item to save'));
        $this->_redirect('*/*/');
    }

    public function deleteAction() {

        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $model = Mage::getModel('mk_mailpreview/placeholder');
                $model->setId($this->getRequest()->getParam('id'))->delete();

                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Item was successfully deleted'));
                $this->_redirect('*/*/');

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }

        $this->_redirect('*/*/');
    }

    public function massDeleteAction() {

        $placeholderIds = $this->getRequest()->getParam('placeholder');

        if(!is_array($placeholderIds)) {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Please select item(s)'));
        } else {
            try {
                foreach ($placeholderIds as $placeholderId) {
                    $carousel = Mage::getModel('mk_mailpreview/placeholder')->load($placeholderId);
                    $carousel->delete();
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($placeholderIds)
                    )
                );

            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }

}