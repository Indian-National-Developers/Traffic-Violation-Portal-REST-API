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

require_once "common.php";
//require_once "address.php";

/**
 * GET Video Endpoint
 * Returns first 20 Videos in a page, sorted by recency, by default.
 * If any parameters are given, returns videos as per the request.
 *
 * TODO:
 * 1. Also need to retrieve the violations in each video and return
 *    them with the video data.
 *
 */
$app->get('/video/', function () use ($app) {
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
 * POST Video Endpoint
 */
$app->post('/video/', function () use ($app) {
    $config                         =   require 'app/config_dev.php';
    $dbConfig                       =   $config['db'];

    $dbLink                         =   openDB($dbConfig);
    if (!$dbLink) {
        return;
    }

    // retrieve JSON in Request body
    $newVidData                     =   (array)json_decode($app->request()->getBody());
    //$newVidData[':addressID']       =   getAddressID($dbLink, $newVidData);
    
    // adding current date time
    $addedOn                        =   date('Y-m-d H:i:s');
    $newVidData[':addedOn']         =   $addedOn;

    $query                          =   $dbLink->prepare("INSERT INTO Video
        (videoURL, uploaderName, isAnonymous, latitude, longitude, shotOn, addedOn) 
        VALUES (:videoURL, :uploaderName, :isAnonymous, :latitude, :longitude, :shotOn, :addedOn)");

    $result                         =   $query->execute($newVidData);

    if ($result == false) {
        $responseData               =   array("result" => "success", "videoID" => $dbLink->lastInsertId());
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

    $query                          =   "SELECT * FROM video ";
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

    $statement                      =   $dbLink->prepare("SELECT count(*) FROM video");
    $statement->execute();
    $num_rows                       =   $statement->fetchColumn();

    $lastPageIndex                  =   ceil($num_rows / 20.0);
    $pagingJson                     =   array();
    $pagingJson['first']            =   "/video/?page=1";
    if ($page > 1) $pagingJson['prev']                  =   "/video/?page=" . ($page - 1);
    if ($page < $lastPageIndex) $pagingJson['next']     =   "/video/?page=" . ($page + 1);
    $pagingJson['last']             =   "/video/?page=" . $lastPageIndex;

    return                              $pagingJson;

}

?>
