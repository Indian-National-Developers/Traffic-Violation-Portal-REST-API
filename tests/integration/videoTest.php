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
 * This class test for all behaviors of the `VIDEO` endpoint against the TEST Database
 * in tests/testData.sql
 *
 * (In this version the tests are run against app/routes/testdata.json, which will be
 * changed in future)
 *
 * @author          saiy2k <http://saiy2k.blogspot.in>
 * @link            https://github.com/GethuGames/Traffic-Violation-Portal-REST-API
 *
 */

class videoTest extends Slim_Framework_TestCase {

    /**
     * Test the Number of Videos in the regular GET request
     */
    public function testVideoCountInPage1() {
        $this->get('/video/');
        $this->assertEquals(200, $this->response->status());

        $rawResponse                =   $this->response->body();
        $jsonResponse               =   json_decode($rawResponse);

        //fwrite(STDERR, print_r($jsonResponse, TRUE));
        $this->assertSame(20, count($jsonResponse->data));
    }

    /**
     * Test the Number of Videos in Total
     */
    public function testVideoCountInTotal() {
        $cursor                     =   '/video/';
        $totalVideoCount            =   0;

        do {
            $this->get($cursor);
            $this->assertEquals(200, $this->response->status());

            $rawResponse            =   $this->response->body();
            $jsonResponse           =   json_decode($rawResponse);
            $totalVideoCount        +=  count($jsonResponse->data);
            $cursor                 =   $jsonResponse->paging->next;
        } while ($cursor !== null);

        $this->assertSame(61, $totalVideoCount);
    }


    public function traversePagesOfVideos() {
        $this->get('/video/');
        $this->assertEquals(200, $this->response->status());

        $rawResponse                =   $this->response->body();
        $jsonResponse               =   json_decode($rawResponse);
    }

    /**
     * Test the Video IDs of the returned videos.
     */
    public function testVideoIDs() {
        $this->get('/video/');
        $this->assertEquals(200, $this->response->status());

        $rawResponse                =   $this->response->body();
        $jsonResponse               =   json_decode($rawResponse);
        $vidJSON                    =   $jsonResponse->data[0];
        $videoID                    =   $vidJSON->videoID;
        $this->assertSame('51', $videoID);

        $vidJSON                    =   $jsonResponse->data[1];
        $videoID                    =   $vidJSON->videoID;
        $this->assertSame('50', $videoID);

        $vidJSON                    =   $jsonResponse->data[2];
        $videoID                    =   $vidJSON->videoID;
        $this->assertSame('52', $videoID);
    }

}

/* End of file videoTest.php */
