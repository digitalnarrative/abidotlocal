<?php
/*
 * Groups class code to do integration with buddypress groups
 **/

class bb_gcf_groups {
    
    function __construct(){
        $this->hooks();
    }
    
    function hooks() {
        add_action( 'bp_after_group_details_admin', array($this,'group_details_screen') );
        add_action( 'groups_group_details_edited', array($this,'save_fields_data'),1,1 );
    }
 
    /**
     * Output the fields on the groups detals screen
     **/
    function group_details_screen() {
        global $groups_template;		
	$group =& $groups_template->group;
        
        
        $all_fields = bb_gcf()->c->bb_gcf_main->get_all_fields();
        
        if(!empty($all_fields)) {
        
            foreach($all_fields as $field) {   
                echo '<div class="bb-gcf-field">';
                echo '<label>'.$field->field_name.'</label>';
                
                $field->field_type->_form(array(
                                                "field_id"=>$field->id,
                                                "value" => groups_get_groupmeta($group->id,"bb-gcf-{$field->id}",true)
                                                ));
                                
                
                echo '</div>';
            }
        
        }        
        
    }
    
    /**
     * Saves the fields data
     **/
    
    function save_fields_data($groupid) {
        
          
        $all_fields = bb_gcf()->c->bb_gcf_main->get_all_fields();
        
        if(!empty($all_fields)) {
      
            foreach($all_fields as $field) {
                
                if(isset($_POST["bb_gcf_{$field->id}"])) { //only allow when data is available
                    
                    
                    $value = $field->field_type->_update(array(
                                                "field_id"=>$field->id,
                                                "value" => sanitize_text_field($_POST["bb_gcf_{$field->id}"])
                                                ));
                    
                    groups_update_groupmeta($groupid,"bb-gcf-{$field->id}",$value); //update the value
                    
                }
                
            }
            
        }
        
        
    }
    
    /**
     * return the value
     **/
    
    function get_the_field_value($group_id,$field_id) {
        return groups_get_groupmeta($group_id,"bb-gcf-{$field_id}",true);
    }
}

/**
 * quick help to get group field value by id.
 **/
function bb_gcf_get_group_field_data($group_id,$field_id) {
    return bb_gcf()->c->bb_gcf_groups->get_the_field_value($group_id,$field_id);
}

/**
 * get the current group field value by id
 **/
function bb_gcf_get_current_group_field_data($field_id) {
    $group_id = bp_get_current_group_id();
    return bb_gcf_get_group_field_data($group_id,$field_id);
}