<?php
namespace Sundial\ERP\Model;
use Sundial\ERP\Api\GetorderInterface;
class Getorder implements GetorderInterface
{
	private $_xmlData = '';
	protected $accountManagement;
	public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
		\Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
		\Magento\Customer\Model\AccountManagement $accountManagement,
		\Sundial\ERP\Helper\Data $dataHelper
	){
		$this->_orderFactory = $orderFactory;
		$this->customerRepository = $customerRepository;
		$this->accountManagement = $accountManagement;
		$this->_dataHelper = $dataHelper;
	}
    public function getOrderDetail($orderId) {
        return $this->process($orderId);
    }
	public function process($orderId){
		try{
			$order = $this->_orderFactory->create()->loadByIncrementId($orderId);
			if(!$order->hasData()){
				$this->_dataHelper->fault(404, 'order does not exit');
			}else{
				  if($order->getCustomerIsGuest()){
						 $customerId = 0;
					 }else{
						 $customerId = $order->getCustomerId();
				  }
				  $this->_xmlData = "<?xml version='1.0' encoding='utf-8'?>"; 
				  $this->_xmlData .= "<getOrder xmlns='http://www.sundialbrands.com/magento'><xmlstring><![CDATA[<MagOrder>";
				  $this->_addFieldToXML("MagCustId", $customerId);
				  $this->_getCustomerInfo($order);
				  $this->_getOrderInfo($order);
				  $this->_xmlData .= "</MagOrder>]]></xmlstring>
				  </getOrder>";
				 return $this->_xmlData;
			}
		}catch (Exception $fault) {
            $this->_dataHelper->fault(500, 'Something went wrong');
        }
        exit;
	}
	private function _addFieldToXML($strFieldName, $strValue)
    {
        $strResult = mb_convert_encoding(
            str_replace('&', '&amp;', $strValue),
            'UTF-8'
        );
        $this->_xmlData .= "\t\t<$strFieldName>$strResult</$strFieldName>";
    }
	
	private function _getCustomerInfo($order){
		 if($order->getCustomerIsGuest()){
			$shipSave = 0;
			foreach($order->getAllItems() as $items){		
				if(($items->getProductType() == 'downloadable') || ($items->getProductType() == 'virtual') ){
					$shipSave = 0;
				}else{
					$shipSave = 1;
				}
			}
			if($shipSave){
				 $shippingAddress = $order->getShippingAddress();
				 $shipStreetArray = $shippingAddress->getStreet();
				 $shipstreet = $shipStreetArray[0];
				 $shipcity = $shippingAddress->getCity();
				 $shipregion = $shippingAddress->getRegion();
				 $shipcountry =  $shippingAddress->getCountryId();
				 $shippostcode = str_replace("-", "", $shippingAddress->getPostcode());
				 $shipphone = $shippingAddress->getTelephone();
				 $mainHistory = 'N';			 
				 $name = $shippingAddress->getFirstName().' '.$shippingAddress->getLastName();
				 $email = $shippingAddress->getEmail();
				 $createdAt = $order->getCreatedAt();
				 $billstreet = '';
				 $billcity = '';
				 $billregion = '';
				 $billcountry = '';
				 $billpostcode = '';
				 $billphone = '';
			}else{
				 $billingingAddress = $order->getBillingAddress();
				 $mainHistory = 'N';			 
				 $name = $billingingAddress->getFirstName().' '.$billingingAddress->getLastName();
				 $email = $billingingAddress->getEmail();
				 $createdAt = $order->getCreatedAt();
				 $billstreet = '';
				 $billcity = '';
				 $billregion = '';
				 $billcountry = '';
				 $billpostcode = '';
				 $billphone = '';
				 $shipStreetArray = '';
				 $shipstreet = '';
				 $shipcity = '';
				 $shipregion = '';
				 $shipcountry =  '';
				 $shippostcode = '';
				 $shipphone = '';
			}
			 
		 }else{
			 $customer =  $this->customerRepository->getById($order->getCustomerId());
			 if($customer->getDefaultBilling()){
				 $billingAddress = $this->accountManagement->getDefaultBillingAddress($order->getCustomerId());	
				 $billregion = $billingAddress->getRegion()->getRegion();
			 }else{				  
				  $billingAddress = $order->getBillingAddress();
				  $billregion = $billingAddress->getRegion();
			 }
			 if($customer->getDefaultShipping()){				 
				  $custshippingAddress = $this->accountManagement->getDefaultShippingAddress($order->getCustomerId());
				  $shipStreetArray = $custshippingAddress->getStreet();
				  $shipstreet = $shipStreetArray[0];
				  $shipcity = $custshippingAddress->getCity();
				  $shipregion = $custshippingAddress->getRegion()->getRegion();
				  $shipcountry = $custshippingAddress->getCountryId();
				  $shippostcode = str_replace("-", "", $custshippingAddress->getPostcode()); 
				  $shipphone = $custshippingAddress->getTelephone();
			 }else{
				 
				$shipSave = 0;
				foreach($order->getAllItems() as $items){		
					if(($items->getProductType() == 'downloadable') || ($items->getProductType() == 'virtual') ){
						$shipSave = 0;
					}else{
						$shipSave = 1;
					}
				}
				if($shipSave){
					$custshippingAddress = $order->getShippingAddress();$shipStreetArray = $custshippingAddress->getStreet();
					$shipstreet = $shipStreetArray[0];
					$shipcity = $custshippingAddress->getCity();
					$shipregion = $custshippingAddress->getRegion();
					$shipcountry = $custshippingAddress->getCountryId();
					$shippostcode = str_replace("-", "", $custshippingAddress->getPostcode()); 
					$shipphone = $custshippingAddress->getTelephone();		
				}else{
					 $shipStreetArray = '';
					 $shipstreet = '';
					 $shipcity = '';
					 $shipregion = '';
					 $shipcountry =  '';
					 $shippostcode = '';
					 $shipphone = '';
				}
				 
			 }
			 $mainHistory = 'Y';
			 $name = $customer->getFirstName().' '.$customer->getLastName();
			 $email = $customer->getEmail();
			 $createdAt = $customer->getCreatedAt();
			 $billStreetArray = $billingAddress->getStreet();
			 $billstreet = $billStreetArray[0];
			 $billcity = $billingAddress->getCity();			 
			 $billcountry = $billingAddress->getCountryId();
			 $billpostcode = str_replace("-", "", $billingAddress->getPostcode());
			 $billphone = $billingAddress->getTelephone();
			 
		 }
		 $this->_xmlData .= "<SetupArCustomer xmlns:xsd='http://www.w3.org/2001/XMLSchema-instance' xsd:noNamespaceSchemaLocation='ARSSCSDOC.XSD'>";
		 $this->_xmlData .= "<Item>";
		 $this->_xmlData .= "<Key>";
		 $this->_xmlData .= "<Customer>";
		 $this->_xmlData .= "</Customer>";
		 $this->_xmlData .= "</Key>";
		 $this->_addFieldToXML("Name", $name);
		 $this->_addFieldToXML("ExemptFinChg", "N");
		 $this->_addFieldToXML("MaintHistory", $mainHistory);
		 $this->_addFieldToXML("CreditStatus", 0);
		 $this->_addFieldToXML("CreditLimit", 99999);
		 $this->_addFieldToXML("Salesperson", '000');
		 $this->_addFieldToXML("PriceCode", "RT");
		 $this->_addFieldToXML("Branch", "NY");
		 $this->_addFieldToXML("TermsCode", "CC");
		 $this->_addFieldToXML("TaxStatus", "N");
		 $this->_addFieldToXML("Telephone", $billphone);
		 $this->_addFieldToXML("Currency", "$");
		 $this->_addFieldToXML("DetailMoveReqd", "Y");
		 $this->_addFieldToXML("ContractPrcReqd", "N");
		 $this->_addFieldToXML("StatementReqd", "N");
		 $this->_addFieldToXML("BackOrdReqd", "Y");
		 $this->_addFieldToXML("DateCustAdded", $createdAt);
		 $this->_addFieldToXML("StockInterchange", "N");
		 $this->_addFieldToXML("MaintLastPrcPaid", "N");
		 $this->_addFieldToXML("IbtCustomer", "N");
		 $this->_addFieldToXML("CounterSlsOnly", "N");
		 $this->_addFieldToXML("CustomerOnHold", "N");
		 $this->_addFieldToXML("EdiFlag", "N");
		 $this->_addFieldToXML("Email", $email);
		 $this->_addFieldToXML("ApplyOrdDisc", "Y");
		 $this->_addFieldToXML("ApplyLineDisc", "Y");
		 $this->_addFieldToXML("FaxInvoices", "N");
		 $this->_addFieldToXML("FaxStatements", "N");
		 $this->_addFieldToXML("FaxQuotes", "N");
		 $this->_addFieldToXML("CustomerClass", "RT");
		 $this->_addFieldToXML("SoldToAddr1", substr($billstreet, 0, 40));
		 $this->_addFieldToXML("SoldToAddr2", "");
		 $this->_addFieldToXML("SoldToAddr3", $billcity);
		 $this->_addFieldToXML("SoldToAddr4", $billregion);
		 $this->_addFieldToXML("SoldToAddr5", $billcountry);
		 $this->_addFieldToXML("SoldPostalCode", $billpostcode);
		 $this->_addFieldToXML("ShipToAddr1", substr($shipstreet, 0, 40));
		 $this->_addFieldToXML("ShipToAddr2", "");
		 $this->_addFieldToXML("ShipToAddr3", $shipcity);
		 $this->_addFieldToXML("ShipToAddr4", $shipregion);
		 $this->_addFieldToXML("ShipToAddr5", $shipcountry);
		 $this->_addFieldToXML("SoDefaultDoc", 0);
		 $this->_addFieldToXML("State", "NA");
		 $this->_addFieldToXML("CountyZip", "NA");
		 $this->_addFieldToXML("City", "NA");
		 $this->_addFieldToXML("State1", "NA");
		 $this->_addFieldToXML("CountyZip1", "NA");
		 $this->_addFieldToXML("City1", "NA");
		 $this->_xmlData .= "</Item>";
		 $this->_xmlData .= "</SetupArCustomer>";
		 
	 }
	 private function _getOrderInfo($order){
		 
		$shipSave = 0;
		foreach($order->getAllItems() as $items){		
			if(($items->getProductType() == 'downloadable') || ($items->getProductType() == 'virtual') ){
				$shipSave = 0;
			}else{
				$shipSave = 1;
			}
		}
		if($shipSave){
			$shippingAddress = $order->getShippingAddress();
			$name = $shippingAddress->getFirstName().' '.$shippingAddress->getLastName();
			$streetArray = $shippingAddress->getStreet();
			$street = $streetArray[0];
		}else{
			$shippingAddress = $order->getBillingAddress();
			$name = $shippingAddress->getFirstName().' '.$shippingAddress->getLastName();	
			$shipStreetArray = $shippingAddress->getStreet();
			$street = $shipStreetArray[0];
		}
		 $orderId = 'MAGAB'.$order->getIncrementId();
		 $this->_xmlData .= " <SalesOrders xmlns:xsd='http://www.w3.org/2001/XMLSchema-instance' xsd:noNamespaceSchemaLocation='SORTOIDOC.XSD'>";
		 $this->_xmlData .= "<Orders>";
		 $this->_xmlData .= "<OrderHeader>";
		 $this->_addFieldToXML("CustomerPoNumber", $orderId);
		 $this->_addFieldToXML("OrderActionType", "A");
		 $this->_addFieldToXML("OrderDate", $order->getCreatedAt());
		 $this->_addFieldToXML("Customer", "");
		 $this->_addFieldToXML("ShippingInstrs", "");
		 $this->_addFieldToXML("CustomerName", $name);
		 $this->_addFieldToXML("ShipAddress1", substr($street, 0, 40));
		 $this->_addFieldToXML("ShipAddress3", $shippingAddress->getCity());
		 $this->_addFieldToXML("ShipAddress4", $shippingAddress->getRegion());
		 $this->_addFieldToXML("ShipAddress5", $shippingAddress->getCountryId());
		 $this->_addFieldToXML("ShipPostalCode", str_replace("-", "",$shippingAddress->getPostcode())); 
		 $this->_addFieldToXML("Email", $shippingAddress->getEmail());
		 $this->_addFieldToXML("OrderType", "R");
		 $this->_addFieldToXML("DocumentFormat", 0);		 
		 $this->_xmlData .= "</OrderHeader>";
		 $this->_xmlData .= "<OrderDetails>";		 
		 $this->_getOrderItemInfo($order);		 
		 $this->_xmlData .= "<FreightLine>";
		 $this->_addFieldToXML("CustomerPoLine", 9999);
		 $this->_addFieldToXML("LineActionType", "A");
		 $this->_addFieldToXML("FreightValue",  round($order->getShippingInvoiced(),2));
		 $this->_addFieldToXML("FreightCost", round($order->getShippingAmount(),2));
		 $this->_xmlData .= "</FreightLine>";
		 $this->_xmlData .= "<MiscChargeLine>";
		 $this->_addFieldToXML("CustomerPoLine", 9999);
		 $this->_addFieldToXML("LineActionType", "A");
		 $this->_addFieldToXML("MiscChargeValue", round($order->getTaxAmount(),2) );
		 $this->_addFieldToXML("MiscChargeCost", round($order->getTaxAmount(),2));
		 $this->_addFieldToXML("MiscQuantity", 1);
		 $this->_addFieldToXML("MiscProductClass", "_TAX");
		 $this->_xmlData .= "</MiscChargeLine>";		 
		 $this->_xmlData .= "<MiscChargeLine>";
		 $this->_addFieldToXML("CustomerPoLine", 9999);
		 $this->_addFieldToXML("LineActionType", "A");
		 $this->_addFieldToXML("MiscChargeValue", round($order->getDiscountAmount(),2));
		 $this->_addFieldToXML("MiscChargeCost", round($order->getDiscountAmount(),2));
		 $this->_addFieldToXML("MiscQuantity", 1);
		 $this->_addFieldToXML("MiscProductClass", "_DSC");
		 $this->_addFieldToXML("MiscDescription", $order->getCouponCode());
		 $this->_xmlData .= "</MiscChargeLine>";		 
		 $this->_xmlData .= "</OrderDetails>";
		 $this->_xmlData .= "</Orders>";
		 $this->_xmlData .= "</SalesOrders>";
	 }
	 private function _getOrderItemInfo($order){
		 $items = $order->getAllItems();
		 foreach ($items as $item) {
			 $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		     $_product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getProductId());
			 $stockCode = $_product->getResource()->getAttribute('ea_stockcode')->getFrontend()->getValue($_product);
			 $this->_xmlData .= "<StockLine>";
			 $this->_addFieldToXML("CustomerPoLine", 9999);
			 $this->_addFieldToXML("LineActionType", "A"); 
			 $this->_addFieldToXML("StockCode", $stockCode);
			 $this->_addFieldToXML("OrderQty", round($item->getQtyInvoiced(),2));
			 $this->_addFieldToXML("OrderUom", "EA");
			 $this->_addFieldToXML("PriceUom", "EA");  
			 $this->_addFieldToXML("Price", round($item->getGwPriceInvoiced(),2));	
			 $this->_addFieldToXML("Warehouse", "F4");
			 $this->_xmlData .= "</StockLine>";		 
		 }
	 }
	 
	
}