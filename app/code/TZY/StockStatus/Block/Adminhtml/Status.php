<?php

namespace TZY\StockStatus\Block\Adminhtml;

use \Magento\Sales\Model\Order;

class Status extends \Magento\Framework\View\Element\Template
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,      
        \Magento\Sales\Model\Order $order,
        \Magento\CatalogInventory\Api\StockStateInterface $stock,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,     
        array $data = []
    ) {
        $this->stock  = $stock;
        $this->order  = $order;        
        $this->productCollectionFactory = $productCollectionFactory;
        parent::__construct($context, $data);
    }

     public function getProduct()
    {       
        //get values of current page
        $page=($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 20;
        
        $productItems =  $this->productCollectionFactory->create();   
        $productItems->addAttributeToSelect('name'); 
        $productItems->setPageSize($pageSize);
        $productItems->setCurPage($page);

        return $productItems;
    }
    

    public function getOrderStatus()
    {
        $itemLoad  = [];
        foreach($this->order->getCollection() as $key => $orderRef){ 
            $order = clone $orderRef;           
            $orderStatus   = $order->getData('status');
            $items   = $this->order->load($orderRef->getData('entity_id'))->getAllItems();
            foreach( $items as $key => $item ){
              $product    = $item->getData();
              $productId  = $product['product_id'];
              if( !array_key_exists($productId, $itemLoad) )
                  $itemLoad[$productId]    = [
                      'processing'  => 0,
                      'pending'     => 0,
                      'complete'    => 0
                  ];
              $itemLoad[$productId][$orderStatus]  += $product['qty_ordered'];
            }
        }
        return $itemLoad;
    }

    public function getAvailableStockQty($prdId)
    {
        return $this->stock->getStockQty($prdId);
    }

    public function productLoad()
    {   
        foreach ($this->getProduct() as $key => $product): ?>
          <tr>
              <?php
                $pending = $processing = $complete = 0;
                $productId = $product->getId();
                $available = $this->getAvailableStockQty($productId);               
                $productStatus = $this->getOrderStatus();               
                if( array_key_exists($productId, $productStatus) ){
                    $pending  = $productStatus[$productId]['pending'];
                    $processing  = $productStatus[$productId]['processing'];
                    $complete  = $productStatus[$productId]['complete'];
                }
                $total  = $pending + $processing + $complete + $available;
               ?>              
              <td><?php echo $product->getName(); ?></td>
              <td class="text-center"><?php echo $product->getSku(); ?></td>
              <td class="text-center"><?php echo $total ?></td>
              <td class="text-center"><?php echo $available ?></td>
              <td class="text-center"><?php echo $pending ?></td>
              <td class="text-center"><?php echo $processing?></td>
          </tr>
        <?php endforeach; ?><?php       
     }    

     protected function _prepareLayout()
    {

        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('Stock Status'));

        if ($this->getProduct()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'stockstatus.status.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15,20=>20))
                ->setShowPerPage(true)->setCollection(
                $this->getProduct()
            );
            $this->setChild('pager', $pager);
            $this->getProduct()->load();
        }
        return $this;
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
   

   

}
