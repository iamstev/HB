<?php

/* TEST FOR SUBMISSION */ if(empty($_POST)){print'<p style="font-family:arial;">Nothing to see here, move along.</p>';exit;}

ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ if($use_https === TRUE){if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == ""){header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);exit;}}

/* WHICH DATABASES DO WE NEED */
	$db2use = array(
		'db_auth'		=> TRUE,
		'db_main'		=> TRUE
	);
//

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
	require_once('classes/phnx-user.class.php');
	require_once('libraries/stripe/Stripe.php');
//

/* PAGE VARIABLES */
$token = $_GET['t'];
//

$user = new phnx_user;
$user->checklogin(2);
if($user->login() === 2){
	
	$json = array(
		'error' => '0',
		'msg' => 'Great.'
	);
	
	
	
}else{
	$json = array(
		'error' => '1',
		'msg' => 'You must be logged in to make changes to your card.  Please refresh the page and try again.'
	);
}


$db_main->close();
$db_auth->close();
header('Cache-Control: no-cache, must-revalidate');
header('Content-type: application/json');
print json_encode($json);
ob_end_flush();

?>