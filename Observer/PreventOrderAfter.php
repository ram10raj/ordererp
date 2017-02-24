<?php
namespace Sundial\ERP\Observer;
use Magento\Framework\Event\ObserverInterface;
class PreventOrderAfter implements ObserverInterface
{   
	 public function __construct(
	 \Psr\Log\LoggerInterface $logger,
	 \Sundial\ERP\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory
	 ){
		 $this->logger = $logger;
		 $this->statusFactory = $statusCollectionFactory;
	 }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$order = $observer->getOrder();
		$statusCollection = $this->statusFactory->create()
								  ->addFieldToFilter('order_id', $order->getIncrementId())
								  ->getFirstItem();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$model = $objectManager->create('Sundial\ERP\Model\Status');								  
		if($statusCollection->hasData()){
			$model->load($statusCollection->getId());
			$model->setStatus('new');
			$model->save();
		}else{
			$model->setOrderId($order->getIncrementId());
			$model->setStatus('new');
			$model->save();
		}		
    }
}
