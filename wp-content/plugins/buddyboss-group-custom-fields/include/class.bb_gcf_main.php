<?php
/*
 * The plugin core code file.
 **/
class bb_gcf_main {
	
	/**
	 * hold the fields type
	 **/
	
	public $field_types;
	
	function __construct() {
		$this->register_types();
		$this->hooks();	
	}
	
	function hooks() {
		
	}
	
	/**
	 * Register the core field types.
	 **/
	function register_types() {
		
		$classes = array(
			       "bb_gcf_text_input",
			       "bb_gcf_textarea"
			       );
		
		foreach($classes as $c) {
			$file = dirname(__FILE__)."/type/class.".$c.".php";
			if(file_exists($file)) {
			    include(dirname(__FILE__)."/type/class.".$c.".php");
			    $this->register_field($c); //register it.
			}
		}
		
	}
	
	function get_all_fields() {
		global $wpdb;
		
		$fields = $wpdb->get_results("SELECT *FROM {$wpdb->prefix}bb_gcf");
		$return = array();
		
		$field_types = bb_gcf_get_all_fields_type();
		
		foreach($fields as $f) {
			if(isset($field_types[$f->field_type])) {
				$f->field_type = $field_types[$f->field_type];
				$return[] = $f;
			}
		}
		
		return $return;
	}
	
	function get_field($field_id) {
		
		global $wpdb;
		
		$field_id = (int) $field_id;
		
		$field = $wpdb->get_row("SELECT *FROM {$wpdb->prefix}bb_gcf WHERE id='{$field_id}'");
		
		$field_types = bb_gcf_get_all_fields_type();
		
		if(!isset($field_types[$field->field_type])) {
			return false;
		}
		
		$field->field_type = $field_types[$field->field_type];
		
		return $field;
		
	}
	
	/*
	 * Register field type.
	 **/
	function register_field($c) {
		if (is_subclass_of($c, 'bb_gcf_field')) {
			$cc = new $c;
			$this->field_types[$cc->type] = $cc; 
		}
		
	}
		
	
	
}



function bb_gcf_register_field($c) {
	return bb_gcf()->c->bb_gcf_main->register_field($c);
	
}

function bb_gcf_get_all_fields_type() {
	return  bb_gcf()->c->bb_gcf_main->field_types;
}