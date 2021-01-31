For classes that extend Viewable Data you can do this:

```php
use Sunnysideup\Vardump\Vardump;
class MyClass extends WhateverWithViewableDataAsOneParentClass
{
    public function DebugMe($anything)
    {
        echo Vardump::mixed_to_ul($anything);
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
