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

class Table
{
    const MODE_READONLY  = 0;
    const MODE_WRITEONLY = 1;
    const MODE_READWRITE = 2;

    private $db;

    private $mode;

    private $dbFilename;

    private $columns;

    public function __construct($dbFilename, $mode = self::MODE_READONLY, $encoding = 'utf-8')
    {
        $this->dbFilename = $dbFilename;
        $this->mode = $mode;
        $this->encoding = $encoding;
    }

    private function open()
    {
        return dbase_open($this->dbFilename, $this->mode);
    }

    private function getDbHandler()
    {
        if (!$this->db) {
            $this->db = $this->open();
        }
        return $this->db;
    }

    /**
     * Retrieve record (as is) at given position
     * @param integer $position Record position
     * @return mixed Associative array of fields and values or null if $position out of range
     */
    public function getRecordRaw($position)
    {
        $record = @dbase_get_record_with_names($this->getDbHandler(), $position);
        return $record === false ? null : $record;
    }

    /**
     * Retrieve record (normalized values) at given position
     * @param integer $position Record position
     * @return mixed Associative array of fields and values or null if $position out of range
     */
    public function getRecord($position)
    {
        $record = $this->getRecordRaw($position);
        if ($record !== null) {
            foreach ($record as $field => $value) {
                if ($field === 'deleted') {
                    $record['deleted'] = (boolean)$value;
                } else {
                    $column = $this->getColumn($field);
                    $record[$field] = $this->formatValue($column['type'], $value);
                }
            }
        }
        return $record;
    }

    public function getHeaders()
    {
        return array_keys($this->getRecordRaw(0));
    }

    public function getColumns()
    {
        if (!$this->columns) {
            $this->columns = array();
            $columns = @dbase_get_header_info($this->getDbHandler());
            foreach ($columns as $idx => $col) {
                $this->columns[$idx] = $col;
                $this->columns[$col['name']] =& $this->columns[$idx];
            }
        }
        return $this->columns;
    }

    public function getColumn($idx)
    {
        $columns = $this->getColumns();
        return $columns[$idx];
    }

    public function getRecordsCount()
    {
        return dbase_numrecords($this->getDbHandler());
    }

    public function getIterator()
    {
        return new RowsIterator($this);
    }

    private function formatValue($type, $value)
    {
        switch ($type) {
            case 'number':
                $value = (float)$value;
                break;
            case 'date':
                $value = \DateTime::createFromFormat('Ymdhis', $value . '000000');
                break;
            case 'boolean':
                $value = (boolean)$value;
                break;
            default:
                $value = iconv($this->encoding, 'utf8', trim($value));
        }
        return $value;
    }
}
