# Sti

[![Build Status](https://img.shields.io/travis/UseMuffin/Sti/master.svg?style=flat-square)](https://travis-ci.org/UseMuffin/Sti)
[![Coverage](https://img.shields.io/coveralls/UseMuffin/Sti/master.svg?style=flat-square)](https://coveralls.io/r/UseMuffin/Sti)
[![Total Downloads](https://img.shields.io/packagist/dt/muffin/sti.svg?style=flat-square)](https://packagist.org/packages/muffin/sti)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

Single Table Inheritance for CakePHP 3.8+ ORM.

> [...] a way to emulate object-oriented inheritance in a relational database. When mapping from a database
> table to an object in an object-oriented language, a field in the database identifies what class in the
> hierarchy the object belongs to.

(source: [Wikipedia][1])

## Install

Using [Composer][composer]:

```
composer require muffin/sti:1.0.x-dev
```

You then need to load the plugin. You can use the shell command:

```
bin/cake plugin load Muffin/Sti
```

## Usage

```php
<?php // src/Model/Table/CooksTable.php
namespace App\Model\Table;

use Cake\ORM\Table;

class CooksTable extends Table {

    public function initialize($config)
    {
        $this->table('sti_cooks');
        $this->addBehavior('Muffin/Sti.Sti', [
            'typeMap' => [
                'chef' => 'App\Model\Entity\Chef',
                'baker' => 'App\Model\Entity\Baker',
                'assistant_chef' => 'App\Model\Entity\AssistantChef',
            ]
        ]);

        // Optionally, set the default type. If none is defined, the
        // first one (i.e. `chef`) will be used.
        $this->setEntityClass('App\Model\Entity\AssistantChef');
    }
}
```

Then create a class for every type of entity:

- Chef
- Baker
- AssistantChef

The entity that was previously defined to be the 'default' one will need to use the `StiAwareTrait`:

```
<?php // src/Model/Entity/AssistantChef.php
namespace App\Model\Entity;

use Cake\ORM\Entity;
use Muffin\Sti\Model\Entity\StiAwareTrait;

class AssistantChef extends Entity
{
    use StiAwareTrait;
}
```

Optionally, you can create classes for your tables that extend the parent table to encapsulate business logic:

```php
<?php // src/Model/Table/ChefsTable.php
namespace App\Model\Table;

class ChefsTable extends CooksTable
{
  // ...
}
```

I said optionally, because if the only thing you need is some extra validation rules, you could define those
on the parent table. For example, to add custom rules to chefs:

```php
// src/Model/Table/CooksTable.php
public function validationChefs(Validator $validator)
{
    // ...
    return $validator;
}
```

### New entities

 The behavior will automatically add helper methods for creating entities of different types
 (i.e. `newChef()`). There are different ways of creating new entities, all are valid and depending
 on the cases, you might need one or the other:

 ```php
 // using the parent table
 $cooks->newChef([...]);

 // or, using the parent table again
 $cooks->newEntity(['type' => 'chef', ...]);

 // or, using the child table
 $chefs->newEntity([...]);
 ```

### Note

For the above examples to work using (*chef), you need to add a custom rule to the `Inflector`:

```php
Cake\Utility\Inflector::rules('plural', ['/chef$/i' => '\1Chefs']);
```

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of
their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

To ensure your PRs are considered for upstream, you MUST follow the [CakePHP coding standards][standards].

## Bugs & Feedback

http://github.com/usemuffin/sti/issues

## License

Copyright (c) 2015, [Use Muffin][muffin] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
[muffin]:http://usemuffin.com
[standards]:http://book.cakephp.org/3.0/en/contributing/cakephp-coding-conventions.html
[1]:https://en.wikipedia.org/wiki/Single_Table_Inheritance
