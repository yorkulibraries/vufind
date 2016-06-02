<?php
ini_set('soap.wsdl_cache_enabled', '0');

class Broker {
    public function __construct($wsdl) {
        $this->client = new SoapClient($wsdl);
    }

    public function OrderInitialize() {
        $response = $this->client->OrderInitialize();
        return $response->OrderInitializeResult;
    }

    public function GetToken($order) {
        $response = $this->client->GetToken(array('order'=>$order));
        return $response->GetTokenResult;
    }
    
    public function AcknowledgeComplete($application, $password, $token) {
        $response = $this->client->AcknowledgeComplete(
            array('application'=>$application, 'password'=>$password, 'token'=>$token)
        );
        return $response;
    }
    
    public function ItemsInitialize($count) {
        $response = $this->client->ItemsInitialize(array('capacity'=>$count));
        $items = $response->ItemsInitializeResult->ItemInfo;
        return is_array($items) ? $items : array($items);
    }
}
?>
