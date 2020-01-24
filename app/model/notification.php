<?php

$sourceLink = "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";

if(@!$_SESSION['authorized']){
    //$view = $config['VIEW_PATH']. 'login.phtml';
    header('location: ?page=home');
}
else {

    $current_view_path = $config['VIEW_PATH'] . 'notification' . DS; //view path for this model
    $file = $config['DATA_PATH'] . 'notifications.txt'; //defines the path to the file
    $idFile = $config['DATA_PATH'] . 'notification-id.txt'; //defines the path to the notification id file
    $cn_file = $config['DATA_PATH'] . 'client-notifications.txt';
    $subMenu = $current_view_path . 'notification-menu.phtml';


    //action param changes the view
    switch (getParam('action')) {
        case 'menu':
        {
            $view = $current_view_path . 'default.phtml';
            $message = 'Welcome to the Notification Page';
            break;
        }
        case 'view':
        {
            $view = $current_view_path . 'view.phtml';
            $contents = returnTable('notifications');
            if (!is_array($contents)) {
                $message = $contents;
                $view = $current_view_path . 'default.phtml';
            }
            else{
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }

            break;
        }
        case 'add':
        {
            $view = $current_view_path . 'add-notification.phtml';
            $submitCheck = getParam('submitCheck');
            $msg1 = '';
            $msg2 = '';
            $new_notification_info = [
                getNextId($idFile),
                //replaceCommaAndEol(@$_POST['name']),
                @$_POST['name'],
                @$_POST['type'],
                //replaceCommaAndEol(@$_POST['content']), //function to avoid corrupting file with unwanted commas or EOL
                @$_POST['content'],
                'active'
            ];

            //this block runs when form has been submitted info is ready to add to file
            if ($submitCheck == 'true') {
                if ($new_notification_info[1] != '' && $new_notification_info[3] != '') {
                    $columns = ['notification_id', 'name', 'type', 'message_content', 'status'];
                    if(addTableLine('notifications', $columns, $new_notification_info)){
                        incrementId($idFile);
                        $message = 'Notification added successfully';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                        $view = $current_view_path . 'confirm-add.phtml';
                    }

                } else {
                    if ($new_notification_info[1] == '') {
                        $msg1 = 'This item cannot be empty';
                    }
                    if ($new_notification_info[3] == '') {
                        $msg2 = 'This item cannot be empty';
                    }

                }
            }

            break;
        }

        case 'update':
        {
            $view = $current_view_path . 'modify-notification.phtml';
            $id = getParam('id');
            //$line = getFileLineById($id, $file); //returns array based on id search, assigns to $line
            $line = getTableLineByColNameAndVal('notifications', 'notification_id', $id); // get a line from the table as an array
            $check = getParam('submitCheck');
            $msg1 = '';
            $msg2 = '';
            $new_notification_info = [
                $id,
                @$_POST['name'],
                @$_POST['type'],
                @$_POST['content']
            ];

            if ($check === 'true') {
                if ($new_notification_info[1] != '' && $new_notification_info[3] != '') {
                    $columns = ['notification_id', 'name', 'type', 'message_content'];
                    if (changeTableLineByCol('notifications', 'notification_id', $id, $columns, $new_notification_info)) {
                        $message = 'Notification updated successfully';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    }
                    else {
                        $message = 'The notification id was not found';
                    }
                    $view = $current_view_path . 'confirm-add.phtml';
                }
                else {
                    if ($new_notification_info[1] == '') {
                        $msg1 = 'This item cannot be empty';
                    }
                    if ($new_notification_info[3] == '') {
                        $msg2 = 'This item cannot be empty';
                    }
                }
            }

            break;
        }
        case 'archive':
        {
            $view = $current_view_path . 'archive-notification.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-archive':
        {
            $id = getParam('id');
            if(setStatus('notifications', 'status',$id, 'notification_id', 'archived')){
                $message = 'Notification archived';
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Notification could not be archived';
            }

            $view = $current_view_path . 'confirm-archive.phtml';
            break;
        }
        case 'activate':
        {
            $view = $current_view_path . 'activate-notification.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-activate':
        {
            $id = getParam('id');
            if(setStatus('notifications', 'status',$id, 'notification_id', 'active')){
                $message = 'Notification activated';
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Notification could not be activated';
            }

            $view = $current_view_path . 'confirm-statusChange.phtml';
            break;
        }
        case 'search':
        {
            $view = $current_view_path . 'search.phtml';
            break;
        }
        case 'searchResult':
        {
            $field = getParam('field');
            $searchTerm = getParam('searchTerm');
            if ($field != '' && $searchTerm != '') {

                $contents = searchTableByColumn('notifications', $field, $searchTerm);

                if(!is_array($contents)){
                    $message = $contents;
                    $view = $config['VIEW_PATH'] . 'default.phtml';
                }
                //success block
                else{
                    $view = $current_view_path . 'view.phtml';
                    logOperations($_SESSION['username'], getParam('page'), getParam('action'));

                    if (empty($contents)) {
                        $message = 'No matching entries were found';
                        $view = $current_view_path . 'default.phtml';
                    }
                }
            }
            else {
                $message = 'No search terms were entered';
                $view = $current_view_path . 'default.phtml';
            }
            break;
        }
    }
}