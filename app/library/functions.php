<?php

function getParam($name, $default = '')
{
    if (isset($_REQUEST[$name])) {
        return $_REQUEST[$name];
    } else {
        return $default;
    }
}

//########################## Validation functions ######################################################################
//this checks that input is in correct format
function correctFormat($item, $format = '')
{
    switch ($format) {
        case 'email':
            if (filter_var($item, FILTER_VALIDATE_EMAIL)) {
                return true;
            }
            return false;

        case 'int':
            if (filter_var($item, FILTER_VALIDATE_INT)) {
                return true;
            }
            return false;
        case 'name':
            if (preg_match('/^[a-zA-Z ]*$/', $item)) {
                return true;
            }
            return false;
        case 'phone':
            if (phoneFormat($item)) {
                return true;
            }
            return false;
        case 'url':
            if (filter_var($item, FILTER_VALIDATE_URL)) {
                return true;
            }
            return false;
        case '' :
            return true;
    }
}

//this cleans up user input
function format_input($input)
{
    $input = trim($input);
    $input = stripslashes($input);
    $input = htmlspecialchars($input);
    return $input;
}

//check that phone number has 10 digits, return boolean or 10 digit int
function phoneFormat($input, $mode = 'bool')
{
    $arr = str_split($input);
    $outArr = [];
    $i = 0;
    foreach ($arr as $item) {
        if (is_numeric($item)) {
            $outArr[$i++] = $item;
        }
    }

    if (sizeof($outArr) == 10 && $mode = 'bool') {
        return true;
    }
    if (sizeof($outArr) == 10 && $mode = 'int') {
        return (int)implode($outArr);
    }

    return false;
}


function createEmptyItemNote($array){
    $messages = [];
    for ($i = 0; $i < sizeof($array); $i++){
        if ($array[$i] == ''){
            $messages[$i] = 'This item cannot be empty';
        }
        else{
            $messages[$i] = '&nbsp;';
        }

    }
    return $messages;
}

function checkMgsArray($array){
    foreach($array as $item){
        if($item != '&nbsp;'){
            return false;
        }
    }
    return true;
}

//takes two parallel arrays: items and formats, and checks that each item is is not empty and valid format
//generates output array of error messages. if no errors are present each position on array is empty string
//first item on errorMsg array indicates whether errors are present
function validateInput($items, $formats){

    $errorMsg = [];
    for ($i = 0; $i<sizeof($items); $i++) {
        $item = format_input($items[$i]);
        if (!empty($item)) {
            if (!correctFormat($item, $formats[$i])) {
                $errorMsg[$i] = 'This item is incorrectly formatted';
            }
            else{
                $errorMsg[$i] = '';
            }
        }
        else {
            $errorMsg[$i] = 'This item is required';
        }
    }
    return $errorMsg;
}

//checks that an incoming array only contains blank strings
function blankErrors($errorMsg){
    foreach ($errorMsg as $msg){
        if($msg != ''){return false;}
    }
    return true;
}



//######################################################################################################################

function incrementId($file){
    $nextId = file($file);
    $id = $nextId[0];
    $newId = $id + 1;
    file_put_contents($file, $newId);
}

function getNextId($file){
    return file($file)[0];
}


//######################################################### DB FUNCTIONS ###############################################

function runQuery($query){
    try {
        $conn = new PDO(DB_CON, USER, PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $conn = null; //clean up
        return true;

    }
    catch(Exception $e)
    {
        $conn = null;
        return false;
    }
}

//returns an associative array of the entire contents of a table
function returnTable($table){
    try{

        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = 'SELECT * FROM ' . $table . ';'; //create query
        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_ASSOC); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        return $contents;

    }
    catch (Exception $ex){
        $ex = 'Database connection error';
        return $ex;

    }
}

function returnTableColumns($table, $columns){
    try{

        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = 'SELECT ';

        //loop through columns to prepare update statement
        for ($i = 0; $i < sizeof($columns); $i++){
            if($i == (sizeof($columns)-1)){
                $query .= "`$columns[$i]` ";
            }
            else{
                $query .= "`$columns[$i]`, ";
            }
        }
        $query .= "FROM `$table`;";



        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_ASSOC); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        return $contents;

    }
    catch (Exception $ex){
        $ex = 'Database connection error';
        return $ex;

    }
}

//returns line as array
function getTableLineByColNameAndVal($table, $colName, $val){
    try{

        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "select * from $table where $colName = $val;"; //create query
        $result = $db_con->query($query); //send query
        $contents = $result->fetch(PDO::FETCH_NUM); //store query result as associative array
        //clean up
        $result->closeCursor();
        $db_con = null;
        return $contents;
    }
    catch (Exception $ex){
        $ex = 'Database connection error';
        //clean up
        $db_con = null;
        return $ex;

    }
}


//take an array of column names, and an array of column values. Update row using given column index and value
//returns boolean to check success
function changeTableLineByCol($table, $col, $id, $columns, $newValues){

        $sql = "UPDATE `$table` SET";

        //loop through columns to prepare update statement
        for ($i = 0; $i < sizeof($columns); $i++){
            if($i == (sizeof($columns)-1)){
                $sql .= "`$columns[$i]` = '$newValues[$i]' ";
            }
            else{
                $sql .= "`$columns[$i]` = '$newValues[$i]', ";
            }
        }
        $sql .= "WHERE `$table`.`$col` = $id;";

    return runQuery($sql);

}

//add entry to table using a specified list of columns and an array of values
//returns boolean to check success
function addTableLine($table, $columns, $values){

        $sql = "INSERT INTO `$table` (";

        //loop through $columns and add to query
        for ($i = 0; $i < sizeof($columns); $i++){
            if($i == (sizeof($columns)-1)){
                $sql .= "`$columns[$i]` ) ";
            }
            else{
                $sql .= "`$columns[$i]`, ";
            }
        }
        $sql .= "VALUES ( ";

        //loop through $values and add to query
        for ($i = 0; $i < sizeof($values); $i++){
            if($i == (sizeof($values)-1)){
                $sql .= "'$values[$i]' ) ";
            }
            else{
                $sql .= "'$values[$i]', ";
            }
        }

    return runQuery($sql);
}

function setStatus($table, $columnName, $id, $idCol, $status){
    $sql = "UPDATE `$table` SET `$columnName`= '$status' WHERE $idCol = $id";
    return runQuery($sql);
}

//check value in particular column in unique row to determine if it matches a given value
function rowValMatches($table, $columnName, $id, $idCol, $matchVal){
    try{

        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "SELECT $columnName FROM $table WHERE $idCol = '$id'"; //create query
        $result = $db_con->query($query); //send query
        $contents = $result->fetch(PDO::FETCH_COLUMN); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        if($contents == $matchVal){
            return true;
        }
        return false;

    }
    catch (Exception $ex){
        return false;

    }
}

//returns 2d array based on search term and search column
function searchTableByColumn($table, $column, $searchTerm){

    $tableLines = returnTable($table);
    $outputArr = [];
    $j = 0;

    if(!is_array($tableLines)){
        return $tableLines;
    }
    else{
        foreach ($tableLines as $line){
            if(strtolower(trim($line[$column])) != strtolower(trim($searchTerm))){
                continue;
            }
            $outputArr[$j++] = $line;
        }
    }

    return $outputArr;
}

//checks that incoming db5 hashed password matches with the given username in the database
function checkPassword($username, $password){
    try{
        $db_con = new PDO(DB_CON, USER, PASSWORD);
        $query = "SELECT password from users WHERE username = '$username';";
        $result = $db_con->query($query);
        $contents = $result->fetch(PDO::FETCH_COLUMN);
        $result->closeCursor();
        $db_con = null;
        if($password == md5($contents)){
            //check that the user is not suspended
            if(rowValMatches('users', 'status',$username,'username','archived')){
                return false;
            }
            //password matches and user is not suspended (archived)
            return true;
        }
        return false;
    }
    catch (Exception $e){
        return false;
    }
}

//returns a particular table column as a 1d array
function getTableColumn($table, $col){
    try{

        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "SELECT `$col` FROM `$table`;"; //create query
        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_COLUMN); //store query result as associative array
        $result->closeCursor();
        $db_con = null;

        return $contents;
    }

    catch (Exception $ex){
        $ex = 'Database connection error';
        $db_con = null;
        return $ex;
    }
}

//selects distinct specified columns from two specified tables based on match between two specified columns
function joinTablesByDistinctVal($table1, $table2, $col1, $col2, $colList){
    try{
        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "SELECT DISTINCT ";
        for ($i = 0; $i < sizeof($colList); $i++){
            if($i == sizeof($colList)-1){
                $query .= "$colList[$i] ";
            }
            else{
                $query .= "$colList[$i], ";
            }
        }

        $query .= "FROM $table1 JOIN $table2 ON $col1 = $col2;";

        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_ASSOC); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        return $contents;

    }
    catch (Exception $ex){
    $ex = 'Database connection error';
    return $ex;
    }
}

//sends a list of the desired columns, and writes a select statement that returns those columns
function selectTableCols($table, ...$column){
    try{
        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "SELECT ";
        for ($i = 0; $i < sizeof($column); $i++){
            if($i == sizeof($column)-1){
                $query .= "$column[$i] ";
            }
            else{
                $query .= "$column[$i], ";
            }
        }
        $query .= "FROM $table;";
        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_ASSOC); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        return $contents;

    }
    catch (Exception $ex){
        $ex = 'Database connection error';
        return $ex;
    }
}

//joins two tables using a subquery. used when displaying only those clients that have notifications
function joinTablesOnSubquery($table1, $table2, $col1, $col2, $colList, $subQCol, $subCon){
    try{
        $db_con = new PDO(DB_CON, USER, PASSWORD); //create database connection PDO object
        $query = "SELECT ";
        for ($i = 0; $i < sizeof($colList); $i++){
            if($i == sizeof($colList)-1){
                $query .= "$colList[$i] ";
            }
            else{
                $query .= "$colList[$i], ";
            }
        }

        $query .= "FROM $table1 JOIN $table2 ON $col1 = $col2";
        $query .=" WHERE $subQCol IN (SELECT $subQCol FROM $table1 WHERE $subQCol = $subCon)";

        //echo "<hr>$query<hr>";
        $result = $db_con->query($query); //send query
        $contents = $result->fetchAll(PDO::FETCH_ASSOC); //store query result as associative array
        $result->closeCursor(); //clean up
        $db_con = null;
        return $contents;

    }
    catch (Exception $ex){
        $ex = 'Database connection error';
        return $ex;
    }
}

//used in creating new user to make sure that that username is not used
function isUniqueUser($username){
    $users = getTableColumn('users','username');
    foreach ($users as $user){
        if ($user == $username)
            return false;
    }
    return true;
}


//takes 2d array and returns specific cols
function removeUnwantedColumn($inputArray, $remove){
    $out = [];
    foreach ($inputArray as $line){
        $temp = [];
        $count = 0;
        foreach ($line as $item){
            if($count != $remove){
                array_push($temp,$item);
            }
            $count++;
        }
        array_push($out,$temp);
    }
    return $out;
}

//this function is called every time an action in any model is successfully done
// (i.e. view, modify, delete, search, add). also tracks the username, and the module in which the action took place
function logOperations($user, $module, $action){
    //use these to get the current time and ip
    date_default_timezone_set('EST');
    $date = date("Y-m-d");
    $time = date("H:i:s");
    $ip = $_SERVER['REMOTE_ADDR'];

    $values = [$date, $time, $user, $module, $action, $ip];
    $columns = ['date','time','username', 'module','action','ip'];
    addTableLine('operations', $columns, $values);
}

//used when displaying multiline content in notifications
function putCommaAndEol($input){
    $illegalChars = ['%%', '##'];
    $replacement = [',',PHP_EOL];
    return str_replace($illegalChars,$replacement, $input);
}
