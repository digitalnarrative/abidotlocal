<?php

require 'class-custom-field-revisions-settings.php';
require 'class-custom-field-revisions.php';

// Settings
$settings =  new up546E_UserPress_Revision_Settings();

// Do revisioning of custom fields
new up546E_Custom_Field_Revisions( $settings );
