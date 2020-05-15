<?php
/**
 * Created by PhpStorm.
 * User: $_s
 * Date: 2020/5/14
 * Time: 17:30
 */

namespace LinLancer\Laravel;



class Config
{
    protected static $config = [];

    public function __construct(array $config)
    {
        self::$config = $config;
    }

    public static function getClasses($rootOnly = true)
    {
        new static(config('operation_logger'));
        $classes = [];
        foreach (self::$config['register_class'] as $class) {

                $classes[] = [
                    'class_name' => $class['class_name'] ?? '',
                    'short_tag' => $class['short_tag'] ?? '',
                    'short_tag_en' => $class['short_tag_en'] ?? '',
                    'tagged_field' => $class['tagged_field'] ?? '',
                    'related_key' => $class['related_key'] ?? '',
                ];

            if (!$rootOnly) {
                foreach ($class['related_with'] ?? [] as $item) {
                    $classes[] = [
                        'class_name' => $item['class_name'] ?? '',
                        'short_tag' => $item['short_tag'] ?? '',
                        'short_tag_en' => $item['short_tag_en'] ?? '',
                        'tagged_field' => $item['tagged_field'] ?? '',
                        'related_key' => $item['related_key'] ?? '',
                    ];
                }
            }
        }
        return $classes;

    }

    public static function getMapping($field1, $field2, $rootOnly = true)
    {
        $classes = self::getClasses($rootOnly);
        $mapping = [];
        foreach ($classes as $class) {
            $mapping[$class[$field1] ?? ''] = $class[$field2] ?? '';
        }
        return $mapping;
    }

}