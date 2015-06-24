<?php
/**
* This class must be extended for each widget
*/

class bb_gcf_field {
    
    /**
     * Holds the type data
     **/
    public $type;
    
    
    /**
     * Holds the name data
     **/
    
    public $name;
    
    
    /**
     *  Hold the description of the field.
     **/
    
    public $description;
    
    
    /**
     * Css Classes 
     **/
    
    protected $css_classes = "bb-gcf-field";
    
    // Member functions that you must over-ride start.
    
    public function form(array $prop = array()){
        
        echo '<p>'.__("There are no input for this field.",bb_gcf()->domain).'</p>';
        
        return false;
        
    }
    
    
    public function update(array $prop = array()) {
        
        //this function must be overide.
        return $prop["value"];   
        
    }
    
    /*
     * This function must be overide
     * return "VALID" if user input is valid else return the error message.
     **/
    public function is_valid(array $prop = array()) {
        
        //this funtion must be overide.
        return "VALID";
        
    }
        
    // Member functions that you must over-ride end.
    
    
    /**
     * This will get the basic value for the field type.
     **/
    function  __construct($type,$name,$description) {
        
            $this->type = $type;
            $this->name  =  strip_tags($name);
            $this->description  =  strip_tags($description);
        
    }
    
    function _form(array $prop = array()) {
        
        $call_prop = array();
        $call_prop["value"] = (isset($prop["value"]))?$prop["value"]:"";
        $call_prop["field_id"] = $prop["field_id"];
        $call_prop["name"] = "bb_gcf_".$prop["field_id"];
        $call_prop["id"] = "bb_gcf_".$prop["field_id"];
        
        $this->form($call_prop);
        
    }
    
    function _update(array $prop = array()) {
               
        $call_prop = array();
        $call_prop["value"] = (isset($prop["value"]))?$prop["value"]:"";
        $call_prop["field_id"] = $prop["field_id"];
        
        return $this->update($call_prop);
        
    }
    
}