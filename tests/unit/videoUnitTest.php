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
 * This class has unit test for all functions in `app/routes/video.php`
 * It tests for both positive and negative test cases
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 * Test Data Specs:
 *   * 4 Pages
 *   * xx videos
 *   * yy complaints
 *   * [list here]
 *
 * TODO: Test cases for `openDB()` and `createSelectQuery()`
 *
 */


class videoUnitTest extends Slim_Framework_TestCase {

    /**
     * Negative Unit Test Cases for generatePagingData
     * @expectedException Invalidargumentexception
     */
    public function test_generatePagingData_negative() {

        // setup DB
        $config                     =   require 'app/config_dev.php';
        openDB($config['db']);

        // check with a character
        $result                     =   generatePagingData('1');

        // check with non number
        $result                     =   generatePagingData(array());

    }

    /**
     * Positive Unit Test Cases for generatePagingData
     */
    public function test_generatePagingData_positive() {

        // setup DB
        $config                     =   require 'app/config_dev.php';
        openDB($config['db']);

        // check with the First Page
        $result                     =   generatePagingData(1);
        $this->assertSame(3, count($result));
        $this->assertSame('/video/?page=1', $result['first']);
        $this->assertSame('/video/?page=4', $result['last']);
        $this->assertSame('/video/?page=2', $result['next']);

        // check with the last Page
        $result                     =   generatePagingData(4);
        $this->assertSame(3, count($result));
        $this->assertSame('/video/?page=1', $result['first']);
        $this->assertSame('/video/?page=3', $result['prev']);
        $this->assertSame('/video/?page=4', $result['last']);

        // check with the 2nd Page
        $result                     =   generatePagingData(2);
        $this->assertSame(4, count($result));
        $this->assertSame('/video/?page=1', $result['first']);
        $this->assertSame('/video/?page=1', $result['prev']);
        $this->assertSame('/video/?page=3', $result['next']);
        $this->assertSame('/video/?page=4', $result['last']);

        // check with a random page number, when test data set grows

    }

    /**
     * Negative Unit Test Cases for createSelectQuery
     */
    public function test_createSelectQuery_negative() {

    }

    /**
     * Positive Unit Test Cases for createSelectQuery
     */
    public function test_createSelectQuery_Positive() {

        // all null parameters
        $result                     =   createSelectQuery(null, null, null, null, null, null, 1, 20);
        $this->assertSame('SELECT * FROM video ORDER BY time DESC LIMIT 20 OFFSET 0', $result);

        // videos from 3rd page
        $result                     =   createSelectQuery(null, null, null, null, null, null, 3, 20);
        $this->assertSame('SELECT * FROM video ORDER BY time DESC LIMIT 20 OFFSET 40', $result);

        // all videos from Velachery
        $result                     =   createSelectQuery(null, null, 'Velachery', null, null, null, 1, 20);
        $this->assertSame("SELECT * FROM video WHERE town LIKE '%Velachery%' ORDER BY time DESC LIMIT 20 OFFSET 0", $result);

        // all videos from Jan 1, 2014
        $result                     =   createSelectQuery('2014-01-01 00:00:00', null, null, null, null, null, 1, 20);
        $this->assertSame("SELECT * FROM video WHERE time >= '2014-01-01 00:00:00' ORDER BY time DESC LIMIT 20 OFFSET 0", $result);

        // all videos between July 1, 2013 and December 1, 2013
        $result                     =   createSelectQuery('2013-07-01 00:00:00', '2013-12-01 00:00:00', null, null, null, null, 1, 20);
        $this->assertSame("SELECT * FROM video WHERE time >= '2013-07-01 00:00:00' AND time <= '2013-12-01 00:00:00' ORDER BY time DESC LIMIT 20 OFFSET 0", $result);

        // all parameters except state (since it doesn't exist in current DB structure)
        $result                     =   createSelectQuery('2013-07-01 00:00:00', '2013-12-01 00:00:00', 'Velachery', 'Chennai', 'Tamil Nadu', 'saiy2k', 2, 20);
        $this->assertSame("SELECT * FROM video WHERE time >= '2013-07-01 00:00:00' AND time <= '2013-12-01 00:00:00' AND town LIKE '%Velachery%' AND city LIKE '%Chennai%' AND uploadedBy LIKE '%saiy2k%' ORDER BY time DESC LIMIT 20 OFFSET 20", $result);

    }



    /**
     * Negative Unit Test Cases for findParameter
     */
    public function test_findParameter_negative() {

        // with null parameter
        $result                     =   findParameter(null, 'town');
        $this->assertSame(null, $result);

        // with empty array
        $result                     =   findParameter(array(), 'town');
        $this->assertSame(null, $result);

        // without proper key
        $result                     =   findParameter(array('randomKey' => 'randomValue'), 'town');
        $this->assertSame(null, $result);

        // with empty object
        $result                     =   findParameter(new stdClass(), 'state');
        $this->assertSame(null, $result);

        // with non string as value
        $result                     =   findParameter(array('city' => 10), 'city');
        $this->assertSame(null, $result);

        // with object as value
        $result                     =   findParameter(array('city' => new stdClass()), 'city');
        $this->assertSame(null, $result);

        // with array as value
        $result                     =   findParameter(array('state' => array()), 'state');
        $this->assertSame(null, $result);

        // with wrong date key
        $result                     =   findParameter(array('city' => 'chennai'), 'state');
        $this->assertSame(null, $result); 

        // with a non date string as value
        $result                     =   findParameter(array('fromDate' => 'this is not a date'), 'fromDate');
        $this->assertSame(null, $result);

        // with a invalid date
        $result                     =   findParameter(array('fromDate' => '2015-30-30 99:12:99'), 'fromDate');
        $this->assertSame(null, $result);

        // with just time and no date
        $result                     =   findParameter(array('fromDate' => '10:12:20'), 'fromDate');
        $this->assertSame(null, $result);

        // with wrong date key
        $result                     =   findParameter(array('fromDate' => '10:12:20'), 'toDate');
        $this->assertSame(null, $result);

        // Page parameter as string
        $result                     =   findParameter(array("page" => 'bulb ah'), 'page');
        $this->assertSame(1, $result);

        // Page parameter as array
        $result                     =   findParameter(array("page" => array()), 'page');
        $this->assertSame(1, $result);

        // page parameter as empty object
        $result                     =   findParameter(new stdClass(), 'page');
        $this->assertSame(1, $result);

        // proper parameter array with missing key
        $paramArray                 =   array('page' => 3, 'town' => 'saidapet', 'city' => 'chennai');
        $result                     =   findParameter($paramArray, 'fromDate');
        $this->assertSame(null, $result);

    }

    /**
     * Positive Unit Test Cases for findParameter
     */
    public function test_findParameter_Positive() {

        // regular city parameter
        $result                     =   findParameter(array('city' => 'chennai'), 'city');
        $this->assertSame('chennai', $result);

        // regular city parameter with multi values
        $result                     =   findParameter(array('state' => 'tamilnadu,delhi'), 'state');
        $this->assertSame('tamilnadu,delhi', $result);

        // regular date time in format YYYY-mm-dd hh:mm:ss
        $result                     =   findParameter(array('fromDate' => '2014-05-01 10:30:24'), 'fromDate');
        $this->assertSame('2014-05-01 10:30:24', $result);

        // regular date time in format YYYY-mm-dd hh:mm:ss, where hh > 12
        $result                     =   findParameter(array('fromDate' => '2010-05-01 23:30:24'), 'fromDate');
        $this->assertSame('2010-05-01 23:30:24', $result);

        // with just date, not time
        $result                     =   findParameter(array('fromDate' => '2014-05-05'), 'fromDate');
        $this->assertSame('2014-05-05', $result);

        // with different key date
        $result                     =   findParameter(array('toDate' => '2014-05-05'), 'toDate');
        $this->assertSame('2014-05-05', $result);

        // with Page 2
        $result                     =   findPageParameter(array('page' => 2));
        $this->assertSame(2, $result);

        // with random page
        $randomNumber               =   rand();
        $result                     =   findPageParameter(array('page' => $randomNumber));
        $this->assertSame($randomNumber, $result);

        // proper parameter array
        $paramArray                 =   array('page' => 3, 'town' => 'saidapet', 'city' => 'chennai', 'fromDate' => '2014-01-01 00:00:00');
        $result                     =   findParameter($paramArray, 'page');
        $this->assertSame(3, $result);
        $result                     =   findParameter($paramArray, 'town');
        $this->assertSame('saidapet', $result);
        $result                     =   findParameter($paramArray, 'fromDate');
        $this->assertSame('2014-01-01 00:00:00', $result);

    }


    /**
     * Negative Unit Test Cases for findRegularParameterForKey
     */
    public function test_findRegularParameterForKey_negative() {

        // with null parameter
        $result                     =   findRegularParameterForKey(null, 'town');
        $this->assertSame(null, $result);

        // with empty array
        $result                     =   findRegularParameterForKey(array(), 'town');
        $this->assertSame(null, $result);

        // without proper key
        $result                     =   findRegularParameterForKey(array('randomKey' => 'randomValue'), 'town');
        $this->assertSame(null, $result);

        // with empty object
        $result                     =   findRegularParameterForKey(new stdClass(), 'state');
        $this->assertSame(null, $result);

        // with non string as value
        $result                     =   findRegularParameterForKey(array('city' => 10), 'city');
        $this->assertSame(null, $result);

        // with object as value
        $result                     =   findRegularParameterForKey(array('city' => new stdClass()), 'city');
        $this->assertSame(null, $result);

        // with array as value
        $result                     =   findRegularParameterForKey(array('state' => array()), 'state');
        $this->assertSame(null, $result);

        // with wrong date key
        $result                     =   findRegularParameterForKey(array('city' => 'chennai'), 'state');
        $this->assertSame(null, $result); 
    }

    /**
     * Positive Unit Test Cases for findRegularParameterForKey
     */
    public function test_FindRegularParameterForKey_Positive() {

        // regular city parameter
        $result                     =   findRegularParameterForKey(array('city' => 'chennai'), 'city');
        $this->assertSame('chennai', $result);

        // regular city parameter with multi values
        $result                     =   findRegularParameterForKey(array('state' => 'tamilnadu,delhi'), 'state');
        $this->assertSame('tamilnadu,delhi', $result);

    }


    /**
     * Negative Unit Test Cases for finddateparameterforkey
     */
    public function test_findDateParameterForKey_negative() {

        // with null parameter
        $result                     =   findDateParameterForKey(null, 'fromDate');
        $this->assertSame(null, $result);

        // with empty array
        $result                     =   findDateParameterForKey(array(), 'fromDate');
        $this->assertSame(null, $result);

        // without proper key
        $result                     =   findDateParameterForKey(array('randomKey' => 'randomValue'), 'fromDate');
        $this->assertSame(null, $result);

        // with empty object
        $result                     =   findDateParameterForKey(new stdClass(), 'fromDate');
        $this->assertSame(null, $result);

        // with a non date string as value
        $result                     =   findDateParameterForKey(array('fromDate' => 'this is not a date'), 'fromDate');
        //fwrite(STDERR, print_r($result, TRUE));
        $this->assertSame(null, $result);

        // with a invalid date
        $result                     =   findDateParameterForKey(array('fromDate' => '2015-30-30 99:12:99'), 'fromDate');
        $this->assertSame(null, $result);

        // with array as value
        $result                     =   findDateParameterForKey(array('fromDate' => array()), 'fromDate');
        $this->assertSame(null, $result);

        // with just time and no date
        $result                     =   findDateParameterForKey(array('fromDate' => '10:12:20'), 'fromDate');
        $this->assertSame(null, $result);

        // with wrong date key
        $result                     =   findDateParameterForKey(array('fromDate' => '10:12:20'), 'toDate');
        $this->assertSame(null, $result);

    }

    /**
     * Positive Unit Test Cases for findDateParameterForKey
     */
    public function test_FindDateParameterForKey_Positive() {

        // regular date time in format YYYY-mm-dd hh:mm:ss
        $result                     =   findDateParameterForKey(array('fromDate' => '2014-05-01 10:30:24', 'fromDate'), 'fromDate');
        $this->assertSame('2014-05-01 10:30:24', $result);

        // regular date time in format YYYY-mm-dd hh:mm:ss, where hh > 12
        $result                     =   findDateParameterForKey(array('fromDate' => '2010-05-01 23:30:24'), 'fromDate');
        $this->assertSame('2010-05-01 23:30:24', $result);

        // with just date, not time
        $result                     =   findDateParameterForKey(array('fromDate' => '2014-05-05'), 'fromDate');
        $this->assertSame('2014-05-05', $result);

        // with different key date
        $result                     =   findDateParameterForKey(array('toDate' => '2014-05-05'), 'toDate');
        $this->assertSame('2014-05-05', $result);

    }

    /**
     * Negative Unit Test Cases for findPageParameter
     */
    public function test_findPageParameter_withNullParameter() {
        $result                     =   findPageParameter(null);
        $this->assertSame(1, $result);
    }

    public function test_findPageParameter_withEmptyArray() {
        $result                     =   findPageParameter(array());
        $this->assertSame(1, $result);
    }

    public function test_findPageParameter_withArrayWithoutPageKey() {
        $result                     =   findPageParameter(array("randomKey" => "randomValue"));
        $this->assertSame(1, $result);
    }

    public function test_findPageParameter_withStringAsPageValue() {
        $result                     =   findPageParameter(array("page" => 'bulb ah'));
        $this->assertSame(1, $result);
    }

    public function test_findPageParameter_withArrayAsPageValue() {
        $result                     =   findPageParameter(array("page" => array()));
        $this->assertSame(1, $result);
    }

    public function test_findPageParameter_withEmptyObject() {
        $result                     =   findPageParameter(new stdClass());
        $this->assertSame(1, $result);
    }
    // End of Negative Unit Test Cases for findPageParameter

    /**
     * Positive Unit Test Cases for findPageParameter
     */
    public function test_FindPageParameter_WithPage2() {
        $result                     =   findPageParameter(array('page' => 2));
        $this->assertSame(2, $result);
    }

    public function test_FindPageParameter_WithRandomPage() {
        $randomNumber               =   rand();
        $result                     =   findPageParameter(array('page' => $randomNumber));
        $this->assertSame($randomNumber, $result);
        //fwrite(STDERR, print_r($result, TRUE));
    }
    // End of Positive Unit Test Cases for findPageParameter

}

/* End of file videoTest.php */
