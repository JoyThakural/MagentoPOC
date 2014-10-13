<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    User
 * @package     User_Surveys
 * @copyright   Copyright (c) 2014 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User_Surveys_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Pre dispatch action that allows to redirect to no route page in case of disabled extension through admin panel
     */
    public function preDispatch()
    {

        parent::preDispatch();

        if (!Mage::helper('user_surveys')->isEnabled()) {
            $this->setFlag('', 'no-dispatch', true);
            $this->_redirect('noRoute');
        }
    }

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->loadLayout();      
        $listBlock = $this->getLayout()->getBlock('forms.list');
        if ($listBlock) {
            $currentPage = abs(intval($this->getRequest()->getParam('p')));
            if ($currentPage < 1) {
                $currentPage = 1;
            }
            $listBlock->setCurrentPage($currentPage);
        }
        
        $this->renderLayout();
    }

    /**
     * Surveys view action
     */
    
    public function viewAction()
    {
        $formId = $this->getRequest()->getParam('id');
        if (!$formId) {
            return $this->_forward('noRoute');
        }

        /** @var $model User_Surveys_Model_Surveys */
        
        $model = Mage::getModel('user_surveys/forms');
        $model->load($formId);
        
        /* Start By Ankush*/
        
        $questionIds = explode(',',$model['questions_id']);
        $question = array();
        $type= array();
        foreach ($questionIds as $key=> $value){
        	$collection = Mage::getModel('user_surveys/questions')->load($key);        
        	$question[$key] = $collection->getQuestions();
            
            $type[$key] = $collection->getType();
            //echo "</pre>"; print_r($question); echo "</pre>";   
        }
        //die("HEre");
        //$type[]=  $collection->getType();



     	Mage::register('questions', $question);
     	Mage::register('type', $type);

        if (!$model->getId()) {
            return $this->_forward('noRoute');
        }

        Mage::register('surveys_item', $model);

        Mage::dispatchEvent('before_surveys_item_display', array('surveys_item' => $model));

        $this->loadLayout();
        $itemBlock = $this->getLayout()->getBlock('forms.item');
        if ($itemBlock) {
            $listBlock = $this->getLayout()->getBlock('forms.list');
            if ($listBlock) {
                $page = (int)$listBlock->getCurrentPage() ? (int)$listBlock->getCurrentPage() : 1;
            } else {
                $page = 1;
            }
            $itemBlock->setPage($page);
        }
        $itemBlock->setFormAction( Mage::getUrl('*/*/post') );
        $this->renderLayout();
    }
    
    public function postAction()
    {	
    	
    	$post = $this->getRequest()->getPost();

    	if ( $post ) {
            $model = Mage::getModel('user_surveys/surveys');
            foreach ($post as $key => $value) {
     			$userId= $post['user_id'];
     			$formId= $post['form_id'];
     			
                if (preg_match('/question_/',$key)) {
             		$que_array = explode("_", $key);
                	$questionId = $que_array[1];
                    $model->setQuestionId($questionId);
                    $model->setId(null);
                    $model->setUserId($userId);
                    $model->setFormId($formId);
                    $model->setValue($value);
                    $model->save();
                }		
     		}
     	
    	}
        Mage::getSingleton('core/session')->addSuccess("Thanks for participating in Survey.");
        $this->_redirect('*/*/index');
    }
    /*End By Ankush */
}