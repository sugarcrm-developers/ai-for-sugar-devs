---
name: sugar-viewdef-editing
description: Safely edit existing SugarCRM Sidecar viewdefs (record.php, list.php, subpanel-list.php) — uses explicit numeric array keys that MUST be renumbered when inserting/removing fields. Use a paren-balanced parser, not non-greedy regex.
when_to_use:
  - "edit a Sidecar viewdef"
  - "insert a field into record.php"
  - "renumber viewdef array keys"
  - "fix a corrupted viewdef"
  - "Sidecar metadata editing safely"
not_for:
  - Creating a brand-new viewdef — use sugar-ui-customization or sugar-new-module
  - Studio-driven changes — let Studio re-emit the file
related_skills:
  - sugar-ui-customization
  - sugar-new-module
  - sugar-mb-export-flow
  - sugar-address-grouping
---

## When to use this skill

Use this skill any time you need to edit an existing Sidecar viewdef PHP file in place — `record.php`, `list.php`, `subpanel-list.php`, `selection-list.php`, etc. These files use EXPLICIT numeric array keys that MUST stay contiguous and sorted. Sugar's PHP parser will accept gaps, but downstream consumers (Studio, the cache builder) sometimes silently drop entries after the first gap.

DO NOT use non-greedy regex for surgery on these files. A nested array structure with multiple `[...]`-style entries will fool a `.+?` regex into matching across the wrong boundary. Use a paren-balanced parser (read PHP token-by-token) or restore from a Module Builder sample.

## Concrete example: the structure

```php
<?php
// excerpt
$viewdefs['Foos']['base']['view']['record'] = [
    'panels' => [
        0 => [
            'name' => 'panel_header',
            'fields' => [
                0 => 'name',
                1 => 'status',
            ],
        ],
        1 => [
            'name' => 'panel_body',
            'fields' => [
                0 => 'priority',
                1 => 'assigned_user_name',
                2 => 'team_name',
            ],
        ],
    ],
];
```

If you insert a field at position 1 in `panel_body.fields`, you MUST shift the trailing entries:

```php
'fields' => [
    0 => 'priority',
    1 => 'new_field_c',   // INSERTED
    2 => 'assigned_user_name',  // was 1
    3 => 'team_name',           // was 2
],
```

## Safe editing approach

### Option A: rewrite via PHP

Read the file with `include`, mutate the array, write back with `var_export`:

```php
<?php
$file = 'src/SugarModules/modules/Foos/clients/base/views/record/record.php';
include $file;
$record = &$viewdefs['Foos']['base']['view']['record'];

// insert new_field_c into panel_body (the second panel)
$panel = &$record['panels'][1];
$panel['fields'] = array_values(array_merge(
    array_slice($panel['fields'], 0, 1),
    ['new_field_c'],
    array_slice($panel['fields'], 1)
));

file_put_contents(
    $file,
    "<?php\n\$viewdefs = " . var_export($viewdefs, true) . ";\n"
);
```

`array_values()` is the renumbering trick — it forces 0-based contiguous keys.

### Option B: paren-balanced surgery

If you must edit textually (e.g., to preserve comments), tokenize:

```php
<?php
$tokens = token_get_all(file_get_contents($file));
// walk tokens, track [ ] / ( ) / { } depth, locate the panel by name string,
// then insert/remove array entries at known depth
```

NEVER use `preg_replace('/fields.*\]/', ...)` — `.+?` and `.*?` will cross boundaries unpredictably on nested arrays.

### Option C: restore from MB sample

If the file is already corrupted, the safest recovery is:

1. Generate a fresh export from Module Builder for the same module
2. Diff against the broken file to find what you wanted to keep
3. Hand-apply your changes to the fresh export

## Why this matters

Sugar's metadata pipeline:
- Reads the PHP file
- Caches a merged view per module
- Studio overlays its own changes

When numeric keys are non-sequential (e.g., `0, 1, 3` skipping 2), some legacy paths in Sugar interpret the array as associative and break sort order. Even when the in-memory array is fine, var_export'ing the cached version can drop entries.

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Field disappears from view after install | Numeric keys non-contiguous after edit | Renumber with `array_values()` (Option A above) |
| `var_export` produces an array with letter-prefixed keys | Sequence had gaps | Same fix |
| File has stray `,` before `)` | Manual editing left a trailing comma at array end | Acceptable in PHP 7.3+ — but other tools may complain |
| Studio overwrites your edit | Studio re-emits the file on save | Make the change post-Studio or via `Ext/Metadata/` extension |
| Address fieldset broken after edit | Edited the field array but not the fieldset wrapper | See `[[sugar-address-grouping]]` |

## References

- `[[sugar-ui-customization]]` — for greenfield viewdef creation
- `[[sugar-new-module]]` — reference structure of a fresh viewdef
- `[[sugar-mb-export-flow]]` — regenerate from MB if corrupted
- `[[sugar-address-grouping]]` — fieldset-specific edits
