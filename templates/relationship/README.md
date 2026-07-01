# templates/relationship/

5-file MB-style relationship template. Companion skill: [`skills/sugar-relationship/SKILL.md`](../../skills/sugar-relationship/SKILL.md).

## Placeholders

| Token | Meaning | Example |
|-------|---------|---------|
| `{{rel}}` | Relationship name (lowercase, e.g., `accounts_foos_1`) | `accounts_foos_1` |
| `{{lhs_module}}` | LHS module name (PARENT side, what's linked to) | `Accounts` |
| `{{lhs_module_lower}}` | LHS module name lowercase (used in id_name pattern) | `accounts` |
| `{{lhs_table}}` | LHS DB table | `accounts` |
| `{{lhs_bean}}` | LHS bean class (singular) | `Account` |
| `{{rhs_module}}` | RHS module name (CHILD side, has the link/relate/id triplet) | `Foos` |
| `{{rhs_module_lower}}` | RHS module name lowercase | `foos` |
| `{{rhs_table}}` | RHS DB table | `foos` |
| `{{rhs_bean}}` | RHS bean class (singular) | `Foo` |
| `{{rel_upper}}` | Uppercase relationship name for LBL_ keys | `ACCOUNTS_FOOS_1` |

Convention:
- **lhs = parent** (the target of the relate)
- **rhs = child** (has the link/relate/id field triplet)
- `id_name = <rel><lhs_lower>_ida` — generated from `{{rel}}` + `{{lhs_module_lower}}` + `_ida`

## File count

5 files (the 6th — Sidecar subpanel layouts — is created on a per-module basis under the module's own `clients/base/layouts/subpanels/` directory and is not relationship-specific):

1. `relationships/{{rel}}.php` — relationship metadata
2. `vardefs/{{rhs_module_lower}}_{{rel}}_{{lhs_module}}.php` — link vardef on LHS (parent)
3. `vardefs/{{rhs_module_lower}}_{{rel}}_{{rhs_module}}.php` — link/relate/id triplet on RHS (child)
4. `language/{{lhs_module}}/{{rel}}.php` — LHS module labels
5. `language/{{rhs_module}}/{{rel}}.php` — RHS module labels

## See also

- [`skills/sugar-relationship/SKILL.md`](../../skills/sugar-relationship/SKILL.md) — full conceptual reference
- [`skills/sugar-mlp-anatomy/SKILL.md`](../../skills/sugar-mlp-anatomy/SKILL.md) — how to wire into installdefs
