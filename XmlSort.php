<?php

class XmlSort 
{
	/**
     * Grabs Content from Xml File and validates
     *  
     * @return Object
    */
	public function getContents(String $file) : Object
	{	
		if (file_exists($file)) {
			$xml = simplexml_load_file($file);
			if($xml === FALSE) {
				throw Exception('Error Not Valid Xml');
			}

			return $xml;

		} else {
			throw Exception('Error No Xml file' . $file);
		}
	}

	/**
     * Using array data it converts it into Xml
     *  
     * @return xml
    */
	public function toXML($xml, array $order)
	{	
		foreach($order as $key => $value) {
	        if (is_array($value)) { 

	        	if(is_numeric($key)) {

	        		if(count($value) > 1) {	

		        		foreach($value as $attributes) {
		        			if(is_array($attributes)) {
			        			$new_object = $xml->addChild('product'); 
			        			foreach($attributes as $key =>$attribute) { 
			        				$new_object->addAttribute($key,$attribute);
			        			}	
		        			}
		        		}	        			
	        		}
	        	} else {
					$new_object = $xml->addChild($key);	
	        	}

	        	if(isset($new_object)) { 
	        		$this->to_xml($new_object, $value);
	        	}
	            
	        } else { 
	            if ($key == (int) $key) {
	                $key = '' . $key;
	            }
	            $xml->addChild($key, $value);
	        } 
    	}  

	    return $xml->asXML();
	}

	/**
     * using simplexml_load_string which checks 
     * if xml string is valid 
     *  
     * @return bool
    */
	public function isValidXml(string $data) : bool
	{	
		$unsureXml = simplexml_load_string($data);
		if($unsureXml === false) {
			return false;
		}
		return true;
	}

	/**
     * generates creates and writes onto file and outputs
     *  
     * @return void
    */	
	public function outputFile($file):void 
	{	
		$filename = 'xmlfile.xml';
		$myfile = fopen($filename, 'w');
		fwrite($myfile, $file);
	}
}