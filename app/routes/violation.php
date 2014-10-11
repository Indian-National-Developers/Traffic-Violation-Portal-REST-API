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
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Traffic Violation Portal REST API. If not, see <http://www.gnu.org/licenses/>.
 *
 * DESCRIPTION
 * ***********
 * This file sets up all routes for creating, retriving, updating, deleting
 * Complaints.
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 */

require_once "common.php";

/**
 * GET Violation Endpoint
 * Returns first 1000 Violations in a page, sorted by recency, by default.
 * If any parameters are given, returns the Violations as per the request.
 *
 */
$app->get('/violation/', function () use ($app) {
    $config                         =   require 'app/config_dev.php';
    $dbConfig                       =   $config['db'];

    $dbLink                         =   openDB($dbConfig);
    if (!$dbLink) {
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

    $queryString                    =   createSelectQuery($fromDateFilter, $toDateFilter, $townFilter, $cityFilter, $stateFilter, $userFilter, $page, 20);

    $statement                      =   $dbLink->query($queryString);
    $results                        =   $statement->fetchAll(PDO::FETCH_ASSOC);

    $pagingJson                     =   generatePagingData($dbLink, $page);
    $responseData                   =   array("data" => $results,
                                              "paging" => $pagingJson);

    echo json_encode($responseData);

});

/**
 * POST Violation Endpoint
 */
$app->post('/violation/', function () use ($app) {
    $config                         =   require 'app/config_dev.php';
    $dbConfig                       =   $config['db'];

    $dbLink                         =   openDB($dbConfig);
    if (!$dbLink) {
        return;
    }

    // retrieve JSON in Request body
    $newViolationData               =   (array)json_decode($app->request()->getBody());

    // get Credit ID
    //$newViolationData[':creditID']  =   getCreditID($dbLink, $newVidData);

    // get Fine Amount
    //$newViolationData[':fineAmount']=   getFineAmount($dbLink, $newVidData);

    // adding current date time
    $newViolationData[':addedOn']   =   date('Y-m-d H:i:s');


    $query                          =   $dbLink->prepare("INSERT INTO violation
        (videoID, violationType, vehicleRegNo, vehicleType, timeSlice, analyzerName, addedOn, isAnonymous) 
        VALUES (:videoID, :violationType, :vehicleRegNo, :vehicleType, :timeSlice, :analyzerName, :addedOn, :isAnonymous)");

    $result                         =   $query->execute($newViolationData);

    if ($result) {
        $responseData               =   array("result" => "success", "violationID" => $dbLink->lastInsertId());
    } else {
        $responseData               =   array("result" => "fail", "message" => $dbLink->errorInfo());
    }

    echo json_encode($responseData);

});

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

    $query                          =   "SELECT * FROM violation ";
    if ($fromDateFilter || $toDateFilter || $townFilter || $cityFilter || $stateFilter || $userFilter) {
        $query                      =   $query . 'WHERE ';
        $conditionAdded             =   false;
        if ($fromDateFilter) {
            $query                  =   $query . "shotOn >= '" . $fromDateFilter . "' ";
            $conditionAdded         =   true;
        }
        if ($toDateFilter) {
            if ($conditionAdded)        $query = $query . 'AND ';
            $query                  =   $query . "shotOn <= '" . $toDateFilter . "' ";
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
    $query                          =   $query . "ORDER BY shotOn DESC LIMIT " . $limit . " OFFSET ". $offsetCount;

    return                              $query;
}

/**
 * Generates an array of key-value pairs that depicts the 
 * links to first, last, next and prev pages, whichever exists
 *
 * @param   $page                       current page index
 *
 * @return  Array                       array of key value pairs
 */
function generatePagingData($dbLink, $page) {

    if (!is_numeric($page)) {
        throw new Invalidargumentexception();
    }

    $statement                      =   $dbLink->prepare("SELECT count(*) FROM violation");
    $statement->execute();
    $num_rows                       =   $statement->fetchColumn();

    $lastPageIndex                  =   ceil($num_rows / 1000.0);
    $pagingJson                     =   array();
    $pagingJson['first']            =   "/violation/?page=1";
    if ($page > 1) $pagingJson['prev']                  =   "/violation/?page=" . ($page - 1);
    if ($page < $lastPageIndex) $pagingJson['next']     =   "/violation/?page=" . ($page + 1);
    $pagingJson['last']             =   "/violation/?page=" . $lastPageIndex;

    return                              $pagingJson;

}


?>
