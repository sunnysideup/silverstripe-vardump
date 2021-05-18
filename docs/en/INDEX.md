For classes that extend Viewable Data you can do this:

```php
use Sunnysideup\Vardump\Vardump;
class MyClass extends WhateverWithViewableDataAsOneParentClass
{
    public function VardumpMe(string $method)
    {
        return Vardump::inst()->vardumpMe($this->{$method}(), $method, static::class);
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


In the template you can then use:

```ss

$VardumpMe(VariableOrMethod)

```
