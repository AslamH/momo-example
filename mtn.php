<?php
class momo
{
    var $subscription_key_collection='...............................';
    var $subscription_key_disbursement='...............................';
    var $api_user='...............................';
    var $api_key='...............................';
    var $environment='sandbox';
    var $currency='EUR';
    
    public function __construct()
    {
        date_default_timezone_set('Africa/Kampala');
    }
    
    public function gen_uuid() 
    {
            return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }
    
    //Get an authentication token from the wallent that is used for authorization in calls to the collection API
    private function get_access_token()
    {
          $url='https://ericssonbasicapi2.azure-api.net/collection/token/';
          //Generate basic authentication string
           $auth=base64_encode("{$this->api_user}:{$this->api_key}");
          
          $json=json_encode(array());
          $headers = array(
                'type: POST',
                'Accept: */*',
                'Content-type: application/json',
                'Authorization: Basic '.$auth,
                'X-Target-Environment:'.$this->environment,
                'Ocp-Apim-Subscription-Key: '.$this->subscription_key_collection
            ); 
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
            curl_setopt($ch, CURLOPT_TIMEOUT,20);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch,CURLINFO_HEADER_OUT,true);
              
            $response = curl_exec($ch);  
            $info = curl_getinfo($ch);
            curl_close($ch);
            
            $token_data=json_decode($response,true);
            if(json_last_error()>0) return false;
            if(isset($token_data['access_token'])&&isset($token_data['expires_in']))
            {
                //You may need to store this token and use it in other requests without requesting for a new one.
                //This reduces the time for your requests
                return $token_data['access_token'];
            }
            return false;
            
    }
    
    //Request for payment from mobile money subscriber.
     public function request_payment($params)
     {
          $expected_params=array('amount','id','msisdn','payer_message','payee_note','x_reference_id');
          foreach($expected_params as $param)
          {
              if(!isset($params[$param]))
              {
                  exit($param.' parameter not set');
              }
          }
          
          $url='https://ericssonbasicapi2.azure-api.net/collection/v1_0/requesttopay';
          $token=$this->get_access_token();
          $request_parameters=array();
          $request_parameters['amount']=$params['amount'];
          $request_parameters['currency']=$this->currency;
          $request_parameters['externalId']=$params['id'];
          $request_parameters['payer']=array('partyIdType'=>'MSISDN','partyId'=>$params['msisdn']);
          $request_parameters['payerMessage']=$params['payer_message'];
          $request_parameters['payeeNote']=$params['payee_note'];
          
          $headers = array(
                'type: POST',
                'Accept: */*',
                'Content-type: application/json',
                'Authorization: Bearer '.$token,
                'X-Callback-Url:',
                'X-Reference-Id: '.$params['x_reference_id'],
                'X-Target-Environment: '.$this->environment,
                'Ocp-Apim-Subscription-Key: '.$this->subscription_key_collection
            );
      
           $json=json_encode($request_parameters);
          
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
            curl_setopt($ch, CURLOPT_TIMEOUT,15);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch,CURLINFO_HEADER_OUT,true);
             
            $response = curl_exec($ch);  
            $info = curl_getinfo($ch);
            curl_close($ch);
            $http_code=0;
            if(isset($info['http_code']))
            {
                $http_code=$info['http_code'];
            }
            
            $response_details=array('http_code'=>$http_code,'data'=>$response);
            return $response_details;
              
     }
     
     //Request for payment from mobile money subscribers.
     public function send_payment($params)
     {
          $expected_params=array('amount','id','msisdn','payer_message','payee_note','x_reference_id');
          foreach($expected_params as $param)
          {
              if(!isset($params[$param]))
              {
                  exit($param.' parameter not set');
              }
          }
          
          $url='https://ericssonbasicapi2.azure-api.net/disbursement/v1_0/transfer';
          $token=$this->get_access_token();    
          $request_parameters=array();
          $request_parameters['amount']=$params['amount'];
          $request_parameters['currency']=$this->currency;
          $request_parameters['externalId']=$params['id'];
          $request_parameters['payee']=array('partyIdType'=>'MSISDN','partyId'=>$params['msisdn']);
          $request_parameters['payerMessage']=$params['payer_message'];
          $request_parameters['payeeNote']=$params['payee_note'];
              
          $headers = array(
                'type: POST',
                'Accept: */*',
                'Content-type: application/json',
                'Authorization: Bearer '.$token,
                'X-Callback-Url:',
                'X-Reference-Id: '.$params['x_reference_id'],
                'X-Target-Environment: '.$this->environment,
                'Ocp-Apim-Subscription-Key: '.$this->subscription_key_disbursement
            );
      
           $json=json_encode($request_parameters);
          
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
            curl_setopt($ch, CURLOPT_TIMEOUT,15);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch,CURLINFO_HEADER_OUT,true);
             
            $response = curl_exec($ch);  
            $info = curl_getinfo($ch);
            curl_close($ch);
            $http_code=0;
            if(isset($info['http_code']))
            {
                $http_code=$info['http_code'];
            }
            
            $response_details=array('http_code'=>$http_code,'data'=>$response);
            return $response_details;
     }
     
     //Get collection transaction status
     public function get_collection_status($reference_id)
     {
          $url='https://ericssonbasicapi2.azure-api.net/collection/v1_0/requesttopay/'.$reference_id;
          $token=$this->get_access_token();
          $headers = array(
                'type: POST',
                'Accept: */*',
                'Content-type: application/json',
                'Authorization: Bearer '.$token,
                'X-Target-Environment:'.$this->environment,
                'Ocp-Apim-Subscription-Key: '.$this->subscription_key_collection
            ); 
         
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
            curl_setopt($ch, CURLOPT_TIMEOUT,15);
            curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
            curl_setopt($ch,CURLINFO_HEADER_OUT,true);
             
            $response = curl_exec($ch);  
            $info = curl_getinfo($ch);
            curl_close($ch);
            $http_code=0;
            if(isset($info['http_code']))
            {
                $http_code=$info['http_code'];
            }
            $response_details=array('http_code'=>$http_code,'data'=>$response);
            return $response_details;
     }
     
     //Get collection transaction status
     public function get_disbursement_status($reference_id)
     {
         $url='https://ericssonbasicapi2.azure-api.net/disbursement/v1_0/transfer/'.$reference_id;
         
         $token=$this->get_access_token();
         $headers = array(
            'type: POST',
            'Accept: */*',
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            'X-Target-Environment:'.$this->environment,
            'Ocp-Apim-Subscription-Key: '.$this->subscription_key_disbursement
        ); 
            
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
        curl_setopt($ch, CURLOPT_TIMEOUT,15);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);
         
        $response = curl_exec($ch);  
        $info = curl_getinfo($ch);
        curl_close($ch);
        $http_code=0;
        if(isset($info['http_code']))
        {
            $http_code=$info['http_code'];
        }
        $response_details=array('http_code'=>$http_code,'data'=>$response);
        return $response_details;
     }
     
     //Get Collection Account balace
     public function get_collection_balance()
     {
         $url='https://ericssonbasicapi2.azure-api.net/collection/v1_0/account/balance';
         $token=$this->get_access_token();
         $headers = array(
            'type: POST',
            'Accept: */*',
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            'X-Target-Environment:'.$this->environment,
            'Ocp-Apim-Subscription-Key: '.$this->subscription_key_collection
        ); 
            
         $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
        curl_setopt($ch, CURLOPT_TIMEOUT,15);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);
        $response = curl_exec($ch);  
        $info = curl_getinfo($ch);
        curl_close($ch);
        $http_code=0;
        if(isset($info['http_code']))
        {
            $http_code=$info['http_code'];
        }
        $response_details=array('http_code'=>$http_code,'data'=>$response);   
        
        return $response_details;      
     }
     
     //Get Disbursement Account balace
     public function get_disbursement_balance()
     {
         $url='https://ericssonbasicapi2.azure-api.net/disbursement/v1_0/account/balance';
         $token=$this->get_access_token();
         
         $headers = array(
            'type: POST',
            'Accept: */*',
            'Content-type: application/json',
            'Authorization: Bearer '.$token,
            'X-Target-Environment:'.$this->environment,
            'Ocp-Apim-Subscription-Key: '.$this->subscription_key_disbursement
        ); 
            
         $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,15);
        curl_setopt($ch, CURLOPT_TIMEOUT,15);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
        curl_setopt($ch,CURLINFO_HEADER_OUT,true);        
        $response = curl_exec($ch);  
        $info = curl_getinfo($ch);
        curl_close($ch);
        $http_code=0;
        if(isset($info['http_code']))
        {
            $http_code=$info['http_code'];
        }
        $response_details=array('http_code'=>$http_code,'data'=>$response);
        return $response_details;
     }
}