<?php


class SMSPubliApi {

	public $APIKEY = false;
	public $ROOT = 'https://api.gateway360.com/api/3.0';

    var $timeDrift = 0;

    public function __construct($_apikey=false, $_root=false) {
		
        if ($_apikey) $this->APIKEY = $_apikey;
		if ($_root) $this->ROOT = $_root;

    }


    function call($method, $url, $body = NULL)
    {
        $url = $this->ROOT . $url;
        
		$bodystring = '{
				"api_key":"'.$this->APIKEY.'"';

        if($body)
        {
			foreach($body as $key=>$value) { $bodystring .= ', '.$key.':'.$value; }	
        }

		$bodystring .= '}';
        
        	
		$headers = array('Content-Type: application/json');        	

        // Call
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	    //curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        
        
        if($bodystring)
        {
			curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $bodystring);
        }
        $result = curl_exec($curl);
           
        if($result === FALSE)
        {
            echo curl_error($curl);
            return NULL;
        }
        return json_decode($result); 
    }

    function get($url)
    {
        return $this->call("GET", $url);
    }
    function put($url, $body)
    {
        return $this->call("PUT", $url, $body);
    }
    function post($url, $body)
    {
        return $this->call("POST", $url, $body);
    }
    function delete($url, $body = false)
    {
        return $this->call("DELETE", $url, $body);
    }
	
	function encode($string) {
		if (preg_match('!!u', $string))
		{
		   return $string;
		}
		else 
		{
		   return utf8_encode($string);
		}
	}
	
	function decode($string) {
		if (preg_match('!!u', $string))
		{
		   return utf8_decode($string);
		}
		else 
		{
		   return $string;
		}
	}
	
}

?>
