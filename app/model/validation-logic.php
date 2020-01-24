<?php
//$ncIndex = 1;
for ($i = 0; $i<sizeof($params); $i++) {
    $item = format_input(@$_POST[$params[$i]]);
    if (!empty($item)) {
        if (correctFormat($item, $formats[$i])) { //if item is correct format, add to client info
            $new_client_info[$ncIndex] = $item;
            $ncIndex++;
        } else {
            $errorMsg[$i] = 'This item is incorrectly formatted';
        }

    } else {
        $errorMsg[$i] = 'This item is required';
    }
}
//separate check for optional item
$optItem = format_input(@$_POST[$optParam]);
if(!empty($optItem)){
    if(correctFormat($optItem, 'url')){
        $new_client_info[$ncIndex] = $optItem;
    }
    else{
        $errorMsg[$i] = 'This item is incorrectly formatted';

    }
}

$sourceLink .= "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";