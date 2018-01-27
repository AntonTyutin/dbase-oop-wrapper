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

class RowsIterator implements \Iterator
{

    private $db;

    private $current;

    private $last;

    public function __construct(Table $db)
    {
        $this->db = $db;
        $this->last = $db->getRecordsCount();
    }

    public function current()
    {
        if (!$this->valid()) {
            return null;
        }
        return $this->db->getRecord($this->current);
    }

    public function key()
    {
        return $this->current;
    }

    public function next()
    {
        if (++$this->current > $this->last) {
            $this->current = null;
        }
    }

    public function rewind()
    {
        $this->current = 1;
    }

    public function valid()
    {
        return null !== $this->current;
    }
}
