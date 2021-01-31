<?php

namespace Sunnysideup\Vardump;

use SilverStripe\Control\Director;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;

use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Environment;

class Vardump
{

    protected static $singleton = null;

    public static function inst()
    {
        if(self::$singleton === null) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    public function isSafe(): bool
    {
        return (Permission::check('ADMIN') && Director::isDev()) || Environment::getEnv('SS_VARDUMP_DEBUG_ALLOWED');
    }

    public function vardumpMe($data, string $method, string $className)
    {
        if (Vardump::inst()->isSafe()) {
            $html = Vardump::inst()->mixedToUl($data) . $this->addMethodInformation($method, $className);

            return DBField::create_field('HTMLText', $html);
        }
    }


    protected function mixedToUl($mixed): string
    {
        if ($this->isSafe()) {
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
                    return $this->mixedToUl($mixed->toMap());
                } elseif ($mixed instanceof ArrayList) {
                    return $this->mixedToUl($mixed->toArray());
                } elseif ($mixed instanceof DataList || $mixed instanceof PaginatedList) {
                    return $this->mixedToUl($mixed->map('ID', 'Title')->toArray());
                } elseif ($mixed instanceof DataObject) {
                    return $mixed->i18n_singular_name() . ': ' . $mixed->getTitle() . ' (' . $mixed->ClassName . ', ' . $mixed->ID . ')';
                }
                return print_r($mixed, 1);
            } elseif (is_array($mixed)) {
                $html = '';
                $isAssoc = $this->isAssoc($mixed);
                $count = count($mixed);
                $isLarge = false;
                if ($count > 1) {
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
                    $html .= '<li style="' . $style . '">' . $keyString . $countStr . $this->mixedToUl($item) . $after . '</li>';
                }
                return $html . '</ul>';
            }
            return '<span style="color: green">' . $mixed . '</span>';
        } else {
            return 'not available';
        }
    }

    protected function isAssoc(array $arr)
    {
        if ($arr === []) {
            return false;
        }
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function addMethodInformation($method, $className)
    {
        return '
            <div style="color: blue; font-size: 12px; margin-top: 0.7rem;">
                ⇒' . $className . '::<strong>' . $method . '</strong>
            </div>
            <hr style="margin-bottom: 2rem;"/>
        ';
    }
}
