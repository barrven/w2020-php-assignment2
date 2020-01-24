<?php

$sourceLink = "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";


if(@!$_SESSION['authorized']){
    header('location: ?page=home');
}
else {

    $current_view_path = $config['VIEW_PATH'] . 'client-notification' . DS; //view path for this model
    $idFile = $config['DATA_PATH'] . 'cn-id.txt'; //defines the path to the notification id file
    $subMenu = $current_view_path . 'cn-menu.phtml';

    //action param changes the view
    switch(getParam('action')){
        case 'menu':
        {
            $view = $current_view_path . 'default.phtml';
            $message='Welcome to the Client Notification Page';
            break;
        }
        case 'viewByClient':
        {
            //columns to select in the query
            $columns = ['id','company_name','contact_fname','contact_lname','company_phone','company_cell',
                'company_email','website', 'status'];

            //get only those clients that have notifications
            $clientsList = joinTablesByDistinctVal('clients', 'client_notifications', 'id', 'client_id', $columns);
            if (!is_array($clientsList)) {
                $message = $clientsList;
                $view = $config['VIEW_PATH'] . 'default.phtml';
            }
            else{
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                $view = $current_view_path . 'view-byClient.phtml';
            }

            break;
        }
        case 'viewNoteByClient':
        {

            $cid = getParam('id'); //client id for adding to subquery argument

            //list of columns to include in query
            $colList = ['cn_id', 'n.notification_id','name', 'type', 'start_date', 'time', 'frequency', 'cn_status'];

            $cnList = joinTablesOnSubquery('client_notifications as cn', 'notifications as n',
                'cn.notification_id', 'n.notification_id', $colList, 'client_id', $cid);
            if (!is_array($cnList)) {
                $message = $cnList;
                $view = $config['VIEW_PATH'] . 'default.phtml';
            }
            else{
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                $view = $current_view_path . 'view-byClient-result.phtml';
            }

            break;
        }
        case 'add':
        {

            $clientIds = getTableColumn('clients', 'id');
            $clientInfo = selectTableCols('clients', 'id','company_name', 'contact_fname', 'contact_lname');
            $notifIds = getTableColumn('notifications', 'notification_id');
            $notifInfo = selectTableCols('notifications', 'notification_id', 'name', 'type');


            $view = $current_view_path . 'add-cn.phtml';
            $submitCheck = getParam('submitCheck');
            $new_cn_info = [
                getNextId($idFile),
                @$_POST['client-id'],
                @$_POST['notification-id'],
                @$_POST['date'],
                @$_POST['time'],
                @$_POST['frequency'],
                'active'
            ];

            //this block runs when form has been submitted and info is ready to add to file
            if($submitCheck == 'true'){
                $msgs = createEmptyItemNote($new_cn_info);
                if(checkMgsArray($msgs)){
                    //addFileLine($file,$new_cn_info);
                    $columns = ['cn_id','client_id','notification_id','start_date','time', 'frequency','cn_status'];
                    if (addTableLine('client_notifications', $columns, $new_cn_info)){
                        incrementId($idFile);
                        $message = 'Client Notification added successfully';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                        $view = $current_view_path . 'confirm-add.phtml';
                    }
                    else{
                        $message = 'Client Notification could not be added';
                        $view = $current_view_path . 'default.phtml';
                    }

                }
            }

            break;
        }

        case 'update':
        {
            $view = $current_view_path . 'modify-cn.phtml';
            $id = getParam('id');
            $clientInfo = selectTableCols('clients', 'id','company_name', 'contact_fname', 'contact_lname'); //info to populate the dropdown list
            $notifInfo = selectTableCols('notifications', 'notification_id', 'name', 'type'); // get specific columns from the notification table
            $line = getTableLineByColNameAndVal('client_notifications', 'cn_id', $id); //$line is a 1d array with the information from the record to be updated

            if (!is_array($line)){
                $message = $line;
                $view = $config['VIEW_PATH'] . 'default.phtml';
            }

            $submitCheck = getParam('submitCheck');
            $new_cn_info = [
                $id,
                @$_POST['client-id'],
                @$_POST['notification-id'],
                @$_POST['date'],
                @$_POST['time'],
                @$_POST['frequency'],
                $line[6]
            ];


            if($submitCheck === 'true'){
                $columns = ['cn_id','client_id','notification_id','start_date','time', 'frequency','cn_status'];
                $msgs = createEmptyItemNote($new_cn_info);
                if(checkMgsArray($msgs)) {
                    if (changeTableLineByCol('client_notifications', 'cn_id', $id ,$columns, $new_cn_info)) {
                        $message = 'Notification updated successfully';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    } else {
                        $message = 'The notification id was not found';
                    }

                    $cid = getParam('cid');
                    $view = $current_view_path . 'confirm-update.phtml';
                }
            }

            break;
        }
        case 'archive':
        {
            $id = getParam('id');
            $view = $current_view_path . 'archive-cn.phtml';
            break;
        }
        case 'commit-archive':
        {
            $id = getParam('id');

            if(setStatus('client_notifications', 'cn_status', $id,'cn_id','archived')){
                $message = 'Notification archived';
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Notification could not be archived';
            }

            $view = $current_view_path . 'cn-confirm-archive.phtml';
            break;
        }
        case 'activate':
        {
            $allowed = false;
            $id = getParam('id');
            $cid = getParam('cid');
            $nid = getParam('nid');

            //check that neither the client or notification is archived
            //cannot activate an event where either the client or note is archived
            $cStatusCheck = rowValMatches('clients', 'status', $cid, 'id', 'active');
            $nStatusCheck = rowValMatches('notifications', 'status',$nid, 'notification_id', 'active');

            if($cStatusCheck && $nStatusCheck){
                $allowed = true;
                $message = 'Are you sure you would like to activate this notification?';
            }
            else{
                $message = 'This item cannot be activated. Client or Notification is not available';
            }

            $view = $current_view_path . 'activate-cn.phtml';
            break;
        }
        case 'commit-activate':
        {
            $id = getParam('id');
            if(setStatus('client_notifications', 'cn_status', $id,'cn_id','active')){
                $message = 'Client notification activated';
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Client notification could not be activated';
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

                $cn_List = searchTableByColumn('client_notifications', $field, $searchTerm);

                if(!is_array($cn_List)){
                    $message = $cn_List;
                    $view = $config['VIEW_PATH'] . 'default.phtml';
                }
                //success block
                else{
                    logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    $view = $current_view_path . 'cn-view.phtml';

                    if(empty($cn_List)){
                        $message = 'No matching entries were found';
                        $view = $current_view_path . 'default.phtml';
                    }
                }
            }
            else{
                $message = 'No search terms were entered';
                $view = $current_view_path . 'default.phtml';
            }
            break;
        }
    }
}