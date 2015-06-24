<?php

class bb_gcf_admin {
    
    public $admin_slug = null;
    public $messages = array();
    
    function __construct() {
      $this->hooks();
    }
    
    private function hooks() {
	add_action('admin_menu', array($this,"admin_menu"));
        add_action('init', array($this,"setup"));
	add_action('init', array($this,"add_field_action"));
    }
    
    function setup() {
	
	$this->admin_slug = "buddyboss_custom_fields";
	$this->admin_link = admin_url('options-general.php?page='.$this->admin_slug);
	
    }
    
    public function admin_menu() {
        
        add_submenu_page( 'options-general.php', __("Group Custom Fields",bb_gcf()->domain), __("Group Custom Fields",bb_gcf()->domain), 'manage_options', $this->admin_slug, array($this,'admin_screen') );
        
    }
    
    public function admin_screen() {
	
	if(!current_user_can("manage_options")) {
	    return;
	}
        
	$msg = array(
		     1 => '<div class="message updated"><p>'.__("Field has been successfully added.",bb_gcf()->domain).'</p></div>',
		     2 => '<div class="message updated"><p>'.__("Field has been successfully updated.",bb_gcf()->domain).'</p></div>',
		     3 => '<div class="message error"><p>'.__("Error while deleting the field.",bb_gcf()->domain).'</p></div>',
		     4 => '<div class="message error"><p>'.__("Field has been deleted successfully.",bb_gcf()->domain).'</p></div>'
		     );
	
	$view = $_GET["view"];
	
        echo '<div class="wrap"> <h2>'.__("Group Custom Fields",bb_gcf()->domain).'</h2>';
	
        echo '<div class="bb-gcf-screen">';
	
	foreach($this->messages as $m) {
	    echo $m;
	}
	
	if(isset($_GET["msg"]) AND $msg[$_GET["msg"]]) {
	    echo $msg[$_GET["msg"]];
	}
	
	echo '<div class="screen-head">';
	
	echo '<a class="button-primary add-field-btn" href="'.$this->admin_link.'&view=add_field">'.__("Add Field",bb_gcf()->domain).'</a>';
	
	echo '</div>';
	
	
	if($view == "" || $view == "fields") {
	    
	    $this->screen_fields();
	    
	}
	elseif($view == "add_field") {
	    
	    $this->screen_add_field();
	    
	}
	
	
	echo '</div>';
	
	echo '</div>';
        
    }
    
    function screen_fields() {
    
	$all_fields = bb_gcf()->c->bb_gcf_main->get_all_fields();
    
	echo '<div class="fields-list">';
	
	
	if(empty($all_fields)) {
	    echo '<div class="info-no-fields"><p>'.__("Click Add field button to add fields.",bb_gcf()->domain).'</p></div>';
	}
	
	
	foreach($all_fields as $field) {
	    
	    $field_class = $field->field_type;
	    
	    echo '<div class="field-box '.$field_class->type.'">';
	    
	    echo '<h4>'.$field->field_name.' ('.$field_class->name.')</h4>';
	    
	    echo '<div class="desc">'.$field->field_desc.'</div>';
	    
	    echo '<div class="field-output">';
		
		$field_class->form();
		
	    echo '</div>';
	    
	    echo '<div class="controls">';
	    
	    echo '<a href="'.$this->admin_link.'&view=add_field&field_id='.$field->id.'" class="button edit">'.__("Edit",bb_gcf()->domain).'</a>';
	    
	    $delete_nonce = wp_create_nonce("bb_gcf_delete_field");
	    
	    echo '<a class="delete" href="'.$this->admin_link.'&delete='.$delete_nonce.'&field_id='.$field->id.'">'.__("Delete",bb_gcf()->domain).'</a>';
	    
	    echo '</div>';
	    
	    echo '</div>';
	    
	}
	
	echo '</div>';
	
	
    }
    
    function screen_add_field() {
	
	$field_id = (int) $_GET["field_id"];
	
	if(!empty($field_id)) {
	    
	    $field = bb_gcf()->c->bb_gcf_main->get_field($field_id);
	    
	    if(empty($field)) {
		echo '<div class="message error"><p>'.__("Field not found you requesting.",bb_gcf()->domain).'</p></div>';
		return false;
	    }
	    
	    $field_name = $field->field_name;
	    $field_desc = $field->field_desc;
	    $field_type = $field->field_type->type;
	    
	}
	
	?>
	
	<form action="<?php echo $this->admin_link.'&view=add_field&field_id='.$field_id; ?>" method="post" name="bb-gcf-add-field">
	    
	    <?php wp_nonce_field("add_gcf_field","add_gcf_field"); ?>
	    
	    
	    <table class="form-table">
		
		<tr>
		    
		    <th> <label><?php _e("Field Name",bb_gcf()->domain); ?></label> </th>
		    <td> <input name="field_name" type="text" value="<?php echo $field_name; ?>" class="regular-text" /> </td>
		    
		</tr>
		
		
		<tr>
		    
		    <th> <label><?php _e("Field Description",bb_gcf()->domain); ?></label> </th>
		    <td> <textarea name="field_desc" rows="10" cols="40" class="large-text"><?php echo $field_desc; ?></textarea> </td>
		    
		</tr>
		
		<tr>
		    
		    <th> <label><?php _e("Field Type",bb_gcf()->domain); ?></label> </th>
		    <td> 
		    
			<select name="field_type">
			<?php
			  $field_types = bb_gcf_get_all_fields_type();
			
			  foreach($field_types as $t) {
			    echo '<option value="'.$t->type.'" '.selected($field_type,$t->type).'>'.$t->name.'</option>';
			  }
			  
			?>
			</select>
		    
		    </td>
		    
		</tr>
		
	    </table>
	    
	    <div class="button-control submit">
		<input type="submit" value="Save" name="saveField" id="saveField" class="button-primary">
	    </div>
	    
	</form>
	
	<?php
	
    }
    
    function add_field_action() {
	
	if(!current_user_can("manage_options")) {
	    return;
	}
	
	if(!isset($_GET["page"])) { return; }
	if($_GET["page"] != $this->admin_slug) {  return; }
	
	
	
	/**
	 * Trigger when delete requested.
	 **/
	if(isset($_GET["delete"]) AND wp_verify_nonce($_GET["delete"],"bb_gcf_delete_field")) {
	    
	    $field_id = (int) $_GET["field_id"];
	    $field = bb_gcf()->c->bb_gcf_main->get_field($field_id);
		
	    if(empty($field)) {
		    wp_redirect($this->admin_link."&msg=3");
		    exit;
	    }
	    
	    //all looks fine
	    global $wpdb;
	    $wpdb->query("DELETE FROM {$wpdb->prefix}bb_gcf WHERE id='{$field_id}'");
	    
	    wp_redirect($this->admin_link."&msg=4");
	    exit;    
	    
	}
	
	/*
	 * Trigger when add or update requested
	 **/
	if(isset($_POST["add_gcf_field"]) AND wp_verify_nonce($_POST["add_gcf_field"],"add_gcf_field")) {
	    
	    
	    $field_id = (int) $_GET["field_id"];
	    
	    
	    //verify the field when its given
	    if(!empty($field_id)) {
	    
		$field = bb_gcf()->c->bb_gcf_main->get_field($field_id);
		
		if(empty($field)) {
			$this->messages[] = '<div class="message error"><p>'.__("Trying to update invalid object.",bb_gcf()->domain).'</p></div>';
			return false;
		}
	    
	    }
	    
	    
	    $field_type = sanitize_text_field($_POST["field_type"]);
	    $field_name = sanitize_text_field($_POST["field_name"]);
	    $field_desc = sanitize_text_field($_POST["field_desc"]);
	    
	    $field_types = bb_gcf_get_all_fields_type();
			
	    if(!isset($field_types[$field_type])) {
		$this->messages[] = '<div class="message error"><p>'.__("Field type not found try another one.",bb_gcf()->domain).'</p></div>';
		return false;
	    }
	    
	    if(empty($field_name)) {
		$this->messages[] = '<div class="message error"><p>'.__("Cannot leave the field name empty.",bb_gcf()->domain).'</p></div>';
		return false;
	    }
	    
	    global $wpdb;
	    
	    if(empty($field_id)) {
		
		$wpdb->insert($wpdb->prefix."bb_gcf",
			    array(
				  "field_name"=>$field_name,
				  "field_desc"=>$field_desc,
				  "field_type"=>$field_type,
				  )      
			);
		
		if(!empty($wpdb->insert_id)){
		    
		    wp_redirect($this->admin_link."&msg=1");
		    exit;
		    	    	    
		} else {
		    
		    $this->messages[] = '<div class="message error"><p>'.__("Having an error while creating field try later.",bb_gcf()->domain).'</p></div>';
		    return false;
		    
		}
		
	    } else {
		
		//all well now we can update stuff :D.
		$wpdb->update($wpdb->prefix."bb_gcf",
			    array(
				  "field_name"=>$field_name,
				  "field_desc"=>$field_desc,
				  "field_type"=>$field_type,
				  ),
			    array(
				"id"=>$field_id
			    )
			);
		
		wp_redirect($this->admin_link."&msg=2");
		exit;
		
	    }
	    
	}
	
	
    }
    
}

?>