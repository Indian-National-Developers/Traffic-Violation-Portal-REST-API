<?php
/**
 * Traffic Violation Portal REST API
 *
 * LICENSE
 * *******
 * This file is part of Traffic Violation Portal REST API
 *
 * Traffic Violation Portal REST API is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Traffic Violation Portal REST API is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Traffic Violation Portal REST API. If not, see <http://www.gnu.org/licenses/>.
 *
 * DESCRIPTION
 * ***********
 * This file sets up all routes for creating, retriving, updating, deleting
 * videos.
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 * TODO:
 * 1. This code may be prone to SQL Injection attacks. Have to study on this and secure it.
 * 2. Upgrade MySQL code to PDO / MySQLi code
 *
 */

// loading DB Configuration

/**
 * GET Videos; returns all the videos
 */
$app->get('/video/', function () use ($app) {
    $config                         =   require 'app/config_dev.php';
    $dbConfig                       =   $config['db'];

    $dbOpened                       =   openDB($dbConfig);
    if (!$dbOpened) {
        return;
    }

    // retrieve the parameters passed to filter the result
    $paramsArray                    =   $app->request()->params();
    $fromDateFilter                 =   findParameter($paramsArray, 'fromDate');
    $toDateFilter                   =   findParameter($paramsArray, 'toDate');
    $townFilter                     =   findParameter($paramsArray, 'town');
    $cityFilter                     =   findParameter($paramsArray, 'city');
    $stateFilter                    =   findParameter($paramsArray, 'state');
    $userFilter                     =   findParameter($paramsArray, 'uploader');
    $page                           =   findParameter($paramsArray, 'page');

    $query                          =   createSelectQuery($fromDateFilter, $toDateFilter, $townFilter, $cityFilter, $stateFilter, $userFilter, $page, 20);
    $result                         =   mysql_query($query) or die('Could not query');
    $videoJson                      =   array();

    if(mysql_num_rows($result)){
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
            $videoJson[]            =   $row;
        }
    }

    $pagingJson                     =   generatePagingData($page);

    $responseData                   =   array("data" => $videoJson,
                                              "paging" => $pagingJson);

    echo json_encode($responseData);

    mysql_close();
});

/**
 * given current page count, this function find the total number of
 *

/**
 * open a connection to DB server and selects a DB, based on
 * given configuration Dictionary.
 *
 * @param   Dictionary                  ['host', 'userName', 'password', 'dbName'];
 *
 * @return  bool                        success or not
 */
function openDB($dbConfig) {
    $dbLink                         =   mysql_connect($dbConfig['host'],
                                                      $dbConfig['userName'],
                                                      $dbConfig['password']);
    if (!$dbLink) {
        haltExecutionOnError(mysql_error());
        return                          false;
    }

    $success                        =   mysql_select_db($dbConfig['dbName']);
    if (!$success) {
        haltExecutionOnError(mysql_error());
        return                          false;
    }

    return                              $success;
}

/**
 * Forms a SQL query to select `video` records based on given parameters
 * and returns it
 *
 * @param   date        $fromDateFilter         dateTime from when videos should be retrieved. can be set to null
 * @param   date        $toDateFilter           dateTime upto which videos should be retrieved. can be set to null
 * @param   string      $townFilter             name of the town, where the video is taken. can be set to null
 * @param   string      $cityFilter             name of the city, where the video is taken. can be set to null
 * @param   string      $stateFilter            name of the state, where the video is taken. can be set to null
 * @param   string      $userFilter             name of the user who uploaded the Video. can be set to null
 * @param   int         $page                   index of the page to retrieve the videos from
 *
 * @return  string                              string representing SQL query
 */
function createSelectQuery($fromDateFilter, $toDateFilter, $townFilter, $cityFilter, $stateFilter, $userFilter, $page, $limit) {

    $query                          =   "SELECT * FROM video ";
    if ($fromDateFilter || $toDateFilter || $townFilter || $cityFilter || $stateFilter || $userFilter) {
        $query                      =   $query . 'WHERE ';
        $conditionAdded             =   false;
        if ($fromDateFilter) {
            $query                  =   $query . "time >= '" . $fromDateFilter . "' ";
            $conditionAdded         =   true;
        }
        if ($toDateFilter) {
            if ($conditionAdded)        $query = $query . 'AND ';
            $query                  =   $query . "time <= '" . $toDateFilter . "' ";
            $conditionAdded         =   true;
        }
        if ($townFilter) {
            if ($conditionAdded)        $query = $query . 'AND ';
            $query                  =   $query . "town LIKE '%" . $townFilter . "%' ";
            $conditionAdded         =   true;
        }
        if ($cityFilter) {
            if ($conditionAdded)        $query = $query . 'AND ';
            $query                  =   $query . "city LIKE '%" . $cityFilter . "%' ";
            $conditionAdded         =   true;
        }
        if ($userFilter) {
            if ($conditionAdded)        $query = $query . 'AND ';
            $query                  =   $query . "uploadedBy LIKE '%" . $userFilter . "%' ";
            $conditionAdded         =   true;
        }
    }
    $offsetCount                    =   ($page - 1) * 20;
    $query                          =   $query . "ORDER BY time DESC LIMIT " . $limit . " OFFSET ". $offsetCount;

    return                              $query;
}

/**
 * finds the specified parameter in the given parameter array
 * and returns it
 *
 * @param   Array                       array of GET parameters
 * @param   String                      parameter name to search for
 *
 * @return  Object                      int or String or String Array of parameters
 */
function findParameter($params, $paramName) {

    if ($paramName == 'fromDate' || $paramName == 'toDate') {
        return                          findDateParameterForKey($params, $paramName);
    } else if ($paramName == 'town' || $paramName == 'city' || $paramName == 'state' || $paramName == 'uploader') {
        return                          findRegularParameterForKey($params, $paramName);
    } else if ($paramName == 'page') {
        return                          findPageParameter($params);
    }

}

/**
 * find a regular parameter from the parameter array for the given key
 * Returns `null` if not found or is invalid
 * TODO: Throws exception, if invalid
 *
 * @param   Array                       array of GET parameters
 * @param   String                      Any key that holds a textual parameter value
 *
 * @return  String                      datetime the value of the parameter as a string
 */
function findRegularParameterForKey($params, $keyParam) {

    // if null is passed, return null
    if($params == null)
        return                          null;

    // if date param is not passed, return null
    if(!array_key_exists($keyParam, $params))
        return                          null;

    $paramValue                     =   $params[$keyParam];

    // if Param value is not mentioned, return null
    if ($paramValue == null)
        return                          null;

    // if Param value is not string, return null
    if (!is_string($paramValue))
        return                          null;

    return                              $paramValue;
}

/**
 * find the date parameter from the parameter array for the given key
 * Returns `null` if not found or is invalid
 * TODO: Throws exception, if invalid
 *
 * @param   Array                       array of GET parameters
 * @param   String                      either `fromDate` or `toDate`. Any key that holds date value
 *
 * @return  datetime                    datetime string in the format `Y-m-d H-i-s`
 */
function findDateParameterForKey($params, $keyParam) {

    // if null is passed, return null
    if($params == null)
        return                          null;

    // if date param is not passed, return null
    if(!array_key_exists($keyParam, $params))
        return                          null;

    $dateParam                      =   $params[$keyParam];

    // if date Param value is not mentioned, return null
    if ($dateParam == null)
        return                          null;

    // Date validation: http://stackoverflow.com/questions/19271381/correctly-determine-if-date-string-is-a-valid-date-in-that-format
    $d                              =   DateTime::createFromFormat('Y-m-d', $dateParam);
    if ($d && $d->format('Y-m-d') == $dateParam) {
        return $d->format('Y-m-d');
    }

    $d                              =   DateTime::createFromFormat('Y-m-d H:i:s', $dateParam);
    if ($d && $d->format('Y-m-d H:i:s') == $dateParam) {
        return $d->format('Y-m-d H:i:s');
    } else {
        return                          null;
    }
}

/**
 * finds the value of page parameter and returns it.
 * Returns 1, in case of invalid parameter or null
 *
 * @param   $params                     array of GET parameters
 *
 * @return  int                         Page number
 */
function findPageParameter($params) {

    // if null is passed, return first page
    if($params == null)
        return                          1;

    // if page param is not passed, return first page
    if(!array_key_exists('page',$params))
        return                          1;

    $page                           =   $params['page'];

    // if page value is not mentioned, return first page
    if ($page == null)
        return                          1;

    if ( !is_numeric($page) )
        return                          1;

    return                              (int)$page;
}

/**
 * Generates an array of key-value pairs that depicts the 
 * links to first, last, next and prev pages, whichever exists
 *
 * @param   $page                       current page index
 *
 * @return  Array                       array of key value pairs
 */
function generatePagingData($page) {

    if (!is_numeric($page)) {
        throw new Invalidargumentexception();
    }

    $totalCountResult               =   mysql_query('SELECT COUNT(1) FROM video');
    $num_rows                       =   mysql_result($totalCountResult, 0, 0);

    $lastPageIndex                  =   ceil($num_rows / 20.0);
    $pagingJson                     =   array();
    $pagingJson['first']            =   "/video/?page=1";
    if ($page > 1) $pagingJson['prev']                  =   "/video/?page=" . ($page - 1);
    if ($page < $lastPageIndex) $pagingJson['next']     =   "/video/?page=" . ($page + 1);
    $pagingJson['last']             =   "/video/?page=" . $lastPageIndex;

    return                              $pagingJson;

}

function haltExecutionOnError($errorMessage) {
    /*
    mysql_close();
    $errResponse                    =   array('status' => 'error', 'description' => $errorMessage);
    $app->halt(500, json_encode($errResponse));
     */
}

?>
