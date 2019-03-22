<?php
 
require_once('mtn.php');
$momo=new momo;

if ( isset( $_POST['submit'] ) ) {

// retrieve the form data by using the element's name attributes value as key

$number = $_REQUEST['number'];
$ammount = $_REQUEST['ammount'];
$user_id = $momo->gen_uuid();
$transaction_id = $user_id.$number ;
}
//Request Payment
$params=array('amount'=> $ammount,'id'=>$transaction_id,'msisdn'=> $number,'payer_message'=>'test','payee_note'=>'test','x_reference_id'=>$user_id);
$response=$momo->request_payment($params);
//print_r($params);
print_r($response); exit;
//get status for request payment
//$ref=$user_id;
//$response=$momo->get_collection_status($ref);
//print_r($response); exit;

}
//Send Payment
/*$params=array('amount'=>1000,'id'=>1,'msisdn'=>'256773250433','payer_message'=>'test','payee_note'=>'test','x_reference_id'=>$momo->gen_uuid());
$response=$momo->send_payment($params);
print_r($params);
print_r($response); exit;*/

//get status for request payment
/*$ref='3254f490-a752-4c58-8525-53597613331b';
$response=$momo->get_disbursement_status($ref);
print_r($response); exit;*/

//Get collection account balance
/*$response=$momo->get_collection_balance();
print_r($response); exit; */

//Get disbursement account balance
/*$response=$momo->get_disbursement_balance();
print_r($response); exit;*/ 

