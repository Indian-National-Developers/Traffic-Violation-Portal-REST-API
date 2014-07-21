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
 * videos.
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 */

// loading DB Configuration

/**
 * GET Videos; returns all the videos
 */
$app->get('/video/', function () use ($app) {
    $config                         =   require 'app/config_dev.php';
    $dbConfig                       =   $config['db'];

    $db                             =   mysql_connect($dbConfig['host'],
                                                      $dbConfig['userName'],
                                                      $dbConfig['password']);
    mysql_select_db($dbConfig['dbName'], $db) or die('could not select db');
    $page                           =   $app->request()->params('page');
    if ($page == null)                  $page = 1;
    $page                           =   (int)$page;

    $offsetCount                    =   ($page - 1) * 20;
    $query                          =   "SELECT * FROM video ORDER BY time DESC LIMIT 20 OFFSET ". $offsetCount;
    $result                         =   mysql_query($query) or die('Could not query');
    $videoJson                      =   array();

    if(mysql_num_rows($result)){
        while($row = mysql_fetch_array($result, MYSQL_ASSOC)){
            $videoJson[]            =   $row;
        }
    }

    $pagingJson                     =   array();
    $pagingJson['first']            =   "/video/?page=1";
    if ($page > 1) $pagingJson['prev']= "/video/?page=" . ($page - 1);
    if ($page < 4) $pagingJson['next']= "/video/?page=" . ($page + 1);
    $pagingJson['last']             =   "/video/?page=4";

    $responseData                   =   array("data" => $videoJson,
                                              "paging" => $pagingJson);

    echo json_encode($responseData);

    mysql_close($db);
});

?>
