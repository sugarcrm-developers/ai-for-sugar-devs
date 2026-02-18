## Tech Stack & Frameworks
- **Runtime:** PHP **8.3** (primary), **8.4** compatible
- **App:** SugarCRM **25.1** (Sidecar frontend)
- **Sidecar/JS:** Backbone.js, Underscore.js, Handlebars, jQuery
- **Search/DB:** Elasticsearch 8.x (if edition), MySQL 8.x
- **Packaging:** Module Loader **Package Builder** (preferred) or manual `custom/` deploy

### PHP 8.3/8.4 Rules (Custom Code)
- Avoid dynamic properties; use typed properties + **return types**
- Use `DateTimeImmutable`/`SugarDateTime`
- Strict comparisons (`===`), validate array keys, typed exceptions
- Namespaced PSR-4 classes under `custom/src/...` (hooks, jobs, APIs)

## Programming Patterns (Concise)

### Logic Hooks (PHP, namespaced)
**Registry:** `custom/modules/<Module>/logic_hooks.php`
```php
<?php
$hook_version = 1; $hook_array = $hook_array ?? [];
$hook_array['after_save'][] = [10,'DoThing',
  '',
  'Sugarcrm\\Sugarcrm\\custom\\inc\\LogicHooks\\Module\\DoThing','run'];
Class: custom/src/LogicHooks/Module/DoThing.php

<?php
namespace Sugarcrm\Sugarcrm\inc\custom\LogicHooks\Module;

use SugarBean;

final class DoThing {
  public function run(SugarBean $bean, string $event, array $args): void {
    // implementation
  }
}
