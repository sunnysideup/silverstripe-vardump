<?php

namespace Sunnysideup\Vardump;

use SilverStripe\Security\Permission;
use Sunnysideup\Vardump\Vardump;

/**
 * small trait to make non-Viewable objects printable.
 */
trait DebugTrait
{

    /**
     * Get the value of a field on this object, automatically inserting the value into any available casting objects
     * that have been specified.
     *
     * @param string $fieldName
     * @param array $arguments
     * @param bool $cache Cache this object
     * @param string $cacheName a custom cache name
     * @return object|DBField
     */
    public function obj($fieldName, $arguments = [], $cache = false, $cacheName = null)
    {
        if (Vardump::inst()->isSafe()) {
            $data = call_user_func_array([$this, $fieldName], $arguments ?: []);
            return Vardump::inst()->vardumpMe($data, $method);
        }
    }

    /**
     * for debug purposes!
     * @param string $method
     */
    public function XML_val(?string $method, $arguments = [])
    {
        if (Vardump::inst()->isSafe()) {
            if (! is_array($arguments)) {
                $arguments = [$arguments];
            }
            $data = $this->{$method}(...$arguments);
            return Vardump::inst()->vardumpMe($data, $method);
        }
    }

    public function ClassName(): string
    {
        return static::class;
    }


}
