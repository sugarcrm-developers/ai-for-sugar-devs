---
name: sugar-address-grouping
description: Group SugarCRM address sub-fields (street/city/state/postalcode/country) like Quotes — requires BOTH vardef `group`/`group_label` AND a record-view `'type' => 'fieldset'` wrapping the 5 sub-fields. Vardef grouping alone is not enough.
when_to_use:
  - "group billing/shipping address fields like Quotes"
  - "address fieldset on a custom module"
  - "make address sub-fields render as a block"
  - "billing_address / shipping_address pattern"
not_for:
  - Single-field address (just one varchar) — use sugar-custom-field
  - Custom field types beyond addresses — use sugar-custom-field-type
related_skills:
  - sugar-new-module
  - sugar-custom-field
  - sugar-ui-customization
  - sugar-viewdef-editing
---

## When to use this skill

Use this skill when a custom module needs an address that renders as a single visual block — street + city + state + postal code + country grouped together — the way Quotes shows billing/shipping addresses. The pattern is exactly what Quotes uses: see `data/app/sugar/modules/Quotes/clients/base/views/record/record.php:283-362` for the canonical example.

The TWO halves of the pattern are both required:

1. **Vardef side**: each sub-field carries `'group' => '<prefix>_address'` and `'group_label' => 'LBL_<...>'`
2. **Record-view side**: a `'type' => 'fieldset'` block wraps the 5 sub-fields

Vardef grouping ALONE is not enough — without the record-view fieldset, the fields will render as 5 separate rows.

## Concrete example: vardefs side

```php
<?php
// src/SugarModules/modules/Foos/vardefs.php (excerpt)

$dictionary['Foo']['fields']['billing_address_street'] = [
    'name' => 'billing_address_street',
    'vname' => 'LBL_BILLING_ADDRESS_STREET',
    'type' => 'varchar',
    'len' => 150,
    'group' => 'billing_address',
    'group_label' => 'LBL_BILLING_ADDRESS',
];

$dictionary['Foo']['fields']['billing_address_city'] = [
    'name' => 'billing_address_city',
    'vname' => 'LBL_BILLING_ADDRESS_CITY',
    'type' => 'varchar',
    'len' => 100,
    'group' => 'billing_address',
    'group_label' => 'LBL_BILLING_ADDRESS',
];

$dictionary['Foo']['fields']['billing_address_state'] = [
    'name' => 'billing_address_state',
    'vname' => 'LBL_BILLING_ADDRESS_STATE',
    'type' => 'varchar',
    'len' => 100,
    'group' => 'billing_address',
    'group_label' => 'LBL_BILLING_ADDRESS',
];

$dictionary['Foo']['fields']['billing_address_postalcode'] = [
    'name' => 'billing_address_postalcode',
    'vname' => 'LBL_BILLING_ADDRESS_POSTAL_CODE',
    'type' => 'varchar',
    'len' => 20,
    'group' => 'billing_address',
    'group_label' => 'LBL_BILLING_ADDRESS',
];

$dictionary['Foo']['fields']['billing_address_country'] = [
    'name' => 'billing_address_country',
    'vname' => 'LBL_BILLING_ADDRESS_COUNTRY',
    'type' => 'varchar',
    'len' => 100,
    'group' => 'billing_address',
    'group_label' => 'LBL_BILLING_ADDRESS',
];
```

## Concrete example: record view fieldset

```php
<?php
// src/SugarModules/modules/Foos/clients/base/views/record/record.php (excerpt)

$viewdefs['Foos']['base']['view']['record'] = [
    // ... buttons, panel_header ...
    'panels' => [
        [
            'name' => 'panel_body',
            'columns' => 2,
            'fields' => [
                'name',
                'status',
                [
                    'name' => 'billing_address',
                    'type' => 'fieldset',
                    'css_class' => 'address',
                    'label' => 'LBL_BILLING_ADDRESS',
                    'fields' => [
                        [
                            'name' => 'billing_address_street',
                            'css_class' => 'address_street',
                            'placeholder' => 'LBL_BILLING_ADDRESS_STREET',
                        ],
                        [
                            'name' => 'billing_address_city',
                            'css_class' => 'address_city',
                            'placeholder' => 'LBL_BILLING_ADDRESS_CITY',
                        ],
                        [
                            'name' => 'billing_address_state',
                            'css_class' => 'address_state',
                            'placeholder' => 'LBL_BILLING_ADDRESS_STATE',
                        ],
                        [
                            'name' => 'billing_address_postalcode',
                            'css_class' => 'address_zip',
                            'placeholder' => 'LBL_BILLING_ADDRESS_POSTAL_CODE',
                        ],
                        [
                            'name' => 'billing_address_country',
                            'css_class' => 'address_country',
                            'placeholder' => 'LBL_BILLING_ADDRESS_COUNTRY',
                        ],
                    ],
                ],
                // ... other fields
            ],
        ],
    ],
];
```

## Bonus: shipping address with copy-from-billing button

The Quotes pattern also includes a "copy billing to shipping" helper as the final field inside the shipping fieldset:

```php
[
    'name' => 'copy',
    'label' => 'NTC_COPY_BILLING_ADDRESS',
    'type' => 'copy',
    'mapping' => [
        'billing_address_street'     => 'shipping_address_street',
        'billing_address_city'       => 'shipping_address_city',
        'billing_address_state'      => 'shipping_address_state',
        'billing_address_postalcode' => 'shipping_address_postalcode',
        'billing_address_country'    => 'shipping_address_country',
    ],
],
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Address fields render as 5 separate rows | Missing `'type' => 'fieldset'` wrapper in record view | Add the fieldset wrapper (see above) |
| Address renders as fieldset but no group label | `group_label` not set on vardef OR `label` missing on fieldset | Set both |
| Copy button doesn't work | `mapping` key references fields that don't exist | Match field names exactly |
| Studio breaks the fieldset on save | Studio doesn't fully understand fieldsets — edit the file directly | Use `[[sugar-viewdef-editing]]` and revert Studio's changes |

## References

- Canonical example: `data/app/sugar/modules/Quotes/clients/base/views/record/record.php`
- `[[sugar-new-module]]` — broader context for a new module with addresses
- `[[sugar-viewdef-editing]]` — safe edits to Sidecar viewdefs (numeric keys, balanced parens)
- `[[sugar-custom-field]]` — for non-grouped single fields
