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
 * ADDRESS RESOLVER
 * ****************
 *
 * This file has several functions to resolve the given address
 * as text and to find the ID in Database.
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 * TODO:
 * 1. This code may be prone to SQL Injection attacks. Have to study on this and secure it.
 * 2. Upgrade MySQL code to PDO / MySQLi code
 *
 */

/**
 * relates the given address to existing data in DB and finds the
 * addressID. If the given address doesn't relate, create new
 * entities in DB and returns the addressID.
 *
 * @param   Object                      PDO Object
 * @param   Array                       Array of parameters, including address, city, state, town, pincode.
 *
 * @return  String                      addressID for the given address
 */
function getAddressID($dbLink, $params) {

    $selectedParams                 =   array(':address' => $params[':address'], ':pinCode' => $params[':pinCode']);
    $queryString                    =   "SELECT ID
        FROM address 
        WHERE address LIKE :address AND pinCode = :pinCode";
    $statement                      =   $dbLink->prepare($queryString);
    $statement->execute($selectedParams);
    $results                        =   $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($results) { // If address is found, return its ID
        print_r($results);
        return                          $results[0]["ID"];
    } else { // If not, create the address
        $stateID                    =   getStateID($dbLink, $params);
        $cityID                     =   getCityID($dbLink, $params, $stateID);
        $townID                     =   getTownID($dbLink, $params, $cityID);

        $selectedParams             =   array(':address' => $params[':address'],
            ':pinCode' => $params[':pinCode'],
            ':stateID' => $stateID,
            ':cityID' => $cityID,
            ':townID' => $townID);

        $queryString                =   "INSERT INTO address (address, pinCode, stateID, cityID, townID)
            VALUES (:address, :pinCode, :stateID, :cityID, :townID)";
        $statement                  =   $dbLink->prepare($queryString);
        $statement->execute($selectedParams);
        return                          $dbLink->lastInsertId();
    }

}

/**
 * Gets the ID of the given state
 *
 * @param   Object                      PDO Object
 * @param   Array                       Array of parameters, including address, city, state, town, pincode.
 *
 * @return  String                      stateID for the given state
 */
function getStateID($dbLink, $params) {

    $selectedParams                 =   array(':name' => $params[':state']);
    $queryString                    =   "SELECT ID
        FROM state 
        WHERE name = :name";
    $statement                      =   $dbLink->prepare($queryString);
    $statement->execute($selectedParams);
    $results                        =   $statement->fetchAll(PDO::FETCH_ASSOC);

    return                              $results[0]["ID"];

}

/**
 * Gets the ID of the given city, If City doesn't exist,
 * creates one and return its ID
 *
 * @param   Object                      PDO Object
 * @param   Array                       Array of parameters, including address, city, state, town, pincode.
 * @param   String                      stateID of the given state
 *
 * @return  String                      cityID for the given city
 */
function getCityID($dbLink, $params, $stateID) {

    $selectedParams                 =   array(':name' => $params[':city'], ':stateID' => $stateID);
    $queryString                    =   "SELECT ID
        FROM city 
        WHERE name = :name AND stateID = :stateID";
    $statement                      =   $dbLink->prepare($queryString);
    $statement->execute($selectedParams);
    $results                        =   $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($results) { // If city is found, return its ID
        return                          $results[0]["ID"];
    } else { // If not, create the city
        $queryString                =   "INSERT INTO city (name, stateID)
            VALUES (:name, :stateID)";
        $statement                  =   $dbLink->prepare($queryString);
        $statement->execute($selectedParams);
        return                          $dbLink->lastInsertId();
    }
}

/**
 * Gets the ID of the given town, If Town doesn't exist,
 * creates one and return its ID
 *
 * @param   Object                      PDO Object
 * @param   Array                       Array of parameters, including address, city, state, town, pincode.
 * @param   String                      cityID of the given City
 *
 * @return  String                      townID for the given town
 */
function getTownID($dbLink, $params, $cityID) {

    $selectedParams                 =   array(':name' => $params[':town'], ':cityID' => $cityID);
    $queryString                    =   "SELECT ID
        FROM town 
        WHERE name = :name AND cityID = :cityID";
    $statement                      =   $dbLink->prepare($queryString);
    $statement->execute($selectedParams);
    $results                        =   $statement->fetchAll(PDO::FETCH_ASSOC);

    if ($results) { // If town is found, return its ID
        return                          $results[0]["ID"];
    } else { // If not, create the town
        $queryString                =   "INSERT INTO town (name, cityID)
            VALUES (:name, :cityID)";
        $statement                  =   $dbLink->prepare($queryString);
        $statement->execute($selectedParams);
        return                          $dbLink->lastInsertId();
    }
}

?>
