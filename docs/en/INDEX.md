For classes that extend Viewable Data you can do this:

```php
use Sunnysideup\Vardump\Vardump;
class MyClass extends WhateverWithViewableDataAsOneParentClass
{
    public function DebugMe($anything)
    {
        if (Vardump::inst()->isSafe()) {
            return Vardump::inst()->vardumpMe($this->{$method}(), $method);
        }
    }
}
```


If you have a class that does not extend Viewable data then you can do this:


```php
use Sunnysideup\Vardump\Vardump;
use Sunnysideup\Vardump\DebugTrait;
class MyClass
{
    use DebugTrait;

    //.....
}


```
