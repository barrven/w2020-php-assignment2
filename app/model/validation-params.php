<?php
//list of required fields and their names on the post array - > to generalize this, get the first row of each file
$params = ['company_name','contact_fname','contact_lname','company_phone','company_cell','company_email'];
$optParam = 'website';
$formats = ['name', 'name', 'name', 'phone', 'phone', 'email', 'url']; //required format types to be passed to correctFormat function
$new_client_info = []; //array to hold all the correctly formatted information submitted
$errorMsg = []; //array to hold error messages if field is incorrectly formatted or not present
$check = @$_POST['submitCheck']; //reads from hidden form element, checks if form has been submitted

$sourceLink .= "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";