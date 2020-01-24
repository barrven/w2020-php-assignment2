<?php

$sourceLink = "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";

if(@!$_SESSION['authorized']){
    header('location: ?page=home');
}
else {

    $current_view_path = $config['VIEW_PATH'] . 'user' . DS; //view path for this model
    $idFile = $config['DATA_PATH'] . 'user-id.txt'; //defines the path to the notification id file
    $subMenu = $current_view_path . 'sub-menu.phtml';

    //action param changes the view
    switch (getParam('action')) {
        case 'menu':
        {
            $view = $config['VIEW_PATH'] . 'default.phtml';
            $message = 'Welcome to the User Management Page';
            break;
        }
        case 'view':
        {
            $view = $current_view_path . 'view.phtml';
            $columns = ['id', 'fname', 'lname', 'email', 'phone','position','username','status'];
            $contents = returnTableColumns('users', $columns);
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
            $view = $current_view_path . 'add-user.phtml';
            $submitCheck = getParam('submitCheck');
            $formats = ['name', 'name', 'email', 'phone', '',]; //required format types to be passed to correctFormat function
            $new_user_info = [@$_POST['fname'], @$_POST['lname'],@$_POST['email'],@$_POST['phone'],@$_POST['username']];

            //run validation checks only if form has been submitted
            if($submitCheck == 'true'){
                //generates array of error messages by comparing submitted info and expected formats
                //if no error is found, adds empty string to the given position on the error msg array. first item is boolean check
                $errorMsg = validateInput($new_user_info, $formats);

                //separate check for password
                $pw = @$_POST['password'];
                $pw2 = @$_POST['passConfirm'];
                if(!empty($pw)){
                    if($pw == $pw2){
                        array_push($new_user_info, $pw);
                    }
                    else{
                        array_push($errorMsg, 'Passwords do not match');
                    }
                }
                else{
                    array_push($errorMsg, 'Password is required');
                }

                //check that username is not used
                if(!isUniqueUser($new_user_info[4])){
                    $errorMsg[4] = 'Username is already in use';
                }

                //section runs when no errors were detected
                if(blankErrors($errorMsg)){
                    incrementId($idFile);
                    array_unshift($new_user_info, getNextId($idFile));
                    array_push($new_user_info, @$_POST['position'],'active');

                    //define the columns to be used in the insert statement
                    $columns = ['id','fname','lname','email','phone','username','password','position', 'status'];

                    if(addTableLine('users',$columns, $new_user_info)){
                        $message = 'User added';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    }
                    else{
                        $message = 'User could not be added';
                    }
                    $view = $current_view_path . 'confirm-add.phtml';
                }
            }
            break;
        }

        case 'update':
        {
            $id = getParam('id');
            $currInfo = getTableLineByColNameAndVal('users', 'id', $id);
            $submitCheck = getParam('submitCheck');
            $formats = ['name', 'name', 'email', 'phone', '',]; //required format types to be passed to correctFormat function
            $new_user_info = [@$_POST['fname'], @$_POST['lname'],@$_POST['email'],@$_POST['phone'],@$_POST['username']];

            if(is_array($currInfo)){
                $view = $current_view_path . 'modify-user.phtml';
            }
            else{
                //getTableLineByColNameAndVal() function returns an error message (string) if the query failed
                $message = $currInfo;
                $view = $current_view_path . 'default.phtml';
                break;
            }
            if($submitCheck == 'true'){
                //generates array of error messages by comparing submitted info and expected formats
                $errorMsg = validateInput($new_user_info, $formats);
                //separate check for password
                $pw = @$_POST['password'];
                $pw2 = @$_POST['passConfirm'];
                if(!empty($pw)){
                    if($pw == $pw2){
                        array_push($new_user_info, $pw);
                    }
                    else{
                        array_push($errorMsg, 'Passwords do not match');
                    }
                }
                else{
                    array_push($errorMsg, 'Password is required');
                }

                //check that username is not used
                if(!isUniqueUser($new_user_info[4]) && $new_user_info[4] != $currInfo[5]){
                    $errorMsg[4] = 'Username is already in use';
                }

                //section runs when no errors were detected
                if(blankErrors($errorMsg)){
                    array_unshift($new_user_info, $currInfo[0]);
                    array_push($new_user_info, @$_POST['position'],$currInfo[8]);

                    //define the columns to be used in the insert statement
                    $columns = ['id','fname','lname','email','phone','username','password','position', 'status'];
                    if(changeTableLineByCol('users', 'id',$id, $columns, $new_user_info)){
                        $message = 'User updated';
                        logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                    }
                    else{
                        $message = 'User could not be updated';
                    }


                    $view = $current_view_path . 'confirm-add.phtml';
                }
            }

            break;
        }
        case 'archive':
        {
            $message = 'Are you sure you would like to archive this user?';
            $view = $current_view_path . 'archive-user.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-archive':
        {
            $id = getParam('id');

            if(setStatus('users', 'status',$id,'id','archived')){
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
                $message = 'User archived';
            }
            else{
                $message = 'User could not be archived';
            }
            $view = $current_view_path . 'confirm-archive.phtml';
            break;
        }
        case 'activate':
        {
            $message = 'Are you sure you would like to activate this user?';
            $view = $current_view_path . 'activate-user.phtml';
            $id = getParam('id');
            break;
        }
        case 'commit-activate':
        {
            $id = getParam('id');

            if(setStatus('users', 'status',$id,'id','active')){
                $message = 'User activated';
                logOperations($_SESSION['username'], getParam('page'), getParam('action'));
            }
            else{
                $message = 'User could not be activated';
            }

            $view = $current_view_path . 'confirm-archive.phtml';
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
            if ($field != '' && $searchTerm != ''){ //show view table if search params were not blank

                $temp = searchTableByColumn('users', $field, $searchTerm); //$temp will be a string with error msg if search fails

                if(!is_array($temp)){ //return error message if db connection failed
                    $message = $temp;
                    $view = $config['VIEW_PATH'] . 'default.phtml';
                }
                //success block
                else{
                    $contents = removeUnwantedColumn($temp, 6);
                    $view = $current_view_path . 'search-view.phtml';
                    logOperations($_SESSION['username'], getParam('page'), getParam('action'));

                    if(empty($contents)){
                        $message = 'No matching entries were found';
                        $view = $config['VIEW_PATH'] . 'default.phtml';
                    }
                }

            }
            else{
                $message = 'No search terms were entered'; //display message if no search terms were entered
                $view = $config['VIEW_PATH'] . 'default.phtml';
            }
            break;
        }
        case 'backup':
        {
            exec("mysqldump -uf9189284_user --password=trombone22 f9189284_test > dbBackup_ABC.sql");
            $message = 'Click to download backup file';
            $view = $current_view_path . 'backup.phtml';

            break;
        }
        case 'restore':
        {
            $message = 'Upload sql backup file to restore the database';
            $view = $current_view_path . 'restore.phtml';

            if(getParam('submitCheck') == 'true'){

                if($_FILES['bkFile']['size'] !=0){
                    exec("mysql -uf9189284_user --password=trombone22 f9189284_test <" . $_FILES['bkFile']['tmp_name']);
                    $message = 'Database restore completed';
                    $view = $config['VIEW_PATH'] . 'default.phtml';
                }

            }

            break;
        }
    }
}