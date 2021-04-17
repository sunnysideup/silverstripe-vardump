# usage in php

You can use it directly in PHP like this:

```php

use Sunnysideup\Vardump\Vardump;

Class MyClass
{
    protected function foo()
    {
        Vardump::now($var);
        Vardump::inst()->mixedToUl($page->Children());
    }
}
```

# usage in templates

In templates, you can debug any variable or method like this:

```php

use Sunnysideup\Vardump\DebugTrait;

Class MyClass
{

    use DebugTrait;
}
```

Then, in `MyTemplate.ss`, you can debug any Variable or Method from `MyClass` like this:


```ss
    $DebugMe(MyMethodOrVariable);
```
