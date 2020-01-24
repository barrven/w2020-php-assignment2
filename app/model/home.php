<?php
$current_view_path = $config['VIEW_PATH']; //view path for this model
$sourceLink = "<a class='fileSource' href='/folder_view/vs.php?s=" . __FILE__ . "' target='_blank'>View Source</a>";
$view = $current_view_path . 'login.phtml';

switch(getParam('action')) {
    case 'login':
    {
        //make function that queries the db and checks if the username and pw match
        //md5 function hashes password password
        $user = getParam('username');
        $pw = md5(getParam('password'));

        //check that password is not empty first since when db does not find user it returns empty string as pw
        if(getParam('password') != ''){
            if(checkPassword($user, $pw)){
                $_SESSION['authorized'] = true; //add more user info such as username to display in top left
                $_SESSION['username'] = getParam('username');
                $view = $current_view_path . 'default.phtml';
                $message = "Welcome, $user";
            }
        }
        else{
            $_SESSION['authorized'] = false;
            $_SESSION['username'] = '';
        }

        break;
    }
    case 'logout':
    {
        session_unset();
        session_destroy();
        break;
    }

}