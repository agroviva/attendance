<?php

namespace Attendance;

use AgroEgw\Api\Categories as AGRO_Categories;
use AgroEgw\DB;
use EGroupware\Api\Categories as EGW_Categories;

class Categories
{
    const MAIN = 'Attendance';

    protected static $staticCategories = [
        'parent', 'work', 'vacation', 'holiday', 'sickness',
    ];

    private static $designation = [
        'work' => [
            'name'        => 'Work',
            'description' => self::MAIN.' - '.'Work',
            'data'        => '{"color":"#aad4ff","icon":""}',
        ],
        'vacation' => [
            'name'        => 'Vacation',
            'description' => self::MAIN.' - '.'Vacation',
            'data'        => '{"color":"#aaffaa","icon":""}',
        ],
        'holiday' => [
            'name'        => 'Holiday',
            'description' => self::MAIN.' - '.'Holiday',
            'data'        => '{"color":"#ffffaa","icon":""}',
        ],
        'sickness' => [
            'name'        => 'Sickness',
            'description' => self::MAIN.' - '.'Sickness',
            'data'        => '{"color":"#ffaaaa","icon":""}',
        ],
    ];

    private static $extraDesignation = [
        'school' => [
            'name'        => 'School',
            'description' => self::MAIN.' - '.'School',
            'data'        => '{"color":"#d4aaff","icon":""}',
        ],
    ];

    private static $Categories;

    public static function init_static()
    {
        $keys = self::$staticCategories;
        foreach (self::$extraDesignation as $extraKey => $category) {
            $keys[] = $extraKey;
        }

        foreach ($keys as $key) {
            self::$Categories[$key] = self::getCategoryByKey($key);
        }
    }

    private static function getCategoryByKey($key)
    {
        if ($key === 'parent') {
            return self::getMainCategory();
        } else {
            return self::getSubCategory($key);
        }
    }

    public static function GetCategories()
    {
        self::UpdateCategories();
        $categories = Core::getMeta(false, 'categories')[0];
        if ($categories) {
            return json_decode($categories['meta_data'], true);
        }

        return self::GetCategories();
    }

    public static function UpdateCategories()
    {
        $categories = [];
        foreach (self::$Categories as $key => $category) {
            $categories[$key] = $category['meta_connection_id'];
        }
        $meta = Core::getMeta(false, 'categories')[0];
        if ($meta) {
            Core::updateMeta($meta['id'], $categories);
        } else {
            Core::setMeta('categories', '0', $categories);
        }

        return $categories;
    }

    public static function Get($unset = false)
    {
        $categories = self::GetCategories();
        $result = [];
        foreach ($categories as $key => $CategoryID) {
            $result[$key] = AGRO_Categories::Read(intval($CategoryID));
        }

        if ($unset) {
            unset($result['parent']);
        }

        return $result;
    }

    public static function getMainCategory()
    {
        $meta = Core::getMeta(false, 'mainCategory')[0];
        if ($meta) {
            if (AGRO_Categories::Read(intval($meta['meta_connection_id']))) {
                return $meta;
            } else {
                return self::insertMainCategory($meta['meta_connection_id']);
            }
        } else {
            return self::insertMainCategory();
        }
    }

    private static function getSubCategory($key)
    {
        $subcategories = Core::getMeta(false, 'subCategory');
        $subcategory = null;
        foreach ($subcategories as $category) {
            $meta = json_decode($category['meta_data'], true);
            if ($meta['key'] == $key) {
                $subcategory = $category;
            }
        }
        if ($subcategory) {
            if (AGRO_Categories::Read(intval($subcategory['meta_connection_id']))) {
                return $subcategory;
            } else {
                return self::insertSubCategory($key, $subcategory['meta_connection_id']);
            }
        } else {
            return self::insertSubCategory($key);
        }
    }

    private static function insertMainCategory(int $oldCategoryID = 0)
    {
        if ($oldCategoryID) {
            $categoryID = AGRO_Categories::New(self::MAIN, lang(self::MAIN));
            (new DB("
                UPDATE egw_categories SET cat_id = $oldCategoryID 
                WHERE cat_id = $categoryID"
            ));
            $categoryID = $oldCategoryID;
        } else {
            $categoryID = AGRO_Categories::New(self::MAIN, lang(self::MAIN));
        }

        if ($categoryID) {
            $category = AGRO_Categories::Read($categoryID);
            $categories_data = [
                'name'        => $category['name'],
                'id'          => $category['id'],
                'description' => $category['description'],
                'data'        => $category['data'],
            ];

            Core::setMeta('mainCategory', $categoryID, $categories_data);

            return Core::getMeta(false, 'mainCategory')[0];
        }
    }

    private static function insertSubCategory(string $key, int $oldCategoryID = 0)
    {
        $designations = self::$designation + self::$extraDesignation;
        $designation = $designations[$key];
        $parentID = self::getMainCategory()['meta_connection_id'];
        if (empty($designation) || !$parentID) {
            return false;
        }

        if ($oldCategoryID) {
            $categoryID = AGRO_Categories::New($designation['name'], $designation['description'], $designation['data'], $parentID);
            (new DB("
                UPDATE egw_categories SET cat_id = $oldCategoryID 
                WHERE cat_id = $categoryID"
            ));
            $categoryID = $oldCategoryID;
        } else {
            $categoryID = AGRO_Categories::New($designation['name'], $designation['description'], $designation['data'], $parentID);
        }

        if ($categoryID) {
            $category = AGRO_Categories::Read($categoryID);
            $categories_data = [
                'name'        => $category['name'],
                'id'          => $category['id'],
                'description' => $category['description'],
                'data'        => $category['data'],
                'key'         => $key,
            ];

            Core::setMeta('subCategory', $categoryID, $categories_data);

            $subcategories = Core::getMeta(false, 'subCategory');
            $subcategory = null;
            foreach ($subcategories as $category) {
                $meta = json_decode($category['meta_data'], true);
                if ($meta['key'] == $key) {
                    $subcategory = $category;
                }
            }

            return $subcategory;
        }
    }
}
Categories::init_static();
