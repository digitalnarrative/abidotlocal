<?php

class bb_gcf_textarea extends bb_gcf_field {
    
    
    function __construct() {
        
        parent::__construct( "textarea" , __("Multi-line Text",bb_gcf()->domain) , __("Multi-line Area text field",bb_gcf()->domain) );
        
    }
    
    
    public function form(array $prop = array()){
        
        ?>
        <textarea class="bb-gcf-field <?php echo $this->css_classes; ?>" name="<?php echo $prop["name"]; ?>" id="gcf_<?php echo $prop["name"]; ?>"><?php echo esc_textarea($prop["value"]); ?></textarea>
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
