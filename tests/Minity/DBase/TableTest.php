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
            array('a', 'N', 3, 0),
            array('b', 'C', 20),
        );
        $rows = array(
            array(1, iconv('utf8' , 'cp1251', 'Russian текст 1')),
            array(2, iconv('utf8' , 'cp1251', 'Russian текст 2')),
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
        unlink($this->filename);
    }

    public function testGetRecord()
    {
        $this->assertEquals(array('a' => 1, 'b' => 'Russian текст 1', 'deleted' => 0), $this->table->getRecord(1));
        $this->assertEquals(array('a' => 2, 'b' => 'Russian текст 2', 'deleted' => 0), $this->table->getRecord(2));
        $this->assertNull($this->table->getRecord(3));
    }

    public function testGetHeaders()
    {
        $this->assertEquals(array('a', 'b', 'deleted'), $this->table->getHeaders());
    }

    public function testGetRecordsCount()
    {
        $this->assertEquals(2, $this->table->getRecordsCount());
    }
}
