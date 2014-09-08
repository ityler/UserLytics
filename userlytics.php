<?php
/*	Userlytics - Twitter User and Followers Analytics
 *  - Using Twitter REST API (Users/Lookup) resource
 *	- To see who a users top followers are and data of all users followers.
 *	- Pass Twitter username as an argument | php userlytics.php <user_name>
 * 	- Performs requests in batches of (100) users(followers)
 * 		- Minimizes number of requests to API (current limit: 180 requests/interval)
 *		- Example: User with 1000 followers will only require 10 requests to return all data
 *	Returns data of: 
 *		Passed Username - 
 *			-> Number of followers
 *		Users followers -
 *			Returns fully-hydrated user objects of all <user_name> followers
 *			-> User profile data such as(full name,user-name,description,profile image)
 *			-> Number of followers
 *			-> Number of friends(Number of users this user is following)
 *			-> GPS data (if available)
 *			-> Date of account creation
 *			-> Verified status 
 *	Format: 
 *		-> Data pertaining to users followers is output to a CSV file
 *  Note:
 * 		-> If a requested user is unknown,suspended,or deleted, 
 *		   then that user will not be returned in the results list.
 *		-> You must be following a protected user to be able to see their 
 * 		   most recent status update. If you don't follow a protected user 
 *		   their status will be removed.
 * 		-> You must edit auth.php and insert your credentials from your 
 *		   dev.twitter.com account
 *
 */	

require_once("twitteroauth.php"); // Twitter API authenticate library
require_once("auth.php"); // API credentials

if (!isset($argv[1])){							// Check if user_name argument was passed
	echo "Syntax Error...\nUsage: userlytics.php <user_name>\n"; // Output to screen
} else { 										// Argument was passed
	$twitteruser = $argv[1];					// Get user_name 
	$fp = "output.txt";							// Log output file 
	$fh = fopen($fp,'w');						// Open log
	$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret); // Authenticate
	$user = $connection->get("https://api.twitter.com/1.1/followers/ids.json?screen_name=".$twitteruser."");	// Send request
	$idArr = array();							// Init array
	$followers = json_encode($user);			// Encode data
	$returnId = json_decode($followers, true);	// Decode data 
	$total = count($returnId['ids']);			// Get toal number of followers
	echo "Number of Followers: ".$total."\n";	// Output to screen
	foreach($returnId['ids'] as $val){			// Fill array with follower ids
		$idArr[] = $val;
	}
	$batchCount = 0;							// Number of batches processed
	$rqStr = ""; 								// Request string
	$idCnt = 0;									// Records in batch count
	foreach($idArr as $user_id){				// For each follower's id
		$lnStr = "User added:".$user_id."\n";
		fwrite($fh, $lnStr);
		$rqStr .= $user_id.",";					// Build string of user_id's
		$idCnt++;								// Increment followers in batch
		if($idCnt > 99){						// Batch is full - ready for processing
			$rqStr = rtrim($rqStr, ', ');		// Trim trailing comma
			$batchCount++;						// Increment batch count
			getUserInfo($rqStr);				// Get user data for id's in batch
			$btStr = "\n\n---- Batch processed ---- Batch: ".$batchCount." ---- Records: ".$idCnt." ----\n\n";	// Log output header
			fwrite($fh, $btStr);				// Write to log
			$rqStr = "";						// Reinit request string
			$idCnt = 0;							// Reinit records in batch count
		}
	}
	// If any remaining records in batch
	if($idCnt > 0){ 							// Any remaining records
		$btstr = "Batch leftovers: ".$idCnt." -- ".$batchCount."\n"; // Log output header
		fwrite($fh, $btStr);					// Write to log
		$rqStr = rtrim($rqStr, ', ');			// Trim trailing comma
		getUserInfo($rqStr);					// Get user data for id's in batch
		$batchCount++;							// Increment batch count
		$btStr = "\n\n---- Batch processed ---- Batch: ".$batchCount." ---- Records: ".$idCnt." ----\n\n";	// Log output header
		fwrite($fh, $btStr);					// Write to log
		$rqStr = "";							// Reinit request string
		$idCnt = 0;								// Reinit records in batch count
	}
	fclose($fh);								// Close log file
} // End of main


/* Authenticate with Twitter API */
function getConnectionWithAccessToken($cons_key,$cons_secret,$oauth_token,$oauth_token_secret){
  	$connection = new TwitterOAuth(
  						$cons_key, 				// Consumer key
  						$cons_secret, 			// Consumer sectret
  						$oauth_token, 			// Acces token
  						$oauth_token_secret		// Access token secret
  					);
  	return $connection;							// return connection object
}

/* Get data of users followers */
// @ param: String of comma seperated user_ids
function getUserInfo($followerIds){
	$consumerkey = "CONSUMER_KEY";
	$consumersecret = "CONSUMER_SECRET";
	$accesstoken = "ACCESS_TOKEN";
	$accesstokensecret = "ACCESS_TOKEN_SECRET";
	$fp = "userStats.csv";					// Data output file
	$fh = fopen($fp,'a');					// Append data to output file
	$connection = getConnectionWithAccessToken($consumerkey, $consumersecret, $accesstoken, $accesstokensecret);	// Authenicate
	$follower_batch = $connection->get("https://api.twitter.com/1.1/users/lookup.json?user_id=".$followerIds."");	// Send request
	$rt = $follower_batch;					// Data object returned
	foreach($rt as $user){					// For each user in result data
		$usrId = $user->id_str;				// User_id string
		$usrNam = $user->name;				// User display name
		$usrScrnam = $user->screen_name;	// User twitter handle
		$usrFolcnt = $user->followers_count;// Users follower count
		$usrFndcnt = $user->friends_count;	// Users friend count (Number users he/she follows)
		$usrDesc = $user->description;		// Users description
		$usrProimg = $user->profile_image_url;// Users profile image URL
		$usrUrl = $user->url;				// User added URL
		$usrLoc = $user->location;			// Users listed location
		$usrCrtdt = $user->created_at;		// Time (Wed Feb 27 17:05:24 +0000 2013)
		$usrGeo = $user->geo_enabled; 		// Boolean (0,1)
		$usrVer = $user->verified;			// Boolean (0,1)
		
		// Housekeeping of data (Strip delimiter user fields)
		$usrNam = str_replace('|',' ',$usrNam);
		$usrScrnam = str_replace('|',' ',$usrScrnam);
		$usrDesc = str_replace('|',' ',$usrDesc); 
		$usrLoc = str_replace('|',' ',$usrLoc);
		
		// Build string for user record
		$str =  $usrId."|".
				$usrNam."|".
				$usrScrnam."|".
				$usrFolcnt."|".
				$usrFndcnt."|".
				$usrDesc."|".
				$usrProimg."|".
				$usrUrl."|".
				$usrLoc."|".
				$usrCrtdt."|".
				$usrGeo."|".
				$usrVer;
		$str = trim(preg_replace('/\s+/',' ',$str)); // Remove new line characters
		$str .= "\n";							// Add new line
		fwrite($fh, $str);						// Write record to output file
	}
	fclose($fh);								// Close output file
}



/***** TO DO *****
- Which Followers of the username entered have the most followers. 
	- Top 10
- Have the most:
	- Tweets
	- Retweets
	- Favorites
	- Friends (Users they are following)
- Is the user verified?
*/
?>