---
name: sugar-relationship
description: Create a SugarCRM module-to-module relationship (1:M or M:M) in MB-style — uses M:M-with-join-table convention even for declared 1:M (Notes is the exception). lhs=parent, rhs=child. 7 files per relationship.
when_to_use:
  - "add a relationship between Accounts and Contacts"
  - "link two modules many-to-many"
  - "create a 1:M relationship"
  - "join table for custom relationship"
  - "Orders has multiple Accounts (BillTo / ShipTo)"
not_for:
  - Notes attachments — Notes is the only documented exception (use sugar-notes-attachment)
  - Relate field to a single record — use sugar-custom-field
related_skills:
  - sugar-notes-attachment
  - sugar-new-module
  - sugar-package-build
  - sugar-mlp-anatomy
---

## When to use this skill

Use this skill when linking two SugarCRM modules (custom or OOB) via a 1:M or M:M relationship in the MB-style layout. Module Builder always emits the same shape: a join table plus link vardefs plus subpanel layouts, even when you declare a 1:M. The only exception is Notes 1:M, which uses `parent_id`/`parent_type` — see `[[sugar-notes-attachment]]`.

## Conventions

- **lhs = parent** (the side being linked TO — Accounts in "Contact's Account")
- **rhs = child** (the side that holds the link/relate/id triplet)
- `id_name` on the relate field follows the pattern `<rel><lhs_lower>_ida`
- Multi-instance relationships (same lhs+rhs pair more than once, e.g., Orders relate to Accounts as both BillTo and ShipTo) require a unique `<rel>` per instance and role-label-per-instance in the language file
- Custom relationship names should NOT end with `_c` — table names do; relationship names are different

## Decision step BEFORE generating files: subpanels per side

For every relationship, decide explicitly: **does each side want a subpanel showing the related records on its detail view?**

- **Parent side (lhs) — almost always YES.** If you're looking at a parent record (Account, Company, Order), users expect to see a list of its children (Contacts, Products, OrderLines). Without a subpanel, the relate field is installable but invisible — the related records have no UI surface anywhere.
- **Child side (rhs) — usually NO.** The child already shows its parent via the relate-display field on the record view. A subpanel listing "the one parent" is redundant.

The decision drives whether you ship the 3 subpanel files for that side:
1. `relationships/layoutdefs/<rel>_<Module>.php` (legacy subpanel registration)
2. `clients/base/layouts/subpanels/<rel>_<Module>.php` (Sidecar — modern UI)
3. `clients/mobile/layouts/subpanels/<rel>_<Module>.php` (mobile counterpart)

Skipping ANY of those three on a side that needs a subpanel = no subpanel shows up. Legacy layoutdef alone isn't enough — Sugar's modern Sidecar UI needs the `clients/base/...` file too. See `[[sugar-mlp-anatomy]]` for which `installdefs` section each routes to.

When batch-generating relationships (e.g., from a Module Builder export, or via a generator script), run this checklist for each new relationship before moving on:

- [ ] Is the parent (lhs) a record users will browse to view related children? → Ship subpanel files on lhs.
- [ ] Does the child (rhs) need a back-reference list? Rarely. → Default: skip lhs-of-the-reverse subpanel.
- [ ] If multi-instance (Orders↔Accounts BillTo/ShipTo): each instance needs its own set of subpanel files with distinct role labels — see `[[project-erp-role-label-map]]` patterns.

## The 7 files per relationship (MB-style)

For a relationship named `accounts_foos_1` linking Accounts (lhs, parent) ↔ Foos (rhs, child):

```
src/SugarModules/relationships/
├── relationships/
│   └── accounts_foos_1.php                                # relationship metadata
├── vardefs/
│   ├── foos_accounts_foos_1_Accounts.php                  # link vardef on lhs (Accounts)
│   └── foos_accounts_foos_1_Foos.php                      # link/relate/id vardef on rhs (Foos)
├── clients/base/layouts/subpanels/
│   ├── accounts_foos_1_Accounts.php                       # subpanel layout on lhs
│   └── accounts_foos_1_Foos.php                           # subpanel layout on rhs
├── wirelesslayoutdefs/                                    # optional mobile layout
│   └── accounts_foos_1.php
└── language/
    ├── Accounts/
    │   └── accounts_foos_1.php
    └── Foos/
        └── accounts_foos_1.php
```

## Concrete example: relationship metadata (M:M-with-join-table, even for declared 1:M)

```php
<?php
// src/SugarModules/relationships/relationships/accounts_foos_1.php

$relationships['accounts_foos_1'] = [
    'lhs_module' => 'Accounts',
    'lhs_table' => 'accounts',
    'lhs_key' => 'id',
    'rhs_module' => 'Foos',
    'rhs_table' => 'foos',
    'rhs_key' => 'id',
    'relationship_type' => 'many-to-many',          // MB writes M:M even for declared 1:M
    'join_table' => 'accounts_foos_1_c',
    'join_key_lhs' => 'accounts_foos_1accounts_ida',  // <rel><lhs_lower>_ida
    'join_key_rhs' => 'accounts_foos_1foos_idb',      // <rel><rhs_lower>_idb
    'true_relationship_type' => 'one-to-many',        // declares the conceptual intent
];
```

Why M:M for a 1:M? Because MB's runtime relies on the join table for relationship_role_column support (multi-instance relationships), and for consistent subpanel queries. The `true_relationship_type` field tells Sugar to enforce 1:M cardinality at the UI level.

## Concrete example: link vardef on lhs (parent)

```php
<?php
// src/SugarModules/relationships/vardefs/foos_accounts_foos_1_Accounts.php

$dictionary['Account']['fields']['accounts_foos_1'] = [
    'name' => 'accounts_foos_1',
    'type' => 'link',
    'relationship' => 'accounts_foos_1',
    'source' => 'non-db',
    'module' => 'Foos',
    'bean_name' => 'Foo',
    'vname' => 'LBL_ACCOUNTS_FOOS_1_FROM_FOOS_TITLE',
];
```

## Concrete example: link/relate/id triplet on rhs (child)

```php
<?php
// src/SugarModules/relationships/vardefs/foos_accounts_foos_1_Foos.php

// link field
$dictionary['Foo']['fields']['accounts_foos_1'] = [
    'name' => 'accounts_foos_1',
    'type' => 'link',
    'relationship' => 'accounts_foos_1',
    'source' => 'non-db',
    'module' => 'Accounts',
    'bean_name' => 'Account',
    'vname' => 'LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE',
    'side' => 'right',
];

// relate field — displays the account name on Foo's record view
$dictionary['Foo']['fields']['accounts_foos_1_name'] = [
    'name' => 'accounts_foos_1_name',
    'type' => 'relate',
    'source' => 'non-db',
    'vname' => 'LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE',
    'save' => true,
    'id_name' => 'accounts_foos_1accounts_ida',
    'link' => 'accounts_foos_1',
    'table' => 'accounts',
    'module' => 'Accounts',
    'rname' => 'name',
];

// id field — the FK
$dictionary['Foo']['fields']['accounts_foos_1accounts_ida'] = [
    'name' => 'accounts_foos_1accounts_ida',
    'type' => 'link',
    'relationship' => 'accounts_foos_1',
    'source' => 'non-db',
    'reportable' => false,
    'side' => 'right',
    'vname' => 'LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE_ID',
];
```

Note: `id_name` follows `<rel><lhs_lower>_ida`. The lhs is `accounts` (lowercase module name) → `accounts_foos_1accounts_ida`.

## Concrete example: Sidecar subpanel layout on lhs

```php
<?php
// src/SugarModules/relationships/clients/base/layouts/subpanels/accounts_foos_1_Accounts.php

$viewdefs['Accounts']['base']['layout']['subpanels']['components'][] = [
    'layout' => 'subpanel',
    'label' => 'LBL_ACCOUNTS_FOOS_1_FROM_FOOS_TITLE',
    'context' => [
        'link' => 'accounts_foos_1',
    ],
];
```

This MUST register in `installdefs['sidecar']`, NOT `installdefs['copy']`. See `[[sugar-mlp-anatomy]]`.

## Concrete example: language

```php
<?php
// src/SugarModules/relationships/language/Accounts/accounts_foos_1.php

$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_FOOS_TITLE'] = 'Foos';
$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE'] = 'Account';
$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE_ID'] = 'Account (ID)';
```

```php
<?php
// src/SugarModules/relationships/language/Foos/accounts_foos_1.php

$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE'] = 'Account';
$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_FOOS_TITLE'] = 'Foos';
$mod_strings['LBL_ACCOUNTS_FOOS_1_FROM_ACCOUNTS_TITLE_ID'] = 'Account (ID)';
```

## Multi-instance relationships (Orders → Accounts as BillTo + ShipTo)

When the same lhs+rhs pair appears more than once, give each instance its own relationship name and role label:

```php
// instance 1: BillTo
$relationships['accounts_orders_billto'] = [...];

// instance 2: ShipTo
$relationships['accounts_orders_shipto'] = [...];
```

And in the language files, distinct labels per instance:

```php
$mod_strings['LBL_ACCOUNTS_ORDERS_BILLTO_FROM_ACCOUNTS_TITLE'] = 'Bill-To Account';
$mod_strings['LBL_ACCOUNTS_ORDERS_SHIPTO_FROM_ACCOUNTS_TITLE'] = 'Ship-To Account';
```

If both instances were to share the same relationship name, Sugar would collapse them into a single subpanel — the role labels separate them visually and in queries.

## When to use M:M vs Notes-style 1:M

| Use case | Pattern |
|----------|---------|
| Foos → many Bars (no need for role discriminator) | M:M-with-join-table (declared 1:M acceptable) |
| Foos → many Notes (where Note.parent_type is the discriminator) | Notes-style — see `[[sugar-notes-attachment]]` |
| Orders → Accounts as multiple roles (BillTo, ShipTo) | M:M-with-join-table, separate relationship per role |
| Two custom modules linked once | M:M-with-join-table (or use a relate field if 1:1 lookup is enough) |

## installdefs registration (pack.php excerpt)

```php
$installdefs['relationships'][] = [
    'meta_data' => '<basepath>/SugarModules/relationships/relationships/accounts_foos_1.php',
    'module_vardefs' => [
        'Accounts' => '<basepath>/SugarModules/relationships/vardefs/foos_accounts_foos_1_Accounts.php',
        'Foos'     => '<basepath>/SugarModules/relationships/vardefs/foos_accounts_foos_1_Foos.php',
    ],
];

// Sidecar subpanels register in `sidecar`, NOT `copy`
$installdefs['sidecar'][] = [
    'from' => '<basepath>/SugarModules/relationships/clients/base/layouts/subpanels/accounts_foos_1_Accounts.php',
    'to_module' => 'Accounts',
];
$installdefs['sidecar'][] = [
    'from' => '<basepath>/SugarModules/relationships/clients/base/layouts/subpanels/accounts_foos_1_Foos.php',
    'to_module' => 'Foos',
];

// Language entries
$installdefs['language'][] = [
    'from' => '<basepath>/SugarModules/relationships/language/Accounts/accounts_foos_1.php',
    'to_module' => 'Accounts',
    'language' => 'en_us',
];
$installdefs['language'][] = [
    'from' => '<basepath>/SugarModules/relationships/language/Foos/accounts_foos_1.php',
    'to_module' => 'Foos',
    'language' => 'en_us',
];
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Subpanel completely absent on lhs (parent) | Skipped the 3 subpanel files for this relationship | Generate `layoutdefs/<rel>_<Lhs>.php` + `clients/base/layouts/subpanels/<rel>_<Lhs>.php` + `clients/mobile/layouts/subpanels/<rel>_<Lhs>.php`. Easy to miss when batch-generating relationships — always run the "decision step" checklist above. |
| Subpanel registered but doesn't render in modern UI | Only legacy `layoutdef` written, Sidecar component file missing | Add `clients/base/layouts/subpanels/<rel>_<Module>.php` and register in `installdefs['sidecar']` |
| Subpanel in legacy view but not Sidecar | Sidecar layout in `installdefs['copy']` instead of `installdefs['sidecar']` | Re-register under `installdefs['sidecar']` |
| Relate field empty / no name | `id_name` doesn't match the actual id field name | Verify `<rel><lhs_lower>_ida` pattern |
| Both subpanels show same label | Multi-instance role labels not differentiated | One relationship name per role + distinct language label |
| Cannot save (FK constraint) | Wrong `relationship_role_column_value` when used | Match exactly the parent module name (PLURAL) |
| Relate field links Foos to wrong module | lhs/rhs swapped — parent must be lhs | Re-orient |

## References

- `[[sugar-notes-attachment]]` — the documented exception (parent_id/parent_type)
- `[[sugar-new-module]]` — context when both modules are custom
- `[[sugar-mlp-anatomy]]` — installdefs section routing (sidecar vs copy is the trap)
- `[[sugar-package-build]]` — pack.php that wires the installdefs
- `templates/relationship/` — 5-file relationship template (Tier 3)
