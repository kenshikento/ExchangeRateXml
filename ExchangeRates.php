<?php 
include("XmlSort.php");

class ExchangeRates 
{
    /**
     * Grabs the Rates from Xml file
     *
     * @return object
     */	
	public function getRates() :object
	{	
		$data = new XmlSort();
		return $data->getContents('orders.xml');
	}

    /**
     * Grabs the Exchange Rates 
     *
     * @return object
     */
	public function getPrice(string $date, ?string $currencyType, $test=null) :object
	{
		$data = new XmlSort(); 
		$contents = $data->getContents('rates.xml');

		if(!$date) {	
			return $contents;		
		}


		foreach($contents->currency as $key => $orderCurrency) {
			if($orderCurrency->code == $currencyType) {
				foreach($orderCurrency->rateHistory->rates as $rate) {
					$currencyDate = (String) strtotime($rate->attributes()->date);
					if($currencyDate === $date) {	
						return $orderCurrency->rateHistory->rates;
					}
				}
			}
		}
	}

    /**
     * Filters Exchange Rate Date and target currency rate
     *
     * @return array
     */
	public function xmlDateGrabber(object $data, string $currencyType, ?string $test=null) : array  
	{
		$date = [];
		$values = [];
		$max = count($data->rate);
		if($test==='test') {
			//dd(date("m/d/Y h:i:s A T",$date));
		}
		for ($x = 0; $x < $max; $x++) {
			$values = $data->rate[$x]->attributes();

		    if((string)$values->code == $currencyType) {
			    $date[$currencyType]['code'] = (string)$values->code;		    
			    $date[$currencyType]['value'] = (string)$values->value;
		    } else {
			    $date[0]['code'] = (string)$values->code;		    
			    $date[0]['value'] = (string)$values->value;		    	
		    }
		}
		return $date;
	}

    /**
     * Returns Listed Target Currency
     *
     * @return array
     */
	public function targetCurrency() :array
	{
		return ['EUR','GBP'];
	}

    /**
     * Returns Calculates exchange rate price change
     *
     * 
     */
	public function calculateExchangeDiff($price,$exchangeCurrency) 
	{	
		return $price * $exchangeCurrency;
	} 

    /**
     * Sorts Data and changes the product values to target currency
     *
     * @return array
     */
	public function sortData($currencyList):array
	{	
		$transaction = [];
		$orders = $this->getRates();
		$orderCount = count($orders);

		for ($i = 0; $i < $orderCount; $i++) {

			$order = $orders->order[$i];
			$currency = $currencyList[$i]; 

			$id = (int) $order->id;
			$date = strtotime((String) $order->date[0]);
			$price = $this->getPrice($date, (string)$order->currency);

			$xmlCurrency = $this->xmlDateGrabber($price, $currency,'test');
			$targetCurrency = $xmlCurrency[$currency];
			$currentCurrency = $xmlCurrency[0];

			$transaction[$i]['order']['id'] = (string)$order->id;
			$transaction[$i]['order']['date'] = (string)$order->date[0];
			
			$productArray = $this->sortProducts(
				$order->products, 
				$targetCurrency['value']
			);

			$transaction[$i]['order']['products'][] = $productArray;  
			$transaction[$i]['order']['total'] = 
				$this->calculateExchangeDiff(
					(string)$order->total[0],
					$targetCurrency['value']
				)
			;
		}
		return $transaction;
	}

    /**
     * Sorts Data and changes the product values to target currency
     *
     * @return array
     */
	public function sortProducts($data,$rate):array 
	{
		$productArray = [];
		$productCount = count($data->product);

		for ($x = 0; $x < $productCount; $x++) {
			$product = $data->product[$x]->attributes();
			$productArray[$x]['title'] = (string)$product->title;
			$productArray[$x]['price'] = $this->calculateExchangeDiff($product->price,$rate);
		}	
		return $productArray;	
	}

	 /**
     * Controls and generates xml
     *
     * @return void
     */
	public function sortOrders()
	{
		$currency = $this->targetCurrency();	
		$exchangeData = $this->sortData($currency);
		$this->generateXML($exchangeData);
	}

    /**
     * Generates XML for data
     *
     * @return void
     */
	public function generateXML($data) 
	{
		$xml = new XmlSort();

		$root ='orders';
	    $xmlStructure = new SimpleXMLElement($root ? '<' . $root . '/>' : '<root/>');
		foreach($data as $value) {
		
			$xmlData = $xml->toXML($xmlStructure, $value);
		}
		
		if($xml->isValidXml($xmlData) === false) {
			throw new Exception('Not Valid XML');
		}

		$xml->outputFile($xmlData);
		echo 'File has been sucesfully made';
	}
}

$runs = new ExchangeRates();
$runs->sortOrders();