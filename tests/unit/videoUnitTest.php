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
 */

class videoUnitTest extends Slim_Framework_TestCase {

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
