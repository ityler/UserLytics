<?php 
/*	rate_limit - Returns information on a users Twitter API rate limit
 *	Optional:
 *		- Pass rate limit cargeory </this/category>
 *	Returns:
 *		Passed Username - 
 *			-> Number of followers
 *		Users followers -
 */

require_once("twitteroauth.php"); 			// Path to twitteroauth library
require_once("auth.php");					// Authenitcation credentials
date_default_timezone_set('US/Eastern');	// System default timezone

$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);		// Authenticate
getRateLimit($connection);	// Get rate limit


/* Authenticate with Twitter API */
function getConnectionWithAccessToken($cons_key,$cons_secret,$oauth_token,$oauth_token_secret) {
  $connection = new TwitterOAuth($cons_key, $cons_secret, $oauth_token, $oauth_token_secret);
  return $connection;
}

/* Return current rate (limit,remaining,reset) */
// @ param: Authentication Access Token String
function getLookupLimit($connection){
	$rateLimitReq = $connection->get("https://api.twitter.com/1.1/application/rate_limit_status.json?resources=users");
	$rlo = json_encode($rateLimitReq);									// Encode object
	$rlr = json_decode($rlo, true);										// Decode JSON
	$lim = $rlr["resources"]["users"]["/users/lookup"]["limit"];		// Get limit
	$rem = $rlr["resources"]["users"]["/users/lookup"]["remaining"];	// Get remaining
	$res = $rlr["resources"]["users"]["/users/lookup"]["reset"];		// Get reset time
	$res = date("F j, Y, g:i a",$res);									// Convert unix time-stamp
	/* Screen Output */
	echo "\n** Twitter API **\n";
	echo "* Users/Lookup Rate-Limit *\n";
	echo "Limit: ".$lim."\n";
	echo "Remaining: ".$rem."\n";
	echo "Reset: ".$res."\n";
}

function getRateLimit($connection){
	$rateLimitReq = $connection->get("https://api.twitter.com/1.1/application/rate_limit_status.json?resources=users");
	$rlo = json_encode($rateLimitReq);									// Encode object
	$rlr = json_decode($rlo, true);										// Decode JSON
	$res = array();
	$res = $rlr["resources"]["users"];
	var_dump($res);
	foreach($res as $result){
		$limit = $result["limit"];
		echo "Resource: "."\n";
		echo "Limit: ".$limit."\n";
	}
}
?>