# SugarCRM Sugar MLP Generator - Master Reference Guide

This is a comprehensive reference document derived from the SugarCRM Developer Guide 25.2 to ensure accurate, non-hallucinating package generation.

## Extension Framework Overview

The Extension Framework allows developers to customize Sugar without modifying core files. All customizations use the following base path pattern:

```
./custom/Extension/
```

### Key Extension Framework Paths

| Path | Purpose |
|------|---------|
| `custom/Extension/modules/<Module>/Ext/LogicHooks/` | Logic hook definitions |
| `custom/Extension/modules/<Module>/Ext/Vardefs/` | Custom field definitions |
| `custom/Extension/modules/<Module>/Ext/Language/` | Language strings |
| `custom/Extension/modules/<Module>/Ext/Views/` | View customizations |
| `custom/Extension/modules/<Module>/Ext/Layouts/` | Layout customizations |
| `custom/Extension/fields/<fieldtype>/` | Custom field types |
| `custom/Extension/application/Ext/TableDictionary/` | Relationship definitions |
| `custom/Extension/application/Ext/LogicHooks/` | Application-level hooks |
| `custom/metadata/` | Custom relationship metadata |

---

## Logic Hooks

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/architecture/logic_hooks/

### Deployment Methods

1. **Extension Framework (Recommended for plugins)**
   - Location: `custom/Extension/modules/<Module>/Ext/LogicHooks/<filename>.php`
   - Best for distributed packages
   - Uses manifest `hookdefs` array

2. **Logic Hooks installdefs (Promotion/Code Deployment)**
   - Used with manifest `logic_hooks` array
   - Modifies `custom/modules/<Module>/logic_hooks.php`
   - Can be conditionally applied via post_execute scripts

### Module Hook Types

| Hook | Trigger | Arguments |
|------|---------|-----------|
| `before_save` | Before record save | `$bean, $event, $arguments` |
| `after_save` | After record save | `$bean, $event, $arguments` |
| `before_delete` | Before record delete | `$bean, $event, $arguments` |
| `after_delete` | After record delete | `$bean, $event, $arguments` |
| `before_restore` | Before record restore | `$bean, $event, $arguments` |
| `after_restore` | After record restore | `$bean, $event, $arguments` |
| `before_relationship_add` | Before relationship creation | `$bean, $event, $arguments` |
| `after_relationship_add` | After relationship creation | `$bean, $event, $arguments` |
| `before_relationship_delete` | Before relationship deletion | `$bean, $event, $arguments` |
| `after_relationship_delete` | After relationship deletion | `$bean, $event, $arguments` |
| `process_record` | During record processing | `$bean` |
| `after_retrieve` | After record retrieval | `$bean` |

### Hook Definition (Extension Framework)

```php
<?php
// custom/Extension/modules/<Module>/Ext/LogicHooks/<filename>.php

$hook_array['<event_name>'][] = array(
    <process_index>,        // Integer - execution order
    '<description>',         // String - human readable description
    '<file_path>',          // String - path to class file or null for namespaced
    '<class_name>',         // String - class name (with namespace if applicable)
    '<method_name>',        // String - method name
);
```

### Namespaced Hook Example (Recommended)

```php
<?php
// custom/Extension/modules/Accounts/Ext/LogicHooks/custom_webhook.php

$hook_array['after_save'][] = array(
    100,
    'Custom Webhook Handler',
    null,  // null for namespaced classes
    'Sugarcrm\\Sugarcrm\\custom\\modules\\Accounts\\CustomWebhookHook',
    'afterSaveWebhook'
);
```

```php
<?php
// custom/modules/Accounts/CustomWebhookHook.php

namespace Sugarcrm\Sugarcrm\custom\modules\Accounts;

class CustomWebhookHook
{
    public function afterSaveWebhook($bean, $event, $arguments)
    {
        // Logic here
    }
}
```

### Hook Method Signature Requirements

- **Objects passed by reference**: As of PHP 5.3, objects are automatically passed by reference. DO NOT use `&$bean`.
- **Comparing values**: Use `$bean->fetched_row['{field}']` to compare with previous values
- **Return values**: Some hooks may expect return values (e.g., `after_save` can return a value)

### Manifest Configuration for Hooks

**Using Extension Framework (hookdefs):**
```php
$installdefs = array(
    'hookdefs' => array(
        array(
            'from' => '<basepath>/Files/custom/Extension/Accounts/Ext/LogicHooks/custom_webhook.php',
            'to_module' => 'Accounts',
        )
    ),
);
```

**Using Logic Hooks installdefs:**
```php
$installdefs = array(
    'logic_hooks' => array(
        array(
            'module' => 'Accounts',
            'hook' => 'after_save',
            'order' => 100,
            'description' => 'Custom webhook handler',
            'file' => 'custom/modules/Accounts/CustomWebhookHook.php',
            'class' => 'CustomWebhookHook',
            'function' => 'afterSaveWebhook',
        ),
    ),
);
```

---

## Custom Fields (Vardefs)

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/data_framework/vardefs/manually_creating_custom_fields/

### Important Caveats

- Field names MUST end with `_c` suffix
- Custom fields must have `source` property set to `'custom_fields'`
- Each property must be set individually using dictionary keys (not array merge)
- After vardef installation, run Quick Repair and Rebuild
- If module has no existing custom fields, must manually create `<Module>_cstm` table first

### Standard Field Types (Vardefs)

| Type | Usage | Example Properties |
|------|-------|-------------------|
| `varchar` | Text field | `len: 255` |
| `text` | Large text field | `len: 1000` |
| `int` | Integer | - |
| `bool` | Checkbox | `default: false` |
| `date` | Date picker | - |
| `datetime` | Date and time | `enable_range_search: false` |
| `decimal` | Decimal number | - |
| `enum` | Dropdown (use `ext1` for list) | `ext1: 'list_name'` |
| `multienum` | Multi-select dropdown | `ext1: 'list_name'` |
| `encrypt` | Encrypted text | - |
| `relate` | Relationship field | `module: 'Related Module'` |

### Vardef Definition (Extension Framework)

```php
<?php
// custom/Extension/modules/<Module>/Ext/Vardefs/<filename>.php

$vardefs['fields']['field_name_c']['name'] = 'field_name_c';
$vardefs['fields']['field_name_c']['vname'] = 'LBL_FIELD_NAME';
$vardefs['fields']['field_name_c']['type'] = 'varchar';
$vardefs['fields']['field_name_c']['len'] = '255';
$vardefs['fields']['field_name_c']['required'] = false;
$vardefs['fields']['field_name_c']['source'] = 'custom_fields';
$vardefs['fields']['field_name_c']['audited'] = false;
$vardefs['fields']['field_name_c']['reportable'] = true;
$vardefs['fields']['field_name_c']['duplicate_merge'] = 'disabled';
```

### Using ModuleInstaller for Complex Fields

For dropdown, multiselect, encrypted, and other complex fields, use `ModuleInstaller::install_custom_fields()`:

```php
$fields = array(
    array(
        'name' => 'dropdown_field_c',
        'label' => 'LBL_DROPDOWN_FIELD',
        'type' => 'enum',
        'module' => 'Accounts',
        'ext1' => 'account_type_dom',  // maps to list name
        'default_value' => 'Analyst',
        'required' => false,
        'reportable' => true,
        'audited' => false,
        'importable' => 'true',
        'duplicate_merge' => false,
    ),
);

$moduleInstaller = new ModuleInstaller();
$moduleInstaller->install_custom_fields($fields);
```

---

## Custom Field Types

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/cookbook/creating_custom_fields/

### When to Use Custom Field Types

Custom field types are needed when:
- Standard field types (varchar, int, etc.) don't meet requirements
- Custom rendering logic is needed in edit/detail views
- Special JavaScript interactivity is required
- Custom display formatting is needed

### Required Files for Custom Field Types

1. **PHP Class** (`custom/Extension/fields/<fieldtype>/<fieldtype>.php`)
   - Extends `SugarFieldBase`
   - Implements `getClassname()` method

2. **JavaScript View** (`custom/Extension/fields/<fieldtype>/<fieldtype>.js`)
   - Extends Backbone Field view
   - Sets `fieldType` property
   - Implements `setEditValue()` and `getDisplayValue()`
   - Handles events and DOM manipulation

3. **Edit Template** (`custom/Extension/fields/<fieldtype>/templates/edit.hbs`)
   - Handlebars template for edit view
   - Uses expressions like `{{value}}`, `{{name}}`, `{{label}}`

4. **Detail Template** (`custom/Extension/fields/<fieldtype>/templates/detail.hbs`)
   - Handlebars template for read-only display
   - Renders field value with styling

5. **Module Vardef** (`custom/Extension/modules/<Module>/Ext/Vardefs/<filename>.php`)
   - Defines field with `custom_type` parameter
   - References the custom field type name

6. **Language File** (`custom/Extension/modules/<Module>/Ext/Language/en_us.lang.php`)
   - Language string definitions

---

## Custom Relationships

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/data_framework/relationships/custom_relationships/

### Relationship Types

| Type | Description |
|------|-------------|
| `one-to-many` | One record relates to many records |
| `many-to-many` | Multiple records relate to multiple records |

### Relationship Files

1. **Metadata Definition** (`custom/metadata/<relationship_name>MetaData.php`)
   - Defines relationship structure, fields, and indices

2. **TableDictionary Entry** (`custom/Extension/application/Ext/TableDictionary/<relationship_name>.php`)
   - Includes reference to metadata file

### Metadata Example (Many-to-Many)

```php
<?php
// custom/metadata/accounts_contacts_1MetaData.php

$dictionary["accounts_contacts_1"] = array (
  'true_relationship_type' => 'many-to-many',
  'from_studio' => true,
  'table' => 'accounts_contacts_1_c',
  'relationships' => 
  array (
    'accounts_contacts_1' => 
    array (
      'lhs_module' => 'Accounts',
      'lhs_table' => 'accounts',
      'lhs_key' => 'id',
      'rhs_module' => 'Contacts',
      'rhs_table' => 'contacts',
      'rhs_key' => 'id',
      'relationship_type' => 'many-to-many',
      'join_table' => 'accounts_contacts_1_c',
      'join_key_lhs' => 'accounts_contacts_1accounts_ida',
      'join_key_rhs' => 'accounts_contacts_1contacts_idb',
    ),
  ),
  'fields' => array(...),
  'indices' => array(...),
);
```

### TableDictionary Entry

```php
<?php
// custom/Extension/application/Ext/TableDictionary/accounts_contacts_1.php

include('custom/metadata/accounts_contacts_1MetaData.php');
```

---

## Module Loadable Package (MLP) Structure

### Required Files

```
build/<PackageName>/
├── version                          # Semantic version (1.0.0)
├── pack.php                         # Executable package builder
├── releases/
│   └── .keep                        # Directory marker
└── src/
    └── custom/Extension/            # All customizations here
        ├── modules/
        │   └── <Module>/Ext/
        │       ├── LogicHooks/
        │       ├── Vardefs/
        │       └── Language/
        ├── fields/
        │   └── <fieldtype>/
        │       ├── <fieldtype>.php
        │       ├── <fieldtype>.js
        │       └── templates/
        ├── application/Ext/
        │   └── TableDictionary/
        └── [other extensions]
```

### pack.php Structure

CRITICAL: `pack.php` MUST be executable and:
1. Read version from CLI argument or `version` file
2. Create `releases/` directory if needed
3. Use `ZipArchive` to create zip file
4. Recursively scan `src/` directory
5. Auto-populate `installdefs['copy']` array
6. Generate `manifest.php` dynamically inside zip
7. Exit with success message

### Manifest Requirements

```php
$manifest = array(
    'id' => '<PackageID>',
    'name' => '<Package Label>',
    'description' => '<Description>',
    'version' => '<Version>',
    'author' => 'AI Generated',
    'is_uninstallable' => 'true',  // string, not boolean
    'published_date' => date("Y-m-d H:i:s"),
    'type' => 'module',
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(),
        'regex_matches' => array('(26|25|14)\\..*$'),
    ),
    'acceptable_sugar_flavors' => array('ENT', 'ULT', 'PRO'),
);

$installdefs = array(
    'id' => '<PackageID>',
    'beans' => array(),
    'copy' => array(
        // Auto-populated by pack.php from src/ directory
    ),
);
```

---

## HTTP Requests (ExternalResourceClient)

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/integration/externalresourceclient/

### CRITICAL: Never Use Curl Directly

✅ **DO USE:**
```php
use Sugarcrm\Sugarcrm\Util\ExternalResource\ExternalResourceClient;

$client = new ExternalResourceClient();
$response = $client->request('POST', 'https://api.example.com/webhook', [
    'json' => $payload,
    'headers' => ['Content-Type' => 'application/json'],
    'timeout' => 10,
]);

if ($response->getStatusCode() === 200) {
    $body = $response->getBody()->getContents();
}
```

❌ **NEVER USE:**
- `curl_init()`, `curl_exec()`, etc.
- `file_get_contents()` with stream contexts
- `fopen()` for HTTP requests
- `ExternalResourceClient::getInstance()` (this method does NOT exist)

---

## Language Files

Language files use the `$mod_strings` or `$GLOBALS['app_list_strings']` pattern:

```php
<?php
// custom/Extension/modules/<Module>/Ext/Language/en_us.lang.php

$mod_strings['LBL_CUSTOM_FIELD'] = 'Custom Field Label';
$mod_strings['LBL_CUSTOM_FIELD_HELP'] = 'Help text for the field';

// For dropdown/multiselect options
$GLOBALS['app_list_strings']['custom_list_name'] = array(
    'option1' => 'Option 1 Label',
    'option2' => 'Option 2 Label',
);
```

---

## Module File Structure

### Module Beans (SugarBeans)

Core module class location:
```
./modules/<Module>/<Module>.php  // Do NOT modify
```

Custom module extensions:
```
./custom/Extension/modules/<Module>/Ext/
```

### Key Extension Points

- **Vardefs**: `Ext/Vardefs/`
- **Logic Hooks**: `Ext/LogicHooks/`
- **Language**: `Ext/Language/`
- **Views**: `Ext/Views/`
- **Layouts**: `Ext/Layouts/`
- **Metadata**: `Ext/Metadata/`

---

## Critical Rules

1. **Never override core files** - Always use Extension Framework
2. **Use `_c` suffix** - All custom fields must end with `_c`
3. **Property-by-property vardefs** - Don't use array merge for vardefs
4. **Proper HTTP client** - Only use ExternalResourceClient
5. **Executable pack.php** - Must be shebang executable and use ZipArchive
6. **Dynamic manifests** - Generate manifest inside zip, not hardcoded
7. **Extension paths only** - All files under `custom/Extension/` or `custom/metadata/`
8. **Language files always** - Every customization needs language file
9. **Version control** - Keep `releases/.keep` file
10. **No file conflicts** - Use unique naming to avoid conflicts with other packages


