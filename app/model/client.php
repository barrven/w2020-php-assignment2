<?php

$sourceLink = "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";

if(@!$_SESSION['authorized']){
    //$view = $config['VIEW_PATH']. 'login.phtml';
    header('location: ?page=home');
}
else{

    $current_view_path = $config['VIEW_PATH'] . 'client' . DS; //view path for this model
    $validation_params = $config['MODEL_PATH'] . 'validation-params.php';
    $validation_logic = $config['MODEL_PATH'] . 'validation-logic.php';
    $idFile = $config['DATA_PATH'] . 'client-id.txt';
    $subMenu = $current_view_path . 'client-menu.phtml';

    //action param changes the view
    switch(getParam('action')){
        case 'menu':
        {
            $view = $current_view_path . 'default.phtml';
            $message='Welcome to the Client Page';
            break;
        }
        case 'view':
        {
            $view = $current_view_path . 'view.phtml';
            $contents = returnTable('clients');
            if(!is_array($contents)){
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
            $view = $current_view_path . 'add-client.phtml';
            require $validation_params;

            //if the form has been submitted, loop through the params, check if they are empty and if they are in the correct format
            if($check === 'true') {
                $new_client_info[0] = getNextId($idFile);

                $ncIndex = 1;
                require $validation_logic;

                //this block runs when all required data is present and info is ready to add to file
                if(empty($errorMsg)){
                    if(empty($optItem)){
                        array_push($new_client_info, '');
                    }

                    incrementId($idFile);
                    array_push($new_client_info, 'active');

                    //define the columns to be used in the insert statement
                    $columns = ['id','company_name','contact_fname','contact_lname','company_phone','company_cell',
                        'company_email','website', 'status'];

                    if(addTableLine('clients',$columns, $new_client_info)){
                        $message = 'Client added';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    }
                    else{
                        $message = 'Client could not be added';
                    }

                    $view = $current_view_path . 'confirm-update.phtml';
                }
            }

            break;
        }
        case 'update':
        {
            $id = getParam('id');
            $line = getTableLineByColNameAndVal('clients', 'id', $id); //define an array with current values

            if(is_array($line)){
                $view = $current_view_path . 'modify-client.phtml';
                logOperations($_SESSION['username'], getParam('page'), 'view');
            }
            else{
                $message = $line;
                $view = $current_view_path . 'default.phtml';
                break;
            }

            require $validation_params;


            if($check === 'true'){

                $ncIndex = 0;
                require $validation_logic;

                //if all validation checks passed, commence adding to db
                if(empty($errorMsg)){
                    if(empty($optItem)){
                        array_push($new_client_info, '');
                    }

                    //define the columns to be used in the insert statement
                    $columns = ['company_name','contact_fname','contact_lname','company_phone','company_cell',
                        'company_email','website'];


                    if (changeTableLineByCol('clients', 'id', $id, $columns, $new_client_info)){
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                        $message = 'Client updated';

                    }
                    else{
                        $message = 'Client could not be updated';
                    }

                    $view = $current_view_path . 'confirm-update.phtml';


                }
            }

            break;
        }
        case 'archive':
        {
            $view = $current_view_path . 'archive-client.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-archive':
        {
            $id = getParam('id');

            if(setStatus('clients','status', $id, 'id','archived')){
                $message = 'Client archived';
                setStatus('client_notifications','cn_status', $id, 'client_id','archived');
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Client could not be archived';
            }

            $view = $current_view_path . 'confirm-statusChange.phtml';
            break;
        }
        case 'activate':
        {
            $view = $current_view_path . 'activate-client.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-activate':
        {
            $id = getParam('id');
            if(setStatus('clients','status', $id, 'id','active')){
                $message = 'Client activated';
                setStatus('client_notifications','cn_status', $id, 'client_id','active');
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'Client could not be activated';
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
            if ($field != '' && $searchTerm != ''){

                $contents = searchTableByColumn('clients', $field, $searchTerm);

                if(!is_array($contents)){
                    $message = $contents;
                    $view = $config['VIEW_PATH'] . 'default.phtml';
                }
                //success block
                else{
                    $view = $current_view_path . 'view.phtml';
                    logOperations($_SESSION['username'], getParam('page'), getParam('action'));

                    if(empty($contents)){
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

