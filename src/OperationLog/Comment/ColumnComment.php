<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/11
 * Time: 17:12
 */

namespace LinLancer\Laravel\OperationLog\Comment;


class ColumnComment
{
    protected $separator = [
        ':',
        '：',
        ' '
    ];

    protected $enumSeparator = [
        ';',
        ',',
        '；',
        '，',
    ];

    protected $enumKeyValueSeparator = '-';

    public $column;

    public $enumeration = [];

    /**
     * @param string $rawComment
     * @return ColumnComment
     */
    public static function parse(string $rawComment) :ColumnComment
    {
        $self = new static;
        foreach ($self->separator as $unit) {
            if (($start = mb_stripos($rawComment, $unit)) !== false) {
                $self->column = trim(mb_substr($rawComment, 0, $start));
                $rawEnumeration = trim(mb_substr($rawComment, $start + 1));
                if (!empty($rawEnumeration))
                    $self->enumeration = $self->loadEnumerationPair($rawEnumeration);
                break;
            }
        }
        return $self;
    }

    /**
     * @return string
     */
    public function getColumnName():string
    {
        return $this->column;
    }

    /**
     * @return array
     */
    public function getEnumeration():array
    {
        return $this->enumeration;
    }

    /**
     * @param string $rawEnum
     * @return array
     */
    protected function loadEnumerationPair(string $rawEnum):array
    {
        $enumerations = [];
        $enumSeparator = '';
        foreach ($this->enumSeparator as $separator) {
            if (mb_stripos($rawEnum, $separator) !== false) {
                $enumSeparator = $separator;
                break;
            }
        }

        $enumGroups = explode($enumSeparator, $rawEnum);
        foreach ($enumGroups as $enumGroup) {
            if (mb_stripos($enumGroup, $this->enumKeyValueSeparator) === false)
                continue;
            $keyValue = explode($this->enumKeyValueSeparator, $enumGroup);
            if (count($keyValue) !== 2)
                continue;

            $key = trim(reset($keyValue));
            $value = trim(end($keyValue));
            $enumerations[$key] = $value;
        }
        return $enumerations;
    }
}
