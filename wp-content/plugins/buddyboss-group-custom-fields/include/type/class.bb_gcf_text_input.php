<?php

class bb_gcf_text_input extends bb_gcf_field {
    
    
    function __construct() {
        
        parent::__construct( "text" , __("Text",bb_gcf()->domain) , __("Normal single line text field",bb_gcf()->domain) );
        
    }
    
    
    public function form(array $prop = array()){
        
        ?>
        <input type="text" class="bb-gcf-field <?php echo $this->css_classes; ?>" value="<?php echo esc_attr($prop["value"]); ?>" name="<?php echo $prop["name"]; ?>" id="gcf_<?php echo $prop["name"]; ?>" />
        <?php
        
    }
    
    
    public function update(array $prop = array()) {
        
        
        //this function must be overide.
        return $prop["value"];   
        
    }
    
    public function is_valid(array $prop = array()) {
        
        //this funtion must be overide.
        return "VALID";
        
    }
    
}
