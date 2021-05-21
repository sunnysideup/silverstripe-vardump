# why this module?

This module makes debugging faster in Silverstripe.  Especially, the ability to debug directly in templates will really help you understand issues faster. 

# usage in php

You can use it directly in PHP like this:

```php

use Sunnysideup\Vardump\Vardump;

Class MyClass
{
    protected function foo()
    {
        Vardump::now($page->Children());
    }
}
```

# usage in templates

In templates, you can debug any variable or method like this:

```php

use Sunnysideup\Vardump\DebugTrait;
use Sunnysideup\Vardump\Vardump;

Class MyClass
{

    use DebugTrait;
    
}
```

Then, in `MyTemplate.ss`, you can debug any Variable or Method from `MyClass` like this:


```ss
    $VardumpMe(MyMethodOrVariable);
```
