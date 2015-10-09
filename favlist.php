<?php 

	include "./backend-lib.php";
		
	//Connect to the db
	tvdbconnect();
	
	/** User validation goes here **/
	$user=2; 
	
	// Get the favlist json
	favlist($user);
	

?>
	