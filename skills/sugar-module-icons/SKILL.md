---
name: sugar-module-icons
description: Register SugarCRM module icons via `installdefs['image_dir']` (NOT $manifest). Sugar copies 4 files per module (gif + 3 png sizes) to custom/themes/. Misplacing image_dir in $manifest is the #1 cause of "No Image" placeholder.
when_to_use:
  - "add icons to my new module"
  - "module icon missing in sidebar"
  - "No Image placeholder on module"
  - "where does image_dir go?"
not_for:
  - Editing icons themselves (PNG/GIF design)
  - Customizing theme CSS
related_skills:
  - sugar-new-module
  - sugar-mlp-anatomy
  - sugar-package-build
---

## When to use this skill

Use this skill whenever a new SugarCRM module needs icons. The placement of `image_dir` is the most common source of sidebar/menu icon failures — it MUST go in `$installdefs`, never in `$manifest`. See `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120`, which only reads `image_dir` from `installdefs`.

## The 4 icon files per module

Sugar expects 4 files per module:

| File | Purpose | Typical size |
|------|---------|--------------|
| `<Module>.gif` | Legacy icon (used in some classic views) | 16x16 |
| `icon_<Module>_32.png` | Module dropdown/menu icon | 32x32 |
| `icon_<Module>_64.png` | Larger menu icon | 64x64 |
| `icon_<Module>_128.png` | Sidebar / module header icon | 128x128 |

Place them under `src/SugarModules/icons/` (or any subdirectory you point `image_dir` at):

```
src/SugarModules/icons/
├── Foos.gif
├── icon_Foos_32.png
├── icon_Foos_64.png
└── icon_Foos_128.png
```

For multiple modules in one package, drop all icons into the same `icons/` directory — Sugar's ModuleInstaller copies the whole dir into `custom/themes/default/images/`.

## Concrete example: installdefs configuration

```php
<?php
$installdefs = [
    'id' => 'Acme_FoosPackage',
    // image_dir is a STRING pointing to the directory holding the icon files
    'image_dir' => '<basepath>/SugarModules/icons',
    'copy' => [
        // ... your other file copies
    ],
    // ... other sections
];
```

Where `<basepath>` is Sugar's placeholder for the extracted package root inside the zip — at install time Sugar replaces it.

## The mistake — and why it fails silently

```php
// WRONG — image_dir in $manifest is ignored
$manifest = [
    'id' => 'Acme_FoosPackage',
    'image_dir' => '<basepath>/SugarModules/icons',  // <-- IGNORED
    ...
];
```

`ModuleInstaller.php:1120` reads `$installdefs['image_dir']` only. The manifest does not have an `image_dir` reader. There's no error and no warning — the install succeeds, your icons are inside the zip, but Sugar never copies them to `custom/themes/`. Result: "No Image" placeholder forever.

## Concrete example: pack.php snippet for icons

```php
<?php
// inside pack.php, after scanning $files
$installdefs['image_dir'] = '<basepath>/SugarModules/icons';

// Do NOT also add the icons to installdefs['copy'] — image_dir handles them.
foreach ($files as $file) {
    $rel = relativeFromSrc($file);
    if (str_starts_with($rel, 'SugarModules/icons/')) {
        continue;  // skip; image_dir handles these
    }
    $installdefs['copy'][] = ['from' => "<basepath>/$rel", 'to' => $rel];
}
```

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| "No Image" in sidebar | `image_dir` in `$manifest` | Move it to `$installdefs`. ModuleInstaller.php:1120 |
| Icons in `custom/Extension/themes/...` after install but module shows default | Icons routed via `copy` instead of `image_dir` | Use `image_dir`; don't also `copy` |
| Icon shows in dropdown but missing in sidebar | Missing `icon_<Module>_128.png` (sidebar uses the 128 size) | Add all 4 sizes |
| Module not in sidebar at all | Different issue — `moduleList` not registered application-scope | See `[[sugar-application-language]]` |

## References

- `[[sugar-mlp-anatomy]]` — the 7 installdefs sections + image_dir
- `[[sugar-application-language]]` — application-scope `moduleIconList`
- `[[sugar-new-module]]` — the bigger picture
- `[[sugar-package-build]]` — pack.php
- `data/app/sugar/ModuleInstall/ModuleInstaller.php:1120` — the line that reads image_dir
