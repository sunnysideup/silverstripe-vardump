<?php
namespace Sunnysideup\Vardump;

use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DataList;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;
use SilverStripe\Control\Director;


class Vardump
{

    public static function mixed_to_ul($mixed): string
    {
        if (Permission::check('ADMIN') && Director::isDev()) {
            if ($mixed === false) {
                return '<span style="color: grey">[NO]</span>';
            } elseif ($mixed === true) {
                return '<span style="color: grey">[YES]</span>';
            } elseif ($mixed === null) {
                return '<span style="color: grey">[NULL]</span>';
            } elseif ($mixed === '') {
                return '<span style="color: grey">[EMPTY STRING]</span>';
            } elseif (is_array($mixed) && count($mixed) === 0) {
                return '<span style="color: grey">[EMPTY ARRAY]</span>';
            } elseif (is_object($mixed)) {
                if ($mixed instanceof ArrayData) {
                    return self::mixed_to_ul($mixed->toMap());
                } elseif ($mixed instanceof ArrayList) {
                    return self::mixed_to_ul($mixed->toArray());
                } elseif ($mixed instanceof DataList) {
                    return self::mixed_to_ul($mixed->map('ID', 'Title')->toArray());
                } elseif ($mixed instanceof DataObject) {
                    return $mixed->i18n_singular_name() . ': '.$mixed->getTitle() . ' ('.$mixed->ClassName.', '.$mixed->ID.')';
                } else {
                    return print_r($mixed, 1);
                }
            } elseif (is_array($mixed)) {
                $html = '';
                $isAssoc = self::isAssoc($mixed);
                $count = count($mixed);
                $isLarge = false;
                if($count > 1) {
                    $html .= '' . count($mixed) . ' entries ... ';
                    $isLarge = count($mixed) > 20;
                }
                $after = '';
                $style = '';
                $keyString = '';
                $countStr = '';
                if ($isLarge) {
                    $style = 'display: inline;';
                    $after = ', ';
                }
                $html .= '<ul>';
                foreach ($mixed as $key => $item) {
                    if ($isAssoc) {
                        $keyString = '<strong>' . $key . '</strong>: ';
                    }
                    $html .= '<li style="' . $style . '">' . $keyString . $countStr . self::mixed_to_ul($item) . $after . '</li>';
                }
                return $html . '</ul>';
            }
            return '<span style="color: green">' . $mixed . '</span>';
        }
    }

    protected static function isAssoc(array $arr)
    {
        if ($arr === []) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
