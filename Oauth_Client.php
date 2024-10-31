<?php
class Oauth2_Client
{
  const DEBUG=false;
  
  protected $_options;
	
  public function __construct(array $options) {
	  $_options=$this->getRequiredOptions();
	  $this->_options=array_intersect_key($options,$_options);
  }
  
  protected function getRequiredOptions() {
	  return array(
		'client_id' => null,          // client ID
		'client_secret' => null,      // client secret key
		'base_uri' => null,           // oauth2 URI
		'authorize_path' => null,     // oauth2 authorization endpoint
		'token_path' => null,   	  // oauth2 token endpoint
		'service_path' => null        // oauth2 service endpoint
	  );
  }
  
  protected function checkOptions() {
	  if (!is_array($this->_options)) return false;
	  $options=$this->getRequiredOptions();
	  foreach($options as $k=>$v) {
		  if (!array_key_exists($k,$this->_options)) throw new Exception("Required option '".$k."' missing");
		  if (in_array($k,array('base_uri','authorize_path','token_path','service_path'))) $this->_options[$k]=trim($this->_options[$k],'/');
	  }
  }
	
  protected function buildUri($uri,$params) {
	  // adding request signature
	  $params['signature']=$this->buildSignature('GET',$uri,$params,$this->_options['client_secret']);
	  
	  $query=array();
	  foreach($params as $k=>$v) {
		  $query[]=urlencode($k).'='.urlencode($v);
	  }
	  
	  $sep=strpos($uri,'?') ? '&' : '?';
	  return $uri.$sep.(implode('&',$query));
  }
	
  protected function doRedirect($uri, $params,$exit=true) {
    header("Location: " . $this->buildUri($uri, $params));
    if ($exit) exit;
  }
  
  protected function doRequest($uri,$params) {
	  return json_decode(file_get_contents($this->buildUri($uri,$params)));
  }
  
  protected function buildSignature($method,$base_uri,$params,$key) {
	$pairs=array();
	foreach ($params as $k=>$v) {
		$pairs[]=((string)$k).'='.((string)$v);
	}
	$query=implode('&', $pairs); 
	 
	$_params=array(
		urlencode($method),
		urlencode($base_uri),
		urlencode($query)
	);
	$base_string=implode('&',$_params);

	return base64_encode(hash_hmac('sha1', $base_string, $key, true));
  }
  
  public function requestAuthCode(array $params=array(),$exit=true) {
	  try {
		  $this->checkOptions(); 
		  $params['client_id']=$this->_options['client_id'];
		  $this->doRedirect($this->_options['base_uri'].'/'.$this->_options['authorize_path'],$params);
	  } catch(Exception $e) {
		   if (self::DEBUG) echo $e->getMessage();  
		   if ($exit) exit;
	  }
  }
  
  public function requestAccessToken($code,array $params=array(),$exit=true) {
	  try {
		  $this->checkOptions();
		  $params['client_id']=$this->_options['client_id'];
		  $params['code']=$code;
		  return $this->doRequest($this->_options['base_uri'].'/'.$this->_options['token_path'],$params);
	  } catch(Exception $e) {
		   if (self::DEBUG) echo $e->getMessage();  
		   if ($exit) exit;
	  }
  }
  
  public function api($token,array $params=array(),$exit=true) {
	  try {
		  $this->checkOptions();
		  $params['token']=$token;
		  return $this->doRequest($this->_options['base_uri'].'/'.$this->_options['service_path'],$params);
	  } catch(Exception $e) {
		   if (self::DEBUG) echo $e->getMessage();  
		   if ($exit) exit;
	  }
  }
}
?>