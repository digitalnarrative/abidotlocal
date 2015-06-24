<?php

	
	// Get the JSON string
 $jsonstring = stripslashes($_GET['jsonstring']);

	
	// Decode it into an array
 $jsonDecoded = json_decode($jsonstring, true, 64);
	
//print_r($jsonstring);

	/* Function to parse the multidimentional array into a more readable array 
	 * Got help from stackoverflow with this one:
	 *    http://stackoverflow.com/questions/11357981/save-json-or-multidimentional-array-to-db-flat?answertab=active#tab-top
	*/
	function parseJsonArray($jsonArray, $parentID = 0)
	{
	 $return = array();
	  if (is_array($jsonArray)) {
	   foreach ($jsonArray as $subArray) {
		 $returnSubSubArray = array();
		 if (isset($subArray['children'])) {
		   $returnSubSubArray = parseJsonArray($subArray['children'], $subArray['id']);
		 }
		 $return[] = array('id' => $subArray['id'], 'parentID' => $parentID);
		 $return = array_merge($return, $returnSubSubArray);
	  }
		}
	  return $return;
	}
	
	
	
	
	// Dump the array to debug
	//var_dump(parseJsonArray($jsonDecoded));
	
		
	
	// Run the function above
	$readbleArray = parseJsonArray($jsonDecoded);

    function up546E_find_wp_config_path() {
        $dir = dirname(__FILE__);
        do {
            if( file_exists($dir."/wp-config.php") ) {
                return $dir;
            }
        } while( $dir = realpath("$dir/..") );
        return null;
    }
	
	
    include( up546E_find_wp_config_path()  . '/wp-config.php' );

// Loop through the "readable" array and save changes to DB

		global $wpdb;
		$tableprfix= $wpdb->prefix;
		$text = '';
	if (is_array($readbleArray)) {
		foreach ($readbleArray as $key => $value ) {
		
			// $value should always be an array, but we do a check
			if (is_array($value)) {
				if (!is_int($value['id'])) { $output = $key; }
			
				// Update DB
				$query = "UPDATE $wpdb->posts SET menu_order='". $key ."', post_parent='".$value['parentID']."' WHERE ID='".$value['id']."';";
				
				$result = $wpdb->query($query); 
				//or die(mysql_error());
			}
		}
	}
	
	
	// Echo status message for the update
	//echo "The  was updated ".date("y-m-d H:i:s")."!";
	if (!empty($output)) echo $output;
	
