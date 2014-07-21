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
 * The Root of the RESTful Services. This page should list out all the
 * available end points.
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 */

// including all necessary libraries through Composer and Autoload them.
require         'vendor/autoload.php';

// loading configuration data
$config                             =   require 'app/config_dev.php';

// Initializing SLIM with the configuration
$app                                =   new \Slim\Slim($config['slim']);

// Including Application files
require_once    'app/app.php';

$app->run();

?>
