# installdefs Cheatsheet

One-page reference for the 7 installdefs sections plus the special `image_dir` key.

Companion skill: [`skills/sugar-mlp-anatomy/SKILL.md`](../skills/sugar-mlp-anatomy/SKILL.md).

## The 7 sections

| Section | Purpose | Schema |
|---------|---------|--------|
| `beans` | Register new bean classes for new modules | `['module' => 'Foos', 'class' => 'Foo', 'path' => '<basepath>/.../Foo.php', 'tab' => true]` |
| `copy` | Generic file copy (catch-all) | `['from' => '<basepath>/.../file.php', 'to' => 'custom/.../file.php']` |
| `relationships` | Register MB-style relationships | `['meta_data' => '<basepath>/.../<rel>.php', 'module_vardefs' => ['<Module>' => '<basepath>/...']]` |
| `vardefs` | Register vardef extensions per module | `['from' => '<basepath>/.../<rel>.php', 'to_module' => '<Module>']` |
| `layoutdefs` | Register legacy subpanel layouts (non-Sidecar) | `['from' => '<basepath>/.../subpaneldefs.php', 'to_module' => '<Module>']` |
| `sidecar` | Register Sidecar `clients/base/layouts/subpanels/` | `['from' => '<basepath>/.../subpanel-X.php', 'to_module' => '<Module>']` |
| `language` | Register language strings per module + application | `['from' => '<basepath>/.../<lang>.lang.php', 'to_module' => 'application' OR '<Module>', 'language' => 'en_us']` |

Plus the special string key:

| Key | Purpose | Value |
|-----|---------|-------|
| `image_dir` | Sugar copies the directory contents to `custom/themes/default/images/` | `'<basepath>/SugarModules/icons'` (string, not array) |

`image_dir` is the #1 mistake source — see [`skills/sugar-module-icons/SKILL.md`](../skills/sugar-module-icons/SKILL.md). It MUST be inside `$installdefs`, NEVER `$manifest`. Cite `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120`.

## File-pattern → section routing

When pack.php walks `src/`:

| Path pattern | Section |
|--------------|---------|
| `SugarModules/modules/<Module>/<Bean>.php` (`extends SugarBean`) | `beans` (+ also goes to `copy`) |
| `SugarModules/modules/<Module>/vardefs.php` | `copy` (the bean autoloads it) |
| `SugarModules/modules/<Module>/metadata/subpaneldefs.php` | `layoutdefs` |
| `SugarModules/modules/<Module>/clients/base/layouts/subpanels/*.php` | `sidecar` |
| `SugarModules/modules/<Module>/clients/base/views/*/*.php` | `copy` |
| `SugarModules/modules/<Module>/clients/base/menus/*/*.php` | `copy` |
| `SugarModules/modules/<Module>/clients/base/filters/*/*.php` | `copy` |
| `SugarModules/modules/<Module>/language/<lang>.lang.php` | `language` (`to_module=<Module>`) |
| `SugarModules/modules/<Module>/dashboards/*/*.php` | `copy` |
| `SugarModules/relationships/relationships/<rel>.php` | `relationships['meta_data']` |
| `SugarModules/relationships/vardefs/<file>_<Module>.php` | `vardefs` (`to_module=<Module>`) |
| `SugarModules/relationships/clients/base/layouts/subpanels/<rel>_<Module>.php` | `sidecar` (`to_module=<Module>`) |
| `SugarModules/relationships/language/<Module>/<rel>.php` | `language` (`to_module=<Module>`, `language=en_us`) |
| `SugarModules/language/application/<lang>.lang.php` | `language` (`to_module=application`) |
| `SugarModules/icons/*.{gif,png}` | (NOT copy) → `installdefs['image_dir']` |
| `custom/Extension/modules/<Module>/Ext/...` | `copy` |
| `custom/clients/base/...` | `copy` |
| `custom/metadata/<rel>MetaData.php` | `copy` (referenced by TableDictionary entry) |
| `custom/Extension/application/Ext/TableDictionary/<rel>.php` | `copy` |

## Manifest vs installdefs decision

| Belongs in `$manifest` | Belongs in `$installdefs` |
|------------------------|----------------------------|
| `id`, `name`, `description` | `id` (also duplicated here for legacy reasons) |
| `version`, `published_date`, `author` | `beans`, `copy`, `relationships`, `vardefs`, `layoutdefs`, `sidecar`, `language` |
| `type` (`module`) | `image_dir` (STRING) |
| `is_uninstallable` (string `'true'`) | `hookdefs` (for logic hooks) |
| `acceptable_sugar_versions` (with `regex_matches`) | `pre_execute`, `post_execute` (custom install scripts) |
| `acceptable_sugar_flavors` (`['ENT','ULT','PRO']`) | |

## Common section combinations

| Package type | Sections actually used |
|--------------|------------------------|
| Single logic hook | `copy`, `hookdefs` |
| Single custom field on OOB module | `copy` (language and vardef both copy under Ext/) |
| Custom relationship between OOB modules | `copy` (metadata + TableDictionary entry) |
| New module (no relationships) | `beans`, `copy`, `language` (application + module), `sidecar` (subpanel default), `image_dir` |
| New module + relationships | `beans`, `copy`, `relationships`, `vardefs`, `sidecar`, `language`, `image_dir` |
| Notes 1:M attachment to custom module | `copy` (the link vardef), `sidecar` (the subpanel), `language` (application for parent_type_display) |

## See also

- [`skills/sugar-mlp-anatomy/SKILL.md`](../skills/sugar-mlp-anatomy/SKILL.md) — narrative version of this table
- [`skills/sugar-package-build/SKILL.md`](../skills/sugar-package-build/SKILL.md) — pack.php that implements this routing
- [`reference/module_anatomy.md`](module_anatomy.md) — file-by-file walkthrough of a full module
- [`reference/common_gotchas.md`](common_gotchas.md) — symptom → root cause table
- `data/app/sugar/ModuleInstall/ModuleInstaller.php` — Sugar core that reads installdefs
