<?php
ob_start();

/* ROOT SETTINGS */ require($_SERVER['DOCUMENT_ROOT'].'/root_settings.php');

/* FORCE HTTPS FOR THIS PAGE */ forcehttps();

/* WHICH DATABASES DO WE NEED */
$db2use = array(
	'db_auth' 	=> TRUE,
	'db_main'	=> TRUE
);

/* GET KEYS TO SITE */ require($path_to_keys);

/* LOAD FUNC-CLASS-LIB */
require_once('classes/phnx-user.class.php');
require_once('libraries/stripe/Stripe.php');
Stripe::setApiKey($apikey['stripe']['secret']);

/* PAGE VARIABLES */
$currentpage = 'account/';


// create user object
$user = new phnx_user;

// check user login status
$user->checklogin(2);

switch($user->login()){
    case 0:
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/login/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 1:
        $user->regen();
        $db_auth->close();
        $db_main->close();
        header('Location: '.$protocol.$site.'/account/verify/?redir='.$currentpage,TRUE,303);
        ob_end_flush();
        exit;
        break;
    case 2:
        $user->regen();
        break;
    default:
        $db_auth->close();
        $db_main->close();
        ob_end_flush();
        print'You created a tear in the space time continuum.';
        exit;
        break;
}

$R_userdeets = $db_main->query("SELECT * FROM users WHERE userid = ".$user->id." LIMIT 1");
if($R_userdeets !== FALSE){
	$userdeets = $R_userdeets->fetch_assoc();
	$R_userdeets->free();

	try {

		$cust = Stripe_Customer::retrieve($userdeets['stripeID']);

		if($cust['cards']['total_count'] !== 0){
			$card_info = $cust->cards->data;
			$card_num = '&#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; &#183;&#183;&#183;&#183; '.$card_info[0]['last4'];
			$brand = $card_info[0]['brand'];
			$exp_month = sprintf('%02d', $card_info[0]['exp_month']);
			$exp_year = $card_info[0]['exp_year'];
			$card_button_text = 'Update Card';
			$delete_disabled = false;
		}else{
			$card_button_text = 'Add Card';
			$delete_disabled = true;
		}
	} catch(Stripe_CardError $e) {

		// this still needs to show the form in case of expired cards that were already on the account

		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception card error)';
	} catch (Stripe_InvalidRequestError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception invalid request)';
	} catch (Stripe_AuthenticationError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception authentication)';
	} catch (Stripe_ApiConnectionError $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception api connection)';
	} catch (Stripe_Error $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception general)';
	} catch (Exception $e) {
		$msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: stripe exception generic)';
	}



	try {



		$sub_response = Stripe_Customer::retrieve($userdeets['stripeID'])->subscriptions->all();
		$subs = $sub_response->data;

		if(empty($subs)){
			$status = 'You do not have an active subscription.';
			$sub_button_text = 'Subscribe Now';
		}else{
			$status = $subs[0]['status'];
			$sub_button_text = 'Cancel';
			if($subs[0]['cancel_at_period_end'] === true){
				$status .= ' - Your subscription is paid in full until '.date('M j Y', $subs[0]['current_period_end']).' at which point it will be canceled.';
				$sub_button_text = 'Resume Subscription';
			}
		}





	} catch(Stripe_CardError $e) {

		// this still needs to show the form in case of expired cards that were already on the account

		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception card error)';
	} catch (Stripe_InvalidRequestError $e) {
		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception invalid request)';
	} catch (Stripe_AuthenticationError $e) {
		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception authentication)';
	} catch (Stripe_ApiConnectionError $e) {
		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception api connection)';
	} catch (Stripe_Error $e) {
		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception general)';
	} catch (Exception $e) {
		$sub_msg = 'There was an error determining your subscription status. Please refresh the page and try again. (ref: stripe exception generic)';
	}


}else{
	$sub_msg = 'There was an error determining your card status. Please refresh the page and try again. (ref: user details fail)';
}


ob_end_flush();
/* <HEAD> */ $head='
    <script type="text/javascript" src="https://js.stripe.com/v2/"></script>
    <script type="text/javascript">
        Stripe.setPublishableKey(\''.$apikey['stripe']['public'].'\');
    </script>
'; // </HEAD>
/* PAGE TITLE */ $title='Helmar Brewing Co';
/* HEADER */ require('layout/header0.php');


/* HEADER */ require('layout/header2.php');
/* HEADER */ require('layout/header1.php');


print'
	<div class="account">
		<h1 class="pagetitle">Account</h1>

		<div class="yourinfo">
			<h2>Your Info</h2>
			<dl>
				<dt>Username</dt>
				<dd>'.$user->username.'</dd>
				<dt>First Name</dt>
				<dd>'.$user->firstname.'</dd>
				<dt>Last Name</dt>
				<dd>'.$user->lastname.'</dd>
				<input type="button" value="Update Info" />
				<hr />
				<dt>Email</dt>
				<dd id="account-email">'.$user->email.'</dd>
				<input type="button" value="Change Email" onclick="changeEmail(1)" />
			</dl>
		</div>

		<div class="active-logins">
			<h2>Active Logins</h2>
			<ul>
	';

	foreach($user->get_active_logins() as $login){
		print'
				<li>
					Last accessed on <span>'.date("M j Y",$login['logintime']).'</span> at <span>'.date("g:ia",$login['logintime']).'</span><br />from IP address <span>'.$login['IP'].'</span> with <span>'.$login['browser']['parent'].'</span> on <span>'.$login['browser']['platform'].'</span>
					<input type="button" value="Log out device" />
				</li>
		';
	}

	print'
			</ul>
			<form action="logout/all/" method="post">
				<input type="submit" value="Invalidate all logins" />
			</form>
		</div>

		<div>
			<h2>Helmar Gold</h2>
			<h3>Credit Card</h3>
			<form action="" method="POST" id="payment-form">
				<div class="payment-errors" id="payment-errors">'.$msg.'</div>
				<label>Card Number</label>
				<input type="text" size="20" id="card_number" data-stripe="number" value="'.$card_num.'" />
				<label>CVC</label>
				<input type="text" size="4" id="cvc" data-stripe="cvc"/>
				<label>Expiration (MM/YYYY)</label>
				<input type="text" size="2" id="exp_month" data-stripe="exp-month" value="'.$exp_month.'"/>
				<span> / </span>
				<input type="text" size="4" id="exp_year" data-stripe="exp-year" value="'.$exp_year.'"/>
				<button id="add_update_card" type="submit">'.$card_button_text.'</button>
			</form>
			<button id="delete_card" onclick="deleteCard()"';if($delete_disabled){print' disabled';}print'>Delete Card</button>
			<h3>Subscription</h3>
			<p>Helmar Gold costs $9.99 per month.</p>
			<p id="sub_error">'.$sub_msg.'</p>
			<p>Status: <span id="sub_status">'.$status.'</p>
			<!-- maybe insert toggle switch here instead after a transition to SASS/Bourbon/Neat -->
			<button id="sub_button" onclick="toggleSub()">'.$sub_button_text.'</button>
		</div>
	</div>
';

/* FOOTER */ require('layout/footer1.php');


$db_auth->close();
$db_main->close();
?>
