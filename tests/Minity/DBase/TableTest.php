<?php
/*
 * This file is part of the "dbase-oop-wrapper" package.
 *
 * Copyright 2012 Anton Tyutin <anton@tyutin.ru>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Minity\DBase;

class TableTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Table
     */
    private $table;


    private $filename;

    protected function setUp()
    {
        $fields = array(
            array('a', 'N', 3, 1),
            array('b', 'C', 20),
            array('c', 'L'),
            array('d', 'D'),
        );
        $rows = array(
            array(1.2, iconv('utf8' , 'cp1251', 'Russian текст 1'), 'T', '20120505'),
            array(2.4, iconv('utf8' , 'cp1251', 'Russian текст 2'), 'F', '20120105'),
        );
        $this->filename = self::createDatabase($fields, $rows);
        $this->table = new Table($this->filename, Table::MODE_READONLY, 'cp1251');
    }

    protected static function createDatabase(array $fields, array $data = array())
    {
        $filename = uniqid(__CLASS__) . '.db';
        $dbh = dbase_create($filename, $fields);
        foreach ($data as $row) {
            dbase_add_record($dbh, $row);
        }
        return $filename;
    }

    protected function tearDown()
    {
        @unlink($this->filename);
    }

    /**
     * @expectedException \Minity\DBase\Exception\TableOpenException
     * @expectedExceptionMessage not found
     */
    public function testOpenNotExistsError()
    {
        unlink($this->filename);
        $this->table->getHeaders();
    }

    /**
     * @expectedException \Minity\DBase\Exception\TableOpenException
     * @expectedExceptionMessage unable to open database
     */
    public function testOpenInvalidDatabaseError()
    {
        file_put_contents($this->filename, str_repeat('123_', 10) . PHP_EOL);
        $this->table->getHeaders();
    }

    public function testGetRecord()
    {
        $this->assertEquals(
            array('a' => 1.2, 'b' => 'Russian текст 1', 'c' => true, 'd' => new \DateTime('2012-05-05'), 'deleted' => 0),
            $this->table->getRecord(1)
        );
        $this->assertEquals(
            array('a' => 2.4, 'b' => 'Russian текст 2', 'c' => false, 'd' => new \DateTime('2012-01-05'), 'deleted' => 0),
            $this->table->getRecord(2)
        );
        $this->assertNull($this->table->getRecord(3));
    }

    public function testGetHeaders()
    {
        $this->assertEquals(array('a', 'b', 'c', 'd', 'deleted'), $this->table->getHeaders());
    }

    public function testGetRecordsCount()
    {
        $this->assertEquals(2, $this->table->getRecordsCount());
    }

    public function testGetColumns()
    {
        $columns = $this->table->getColumns();
        $this->assertEquals('a', $columns[0]['name']);
        $this->assertEquals('number', $columns[0]['type']);
        $this->assertEquals(3, $columns[0]['length']);
        $this->assertEquals('b', $columns[1]['name']);
        $this->assertEquals('character', $columns[1]['type']);
        $this->assertEquals(20, $columns[1]['length']);
        $this->assertEquals('c', $columns[2]['name']);
        $this->assertEquals('boolean', $columns[2]['type']);
        $this->assertEquals(1, $columns[2]['length']);
    }
}
