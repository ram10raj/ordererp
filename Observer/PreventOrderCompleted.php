<?php
namespace Sundial\ERP\Observer;
use Magento\Framework\Event\ObserverInterface;
class PreventOrderCompleted implements ObserverInterface
{   
	 protected $scopeConfig;
	 const XML_PATH_ERP_URL = 'erpurl/erpconfiguration/url';
	 public function __construct(
	 \Psr\Log\LoggerInterface $logger,
	 \Sundial\ERP\Model\ResourceModel\Status\CollectionFactory $statusCollectionFactory,
	 \Sundial\ERP\Model\Getorder $getOrder,
	 \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
	 ){
		 $this->logger = $logger;
		 $this->getOrder = $getOrder;
		 $this->statusFactory = $statusCollectionFactory;
		 $this->scopeConfig = $scopeConfig;
	 }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
		$storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
		
		$order = $observer->getOrder();
		$label = $order->getStatusLabel();		
		$statusCollection = $this->statusFactory->create()
				->addFieldToFilter('order_id', $order->getIncrementId())
				->getFirstItem();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$model = $objectManager->create('Sundial\ERP\Model\Status');	
		if($label == 'Complete'){
		    $orderXml = $this->getOrder->process($order->getIncrementId());
			//$this->logger->info($orderXml);
			$sysProUrl = $this->scopeConfig->getValue(self::XML_PATH_ERP_URL, $storeScope);
			//$this->logger->info($sysProUrl);			
				$xmlObjectForOrderData = simplexml_load_string($orderXml) or die("Error: Cannot create object");
				$options = array(
                'soap_version'=>SOAP_1_2,
                'exceptions'=>true,
                'trace'=>1,
                'cache_wsdl'=>WSDL_CACHE_NONE
				);
				$client = new \SoapClient($sysProUrl,$options);
				$data = $client->getOrder($xmlObjectForOrderData);
				$erpResponse = json_decode(json_encode($data), true);
				if(is_numeric($erpResponse['getOrderResult'])){
						$model->load($statusCollection->getId());
						$model->setStatus('completed');
						$model->setOrderId($order->getIncrementId());
						$model->setMessage($erpResponse['getOrderResult']);
						$model->save();
				}else{
					$model->load($statusCollection->getId());
					$model->setStatus('failure');
					$model->setOrderId($order->getIncrementId());
					$model->setMessage($erpResponse['getOrderResult']);
					$model->save();
				}
				
			}

		}
}
