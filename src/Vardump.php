<?php

namespace Sunnysideup\Vardump;

use SilverStripe\Control\Director;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\Debug;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\DataList;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\ORM\PaginatedList;
use SilverStripe\Security\Permission;
use SilverStripe\View\ArrayData;

class Vardump
{
    /**
     * @var array
     *            List of words to be replaced
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

    protected static $singleton;

    public static function formatted_variable($data = null): string
    {
        $html = '';
        $html .= '<div style="max-width: calc(100% - 20px); width:fit-content; margin: 20px;">';
        $html .= self::inst()->mixedToUl($data);

        return $html . '</div>';
    }

    public static function now($data = null, ?string $method = '', ?string $className = '')
    {
        echo '<div style="max-width: calc(100% - 20px); width:fit-content; margin: 20px auto;">';
        echo self::inst()->vardumpMe($data, $method, $className)->RAW();
        echo '</div>';
    }

    public static function inst()
    {
        if (null === self::$singleton) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    public function isSafe(): bool
    {
        return (Permission::check('ADMIN') && (Director::isDev() || Environment::getEnv('SS_VARDUMP_DEBUG_ALLOWED')));
    }

    public function vardumpMeRaw($data, ?string $method = '', ?string $className = '')
    {
        return $this->vardumpMe($data, $method, $className)->raw();
    }

    /**
     * @param mixed  $data
     * @param string $method
     * @param string $className
     *
     * @return null|DBHTMLText
     */
    public function vardumpMe($data, ?string $method = '', ?string $className = '')
    {
        $obj = null;
        if (Vardump::inst()->isSafe()) {
            $html = Vardump::inst()->mixedToUl($data) . $this->addMethodInformation($method, $className);
            /** @var DBHTMLText $obj */
            $obj = DBHTMLText::create_field('HTMLText', $html);
        } elseif (Director::isDev()) {
            /** @var DBHTMLText $obj */
            $obj = DBHTMLText::create_field('HTMLText', 'Error: please login');
        }

        return $obj;
    }

    public function mixedToUl($mixed): string
    {
        if ($this->isSafe()) {
            if (false === $mixed) {
                return '<span style="color: grey">[NO]</span>';
            }

            if (true === $mixed) {
                return '<span style="color: grey">[YES]</span>';
            }

            if (null === $mixed) {
                return '<span style="color: grey">[NULL]</span>';
            }

            if (0 === $mixed) {
                return '<span style="color: green">[ZERO]</span>';
            }

            if (1 === $mixed) {
                return '<span style="color: green">[ONE]</span>';
            }

            if (is_int($mixed)) {
                return '<span style="color: green">' . $mixed . '</span>';
            }

            if (is_float($mixed)) {
                return '<span style="color: green">' . $mixed . '</span>';
            }

            if ('' === $mixed) {
                return '<span style="color: grey">[EMPTY STRING]</span>';
            }

            if (is_array($mixed) && [] === $mixed) {
                return '<span style="color: grey">[EMPTY ARRAY]</span>';
            }

            if (is_object($mixed)) {
                if ($mixed instanceof ArrayData) {
                    return $this->mixedToUl($mixed->toMap());
                }

                if ($mixed instanceof ArrayList) {
                    return $this->mixedToUl($mixed->toArray());
                }

                if ($mixed instanceof DataList || $mixed instanceof PaginatedList) {
                    $parameters = null;
                    $sql = $mixed->sql($parameters);
                    $sql = DB::inline_parameters($sql, $parameters);
                    $sql = str_replace('"', '`', $sql);

                    return
                        $this->mixedToUl($sql) . '<hr />' .
                        $this->mixedToUl($mixed->map('ID', 'Title')->toArray());
                }

                if ($mixed instanceof DataObject) {
                    return $mixed->i18n_singular_name() . ': ' . $mixed->getTitle() .
                        ' (' . $mixed->ClassName . ', ' . $mixed->ID . ')';
                }

                return '<span style="color: red">' . substr(Debug::text($mixed), 0, 500) . '</span>';
            }

            if (is_array($mixed)) {
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

                $itemHTML = '<ol>';
                $count = 0;
                $itemHTML = '';
                $flatArray = true;
                foreach ($mixed as $key => $item) {
                    if (is_array($item) || is_object($item)) {
                        $flatArray = false;
                    }

                    ++$count;
                    if ($isAssoc) {
                        $keyString = '<strong>' . $key . '</strong>: ';
                    }

                    if ($count > 20) {
                        $data = '.';
                        $keyString = '';
                    }

                    if (!$flatArray) {
                        $mixed[$key] = $this->mixedToUl($item);
                    }

                    $itemHTML .= '<li style="' . $style . '">' . $keyString . $mixed[$key] . $after . '</li>';
                }

                if ($flatArray) {
                    $itemHTML = ArrayToTable::convert($mixed, 10, 100);
                } else {
                    $itemHTML .= '</ol>';
                }

                return $html . $itemHTML;
            }

            if (is_string($mixed)) {
                $isSql = '';
                if ($this->isSql($mixed)) {
                    $mixed = $this->stringToSqlExplainer($isSql . $mixed);
                }

                return '<span style="color: green">' . substr($mixed, 0, 10000) . '</span>';
            }

            return '<span style="color: red">' . substr(Debug::text($mixed), 0, 500) . '</span>';
        }

        return '<span style="color: red">ERROR: please turn on SS_VARDUMP_DEBUG_ALLOWED to see data.</span>';
    }

    protected function isAssoc(array $arr)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    protected function addMethodInformation($method, $className)
    {
        $callers = debug_backtrace();
        foreach ($callers as $call) {
            if ($call['class'] !== static::class) {
                break;
            }
        }

        if (!$method) {
            $method = $call['function'] ?? 'unknown_method';
        }

        if (!$className) {
            $className = $call['class'] ?? 'unknown_class';
        }

        // foreach($call as $key => $value) {
        //     echo $key;
        // }
        $args = $call['args'] ?? '';

        return '
            <div style="color: blue; font-size: 12px; margin-top: 0.7rem;">
                ⇒' . $className . '::<strong>' . $method . '(' . print_r($args, 1) . ')</strong>
            </div>
            <hr style="margin-bottom: 2rem;"/>
        ';
    }

    protected function stringToSqlExplainer($string): string
    {
        $string = ' ' . $string . ' ';
        $output = preg_replace('#\s+#', ' ', $string);
        foreach (self::SQL_PHRASES as $phrase) {
            $output = str_replace(
                $phrase,
                '<br /><br />' . $phrase . ' ',
                $output
            );
        }

        return $output;
    }

    protected function isSql(string $string): bool
    {
        $sqlCount = 0;
        foreach (self::SQL_PHRASES as $phrase) {
            if (false !== stripos($string, $phrase)) {
                ++$sqlCount;
            }
        }

        return $sqlCount > 2;
    }
}
