---
name: sugar-mlp-anatomy
description: Anatomy of a SugarCRM Module Loadable Package — the 7 installdefs sections (beans, copy, relationships, vardefs, layoutdefs, sidecar, language) and the file-pattern → section routing rules used by pack.php and ModuleInstaller.
when_to_use:
  - "what goes in installdefs"
  - "how is a Sugar MLP structured"
  - "which installdefs section does this file belong in"
  - "pack.php routing rules"
  - "MLP layout overview"
not_for:
  - Generating actual pack.php — use sugar-package-build
  - File-by-file walkthrough of one module — use reference/module_anatomy.md
related_skills:
  - sugar-package-build
  - sugar-new-module
  - sugar-module-icons
  - sugar-application-language
---

## When to use this skill

Use this skill to understand the high-level shape of a Sugar MLP: what's in `manifest`, what's in `installdefs`, the 7 installdefs sections, and how to route files in `src/` to the right section. This is the conceptual scaffolding that `[[sugar-package-build]]` operationalizes in `pack.php`.

## The two top-level arrays

Every MLP zip contains a `manifest.php` at the root with TWO arrays:

- **`$manifest`** — metadata about the package (id, name, version, author, supported Sugar versions/flavors, etc.). Sugar's ModuleInstaller reads this to decide IF a package can install.
- **`$installdefs`** — instructions for WHAT to install. This is where Sugar reads file destinations, bean registrations, sidecar layouts, language registrations, etc.

CRITICAL distinction:
- `image_dir` belongs in **`$installdefs`**, NOT `$manifest` — see `[[sugar-module-icons]]`. Sugar's `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120` reads `image_dir` only from `installdefs`.

## The 7 installdefs sections

| Section | Purpose | Typical files |
|---------|---------|---------------|
| `beans` | Register new bean classes for new modules | `Bean.php`, `module_name` mapping |
| `copy` | Generic file copy (catch-all) | most `custom/Extension/...` files |
| `relationships` | Register MB-style relationships | per-relationship metadata + vardefs |
| `vardefs` | Register vardef extensions per module | `<module>_extension.php` |
| `layoutdefs` | Register subpanel layouts (legacy non-Sidecar) | `<module>_subpanels/*.php` |
| `sidecar` | Register Sidecar `clients/base/layouts/subpanels/` | `subpanel-for-<lhs>.php` |
| `language` | Register language strings per module + application | `<module>_language/<lang>.lang.php`, `application/<lang>.lang.php` |

Plus the special `image_dir` key inside `installdefs` (not a section, just a string).

## File-pattern → section routing

When `pack.php` walks `src/`, it routes each file based on its path:

| Path pattern | installdefs section |
|--------------|---------------------|
| `SugarModules/modules/<Module>/<Module>.php` (the bean class) | `beans` |
| `SugarModules/relationships/<rel>/<rel>MetaData.php` | `relationships['meta_data']` |
| `SugarModules/relationships/relationships/<rel>.php` | `relationships['module_vardefs']` (extension format) |
| `SugarModules/relationships/vardefs/<lhs>_<rel>.php` | `relationships['module_vardefs']` |
| `SugarModules/relationships/language/<Module>/<rel>.php` | `language` |
| `SugarModules/relationships/clients/base/layouts/subpanels/...` | `sidecar` |
| `SugarModules/language/application/<lang>.lang.php` | `language` (to_module=>`application`) |
| `SugarModules/language/<Module>/<lang>.lang.php` | `language` (to_module=>`<Module>`) |
| `SugarModules/icons/...` (gif, png) | `image_dir` — set installdefs `image_dir` to the directory; ModuleInstaller copies all files |
| everything else under `SugarModules/` or `custom/` | `copy` |

## Concrete pack.php skeleton (excerpt)

```php
$installdefs = [
    'id' => $packageID,
    'beans' => [],
    'copy' => [],
    'relationships' => [],
    'vardefs' => [],
    'layoutdefs' => [],
    'sidecar' => [],
    'language' => [],
    'image_dir' => '<basepath>/icons',  // ONLY here, never in $manifest
];

foreach ($files as $file) {
    $rel = relativePath($file);
    if (preg_match('#^SugarModules/modules/([^/]+)/\1\.php$#', $rel, $m)) {
        $installdefs['beans'][] = [
            'module' => $m[1],
            'class' => $m[1],
            'path' => $rel,
            'tab' => true,
        ];
    } elseif (preg_match('#^SugarModules/relationships/.+/(.+)MetaData\.php$#', $rel, $m)) {
        $installdefs['relationships'][] = ['meta_data' => $rel];
    } elseif (preg_match('#^SugarModules/relationships/clients/base/layouts/subpanels/(.+)\.php$#', $rel, $m)) {
        $installdefs['sidecar'][] = [
            'from' => $rel,
            'to_module' => extractModuleFromSidecarPath($rel),
        ];
    } elseif (preg_match('#^SugarModules/language/application/(.+)\.lang\.php$#', $rel, $m)) {
        $installdefs['language'][] = [
            'from' => $rel,
            'to_module' => 'application',
            'language' => $m[1],
        ];
    } elseif (preg_match('#^SugarModules/language/([^/]+)/(.+)\.lang\.php$#', $rel, $m)) {
        $installdefs['language'][] = [
            'from' => $rel,
            'to_module' => $m[1],
            'language' => $m[2],
        ];
    } else {
        $installdefs['copy'][] = ['from' => $rel, 'to' => $rel];
    }
}
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| "No Image" placeholder on new module sidebar | `image_dir` in `$manifest`, not `$installdefs` | Move it. See `ModuleInstaller.php:1120` |
| Sidebar entry missing | `moduleList` not registered in application-scope language | Use `[[sugar-application-language]]` |
| Subpanel missing | Sidecar layout in `copy` instead of `sidecar` | Move to `installdefs['sidecar']` |
| Language string showing as `LBL_*` | `to_module` mismatch — module-scope label registered as application or vice versa | Match `to_module` to actual lookup scope |
| Duplicate-index error at install | Manually declared an index that the `basic` template or `is_sync_key=true` auto-creates | Remove the manual declaration |

## References

- `[[sugar-package-build]]` — pack.php that implements this routing
- `[[sugar-module-icons]]` — image_dir details
- `[[sugar-application-language]]` — application-scope language registration
- `[[sugar-new-module]]` — full MB-style module that exercises all 7 sections
- `reference/installdefs_cheatsheet.md` — quick reference table
- `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120` — Sugar core that reads image_dir
