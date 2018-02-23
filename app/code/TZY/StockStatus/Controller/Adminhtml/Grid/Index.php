<?php
/**
 * Grid Record Index Controller.
 * @category  TZY
 * @package   TZY_StockStatus
 * @author    TZY
 * @copyright Copyright (c) 2018 TZY
 * @license   license.html
 */
namespace TZY\StockStatus\Controller\Adminhtml\Grid;

class Index extends \Magento\Framework\App\Action\Action
{
   public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();       
    }
   

}