<?php

namespace Shemi\Laradmin\Database\Schema;

use Doctrine\DBAL\Schema\Column as DoctrineColumn;
use Doctrine\DBAL\Types\Type as DoctrineType;

abstract class Column
{
    public static function make(array $column)
    {
        $name = $column['name'];
        $type = $column['type'];
        $type = ($type instanceof DoctrineType) ? $type : DoctrineType::getType(trim($type['name']));

        $options = array_diff_key($column, ['name' => $name, 'type' => $type]);

        return new DoctrineColumn($name, $type, $options);
    }

    /**
     * @param DoctrineColumn $column
     * @return array
     */
    public static function toArray(DoctrineColumn $column)
    {
        $columnArr = $column->toArray();
        $columnArr['oldName'] = $columnArr['name'];
        $columnArr['null'] = $columnArr['notnull'] ? 'NO' : 'YES';
        $columnArr['extra'] = static::getExtra($column);
        $columnArr['composite'] = false;

        return $columnArr;
    }

    /**
     * @param DoctrineColumn $column
     * @return string
     */
    protected static function getExtra(DoctrineColumn $column)
    {
        $extra = '';

        $extra .= $column->getAutoincrement() ? 'auto_increment' : '';

        return $extra;
    }
}