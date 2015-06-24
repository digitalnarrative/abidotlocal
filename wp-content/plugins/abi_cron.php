<?php
/*
Plugin Name: Abi CronJob
Plugin URI: 
Description: Cron
Author: BuddyBoss
Version: 1.0.0
*/


#On plugin activation schedule our daily database backup 

register_activation_hook( __FILE__, 'abi_create_schedule' );

register_deactivation_hook( __FILE__, 'abi_remove_schedule' );

add_action("wp","abi_create_schedule"); 


add_filter( 'cron_schedules', 'abi_cron_schedules' );


function abi_cron_schedules( $schedules ){

	// Adds once weekly to the existing schedules.

	
	$schedules['in_per_one_minute'] = array(
										
		'interval'=>  60, 
										
		'display' => __('Once in 1 min')
										
	); 
	
	$schedules['fively'] = array(
							  
		'interval'=> 300,
							  
		'display' => __('Once in Five minutes')
							  
	);  



	$schedules['in_per_ten_minute'] = array(
										 
		'interval'=> 600,
										 
		'display' => __('Once in Ten minutes')
										 
	);  



	$schedules['in_per_hour'] = array(
								   
		'interval'=> 3600,
								   
		'display' => __('Once in One hour')
								   
	);  



	$schedules['in_per_two_hours'] = array(
										
		'interval'=> 7200,
										
		'display' => __('Once in Two Hours')
										
	);

	$schedules['in_per_six_hours'] = array(
										
		'interval'=>  1 * 6 * 60 * 60, //1 days * 6 hours * 60 minutes * 60 seconds
										
		'display' => __('Once in 6 Hours')
										
	);  

	$schedules['in_per_24_hours'] = array(
										
		'interval'=>  1 * 24 * 60 * 60, //1 days * 24 hours * 60 minutes * 60 seconds
										
		'display' => __('Once in 24 Hours')
										
	);  

	$schedules['in_per_12_hours'] = array(
										
		'interval'=>  1 * 12 * 60 * 60, //1 days * 12 hours * 60 minutes * 60 seconds
										
		'display' => __('Once in 12 Hours')
										
	);  



	return $schedules;

}



function abi_create_schedule(){

	

	if( !wp_next_scheduled( 'abi_fively_cron' ) ){

		wp_schedule_event( time(), 'fively', 'abi_fively_cron' );

	}

	if( !wp_next_scheduled( 'abi_in_per_one_minute' ) ){

		wp_schedule_event( time(), 'in_per_one_minute', 'abi_in_per_one_minute' ); 

	}

	if( !wp_next_scheduled( 'abi_in_per_ten_minute' ) ){

		wp_schedule_event( time(), 'in_per_ten_minute', 'abi_in_per_ten_minute' );

	}

	if( !wp_next_scheduled( 'abi_in_per_hour' ) ){

		wp_schedule_event( time(), 'in_per_hour', 'abi_in_per_hour' );

	}

	if( !wp_next_scheduled( 'abi_in_per_two_hours' ) ){

		wp_schedule_event( time(), 'in_per_two_hours', 'abi_in_per_two_hours' );

	}
	
	if( !wp_next_scheduled( 'abi_in_per_24_hours' ) ){

		wp_schedule_event( time(), 'in_per_24_hours', 'abi_in_per_24_hours' );

	}
	
	if( !wp_next_scheduled( 'abi_in_per_12_hours' ) ){

		wp_schedule_event( time(), 'in_per_12_hours', 'abi_in_per_12_hours' );

	}
	if( !wp_next_scheduled( 'abi_in_per_six_hours' ) ){

		wp_schedule_event( time(), 'in_per_six_hours', 'abi_in_per_six_hours' );

	}

}



function abi_remove_schedule(){
 
	wp_clear_scheduled_hook( 'abi_fively_cron' );

	wp_clear_scheduled_hook( 'abi_in_per_one_minute' );
	
	wp_clear_scheduled_hook( 'abi_in_per_ten_minute' );

	wp_clear_scheduled_hook( 'abi_in_per_hours' );

	wp_clear_scheduled_hook( 'abi_in_per_two_hours' );
	
	wp_clear_scheduled_hook( 'abi_in_per_24_hours' );
	
	wp_clear_scheduled_hook( 'abi_in_per_6_hours' );
	
	wp_clear_scheduled_hook( 'abi_in_per_12_hours' );

}

add_action("abi_fively_cron","abi_fively_cron");

function abi_fively_cron(){

	do_action("abi_fively_cron_hook");

}

add_action("abi_in_per_one_minute","abi_in_per_one_minute");

function abi_in_per_one_minute(){

	do_action("abi_in_per_one_minute_hook");

}

add_action("abi_in_per_ten_minute","abi_in_per_ten_minute");

function abi_in_per_ten_minute(){

	do_action("abi_in_per_ten_minute_hook");

}

add_action("abi_in_per_hour","abi_in_per_hour");

function abi_in_per_hour(){

	do_action("abi_in_per_hour_hook");

}

add_action("abi_in_per_two_hours","abi_in_per_two_hours");

function abi_in_per_two_hours(){

	do_action("abi_in_per_two_hours_hook");

}

add_action("abi_in_per_24_hours","abi_in_per_24_hours");

function abi_in_per_24_hours(){

	do_action("abi_in_per_24_hours_hook");

}

add_action("abi_in_per_12_hours","abi_in_per_12_hours");

function abi_in_per_12_hours(){

	do_action("abi_in_per_12_hours_hook");

}

add_action("abi_in_per_six_hours","abi_in_per_six_hours");

function abi_in_per_six_hours(){

	do_action("abi_in_per_six_hours_hook");

}



?>