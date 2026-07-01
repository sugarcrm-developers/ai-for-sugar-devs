---
name: sugar-package-build
description: Generate full pack.php for a SugarCRM MLP — scans src/ with glob/RecursiveDirectoryIterator, routes files to the 7 installdefs sections (beans/copy/relationships/vardefs/layoutdefs/sidecar/language) + image_dir, generates manifest.php dynamically.
when_to_use:
  - "generate pack.php"
  - "create the zip builder for a Sugar MLP"
  - "build full installdefs from src/"
  - "MLP zip with all 7 installdefs sections"
  - "MB-style package builder"
not_for:
  - The Sugar customization PHP itself — use the topic skill
related_skills:
  - sugar-mlp-anatomy
  - sugar-new-module
  - sugar-relationship
  - sugar-module-icons
  - sugar-application-language
  - sugar-feature-generator
---

## When to use this skill

Use this skill any time a package needs its own `pack.php`. For trivial packages (1-2 files of customization), a simple pack.php with everything in `installdefs['copy']` is enough — see `templates/minimal_mlp/pack.simple.stub.php`. For full multi-module packages with new modules, relationships, icons, application-scope language, etc., use the 7-section glob-based scanner shown below (and templated in `templates/minimal_mlp/pack.stub.php`).

EVERY MLP in this repo includes a self-contained pack.php. No shared/global pack.php — each package owns its build script.

For the conceptual reference on what each section is for, see `[[sugar-mlp-anatomy]]`.

## Required behavior

A correct pack.php:

1. Reads version from `$argv[1]` or `version` file
2. Creates `releases/` if missing
3. Refuses to overwrite an existing zip
4. Recursively scans `src/`
5. Routes each file to the correct installdefs section
6. Sets `installdefs['image_dir']` to the icons directory if present
7. Emits `manifest.php` inside the zip with `$manifest` + `$installdefs`
8. Uses ZipArchive (not file_get_contents)
9. Exits 0 with a success message

## Concrete example: full 7-section pack.php

```php
#!/usr/bin/env php
<?php

$packageID = "Acme_FoosPackage";
$packageLabel = "Acme: Foos Package";
$supportedVersionRegex = '(26|25|14)\\..*$';
$acceptableSugarFlavors = ['ENT', 'ULT', 'PRO'];
$description = 'Acme Foos package — adds the Foos module and links it to Accounts.';

// ---- version ----
$version = $argv[1] ?? (file_exists('version') ? trim(file_get_contents('version')) : null);
if (empty($version)) {
    die("Usage: {$argv[0]} [version]\n");
}

// ---- releases dir ----
if (!is_dir('releases')) {
    mkdir('releases');
}
$zipFile = "releases/sugarcrm-{$packageID}-{$version}.zip";
if (file_exists($zipFile)) {
    die("Error: $zipFile exists. Bump version or delete the file.\n");
}

// ---- manifest ----
$manifest = [
    'id' => $packageID,
    'name' => $packageLabel,
    'description' => $description,
    'version' => $version,
    'author' => 'SugarCRM, Inc.',
    'is_uninstallable' => 'true',
    'published_date' => date('Y-m-d H:i:s'),
    'type' => 'module',
    'acceptable_sugar_versions' => [
        'exact_matches' => [],
        'regex_matches' => [$supportedVersionRegex],
    ],
    'acceptable_sugar_flavors' => $acceptableSugarFlavors,
];

// ---- installdefs scaffold (the 7 sections + id + image_dir) ----
$installdefs = [
    'id' => $packageID,
    'beans' => [],
    'copy' => [],
    'relationships' => [],
    'vardefs' => [],
    'layoutdefs' => [],
    'sidecar' => [],
    'language' => [],
];

// ---- scan src/ ----
$basePath = realpath('src');
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$iconDir = null;

foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $real = $file->getRealPath();
    $rel = 'src' . str_replace($basePath, '', $real);
    $relAfterSrc = ltrim(substr($rel, 3), '/');

    // ---- 1. icons → image_dir ----
    if (preg_match('#^SugarModules/icons/.+\.(gif|png)$#i', $relAfterSrc)) {
        $iconDir = 'SugarModules/icons';
        continue;  // do NOT add to copy; image_dir handles it
    }

    // ---- 2. bean class → beans ----
    if (preg_match('#^SugarModules/modules/([^/]+)/([^/]+)\.php$#', $relAfterSrc, $m)
        && $m[2] !== 'vardefs') {
        // we treat the file at modules/<Module>/<File>.php where <File> is a SINGULAR bean class
        // detect by reading the file for "extends SugarBean"
        $contents = file_get_contents($real);
        if (preg_match('/class\s+(\w+)\s+extends\s+SugarBean/', $contents, $cm)) {
            $installdefs['beans'][] = [
                'module' => $m[1],
                'class' => $cm[1],
                'path' => $rel,
                'tab' => true,
            ];
            // also copy the file
            $installdefs['copy'][] = ['from' => "<basepath>/$rel", 'to' => $relAfterSrc];
            $zipQueue[] = [$real, $rel];
            continue;
        }
    }

    // ---- 3. relationship metadata → relationships ----
    if (preg_match('#^SugarModules/relationships/relationships/(.+)\.php$#', $relAfterSrc, $m)) {
        $installdefs['relationships'][] = ['meta_data' => "<basepath>/$rel"];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // ---- 4. relationship vardefs (also count as vardefs for the targeted module) ----
    if (preg_match('#^SugarModules/relationships/vardefs/.+_([^_]+)\.php$#', $relAfterSrc, $m)) {
        $installdefs['vardefs'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // ---- 5. Sidecar subpanel layouts → sidecar ----
    if (preg_match('#clients/base/layouts/subpanels/.+\.php$#', $relAfterSrc)) {
        // infer to_module from path
        if (preg_match('#^SugarModules/relationships/.+_([^_]+)\.php$#', $relAfterSrc, $m)) {
            $toModule = $m[1];
        } elseif (preg_match('#^SugarModules/modules/([^/]+)/#', $relAfterSrc, $m)) {
            $toModule = $m[1];
        } else {
            $toModule = 'application';
        }
        $installdefs['sidecar'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $toModule,
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // ---- 6. layoutdefs (legacy subpaneldefs for non-Sidecar) ----
    if (preg_match('#^SugarModules/modules/([^/]+)/metadata/subpaneldefs\.php$#', $relAfterSrc, $m)) {
        $installdefs['layoutdefs'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // ---- 7. language ----
    if (preg_match('#^SugarModules/language/application/(.+)\.lang\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => 'application',
            'language' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }
    if (preg_match('#^SugarModules/modules/([^/]+)/language/(.+)\.lang\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
            'language' => $m[2],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }
    if (preg_match('#^SugarModules/relationships/language/([^/]+)/(.+)\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
            'language' => 'en_us',
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // ---- 8. fall-through: copy ----
    $installdefs['copy'][] = ['from' => "<basepath>/$rel", 'to' => $relAfterSrc];
    $zipQueue[] = [$real, $rel];
}

// finalize image_dir
if ($iconDir !== null) {
    $installdefs['image_dir'] = "<basepath>/$iconDir";
    // icons not in copy queue — Sugar copies the directory contents
    // BUT we must add the actual icon files to the zip too:
    $icons = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator("$basePath/$iconDir", RecursiveDirectoryIterator::SKIP_DOTS)
    );
    foreach ($icons as $iconFile) {
        if (!$iconFile->isFile()) continue;
        $iconReal = $iconFile->getRealPath();
        $iconRel = 'src' . str_replace($basePath, '', $iconReal);
        $zipQueue[] = [$iconReal, $iconRel];
    }
}

// ---- write zip ----
echo "Creating $zipFile ...\n";
$zip = new ZipArchive();
$zip->open($zipFile, ZipArchive::CREATE);

foreach ($zipQueue as [$real, $rel]) {
    echo " [*] $rel\n";
    $zip->addFile($real, $rel);
}

$manifestContent = sprintf(
    "<?php\n\$manifest = %s;\n\$installdefs = %s;\n",
    var_export($manifest, true),
    var_export($installdefs, true)
);
$zip->addFromString('manifest.php', $manifestContent);
$zip->close();

echo "Done: $zipFile\n";
exit(0);
```

## Lint discipline

After writing pack.php, lint it. If no host PHP:

```bash
# host PHP (preferred)
php -l pack.php

# fallback: docker
docker run --rm -v "$PWD":/app -w /app php:8.1-cli php -l pack.php
```

CI: also run a syntax check on every generated PHP file in `src/`:

```bash
find src -name '*.php' -exec php -l {} +
```

## When to use the simpler version

For a single-feature MLP (one logic hook, one custom field) where everything goes to `installdefs['copy']` and there are no new modules / relationships / icons / application-scope language, use `templates/minimal_mlp/pack.simple.stub.php` instead. Faster to read, less to go wrong.

## Common gotchas

| Symptom | Root cause | Fix |
|---------|------------|-----|
| Module installs but no icons | `image_dir` missing or in `$manifest` | Set `installdefs['image_dir']` (the routing code above does it) |
| Subpanels missing | Sidecar layouts ended up in `installdefs['copy']` | Use the sidecar branch (rule 5 above) |
| Sidebar entry missing | application-scope language not registered with `to_module=>'application'` | Use the language branch for application/ files (rule 7 above) |
| Bean not registered | Bean class not detected (no `extends SugarBean`) | Confirm class name matches Escalation pattern + file path |
| `<basepath>` literal in installed files | Sugar didn't substitute — caused by wrong installdefs format | Use the array form `['from' => '<basepath>/...', 'to' => '...']` |
| Pack.php parse error on first run | Syntax error in generated file | Lint with `php -l` (see above) |

## References

- `[[sugar-mlp-anatomy]]` — the 7 installdefs sections + file-pattern routing rules
- `[[sugar-module-icons]]` — image_dir specifics
- `[[sugar-application-language]]` — application-scope language registration
- `[[sugar-relationship]]` — what relationship files look like (input to this scanner)
- `[[sugar-new-module]]` — what bean/vardefs/metadata files look like
- `templates/minimal_mlp/pack.stub.php` — Tier 3 enhanced template
- `templates/minimal_mlp/pack.simple.stub.php` — Tier 3 simpler template
