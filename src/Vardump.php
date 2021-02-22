<?php

namespace Sunnysideup\Vardump;

use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;

use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;

class Vardump
{
    /**
     * @var array
     *            List of words to be replaced.
     */
    private const SQL_PHRASES = [
        'SELECT',
        'FROM',
        'WHERE',
        'HAVING',
        'GROUP',
        'ORDER BY',
        'INNER JOIN',
        'LEFT JOIN',
    ];

    protected static $singleton = null;

    public static function inst()
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    public function isSafe(): bool
    {
        return (Permission::check('ADMIN') && Director::isDev()) || Environment::getEnv('SS_VARDUMP_DEBUG_ALLOWED');
    }

    public function vardumpMeRaw($data, ?string $method = '', ?string $className = '')
    {
        return $this->vardumpMe($data, $method, $className)->raw();
    }

    public function vardumpMe($data, ?string $method = '', ?string $className = '')
    {
        if (Vardump::inst()->isSafe()) {
            $html = Vardump::inst()->mixedToUl($data) . $this->addMethodInformation($method, $className);
            return DBField::create_field('HTMLText', $html);
        }
    }

    public function mixedToUl($mixed): string
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
                    return $this->mixedToUl($mixed->sql()) . '<hr />' .
                        $this->mixedToUl($mixed->map('ID', 'Title')->toArray());
                } elseif ($mixed instanceof DataObject) {
                    return $mixed->i18n_singular_name() . ': ' . $mixed->getTitle() .
                        ' (' . $mixed->ClassName . ', ' . $mixed->ID . ')';
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
                if ($isLarge) {
                    $style = 'display: inline;';
                    $after = ', ';
                }
                $html .= '<ol>';
                $count = 0;
                foreach ($mixed as $key => $item) {
                    $count++;
                    if ($isAssoc) {
                        $keyString = '<strong>' . $key . '</strong>: ';
                    }
                    if($count > 20) {
                        $data = '.';
                        $keyString = '';
                    } else {
                        $data = $this->mixedToUl($item);
                    }
                    $html .= '<li style="' . $style . '">' . $keyString . $data . $after . '</li>';
                }
            } elseif(is_string($mixed)) {
                if($this->isSql($mixed)) {
                    $mixed = $this->stringToSqlExplainer($mixed);
                }
                return '<span style="color: green">' . substr($mixed, 0, 300) . '</span>';
            } else {
                return '<span style="color: red">[UNKNOWN OBJECT]</span>';
            }
        }
        return 'not available';
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

    protected function stringToSqlExplainer($string): string
    {
        foreach (self::SQL_PHRASES as $phrase) {
            $outcome = str_replace(
                ' ' . $phrase . ' ',
                '<br /><br />' . $phrase . ' ',
                $string
            );
        }
        return $outcome;
    }

    protected function isSql(string $string): bool
    {
        $sqlCount = false;
        foreach (self::SQL_PHRASES as $phrase) {
            if (strpos($string, $phrase)) {
                $sqlCount++;
            }
        }
        if ($sqlCount > 2) {
            return true;
        }
        return false;
    }
}
