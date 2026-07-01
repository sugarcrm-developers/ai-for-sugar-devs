<?php
// pack.stub.php — Reference pack.php for MB-style multi-module MLPs.
//
// Routes files under src/ into the 7 installdefs sections (beans, copy, relationships,
// vardefs, layoutdefs, sidecar, language) plus the special `image_dir` key.
//
// For a SIMPLE single-feature MLP (one logic hook, one custom field) where everything
// goes into installdefs['copy'], use pack.simple.stub.php instead.
//
// Replace the placeholders {{PACKAGE_ID}}, {{PACKAGE_LABEL}}, {{PACKAGE_DESCRIPTION}}.
// See skills/sugar-package-build/SKILL.md for the full narrative.

#!/usr/bin/env php
<?php

$packageID = "{{PACKAGE_ID}}";
$packageLabel = "{{PACKAGE_LABEL}}";
$supportedVersionRegex = '(26|25|14)\\..*$';
$acceptableSugarFlavors = ['ENT', 'ULT', 'PRO'];
$description = '{{PACKAGE_DESCRIPTION}}';
/******************************/

// ---- 1. version ----
$version = $argv[1] ?? (file_exists('version') ? trim(file_get_contents('version')) : null);
if (empty($version)) {
    die("Usage: {$argv[0]} [version]\n");
}

// ---- 2. releases/ ----
if (!is_dir('releases')) {
    mkdir('releases');
}
$zipFile = "releases/sugarcrm-{$packageID}-{$version}.zip";
if (file_exists($zipFile)) {
    die("Error: $zipFile already exists. Bump version or delete file.\n");
}

// ---- 3. $manifest ----
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

// ---- 4. installdefs scaffold ----
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

// ---- 5. scan src/ ----
$basePath = realpath('src');
if ($basePath === false) {
    die("Error: src/ directory not found\n");
}

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

$iconDir = null;
$zipQueue = [];

foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $real = $file->getRealPath();
    $rel = 'src' . str_replace($basePath, '', $real);
    $relAfterSrc = ltrim(substr($rel, 3), '/');

    // 5a. icons → image_dir (do NOT add to copy)
    if (preg_match('#^SugarModules/icons/.+\.(gif|png)$#i', $relAfterSrc)) {
        $iconDir = 'SugarModules/icons';
        continue;
    }

    // 5b. bean class → beans
    if (preg_match('#^SugarModules/modules/([^/]+)/([^/]+)\.php$#', $relAfterSrc, $m)
        && $m[2] !== 'vardefs') {
        $contents = file_get_contents($real);
        if (preg_match('/class\s+(\w+)\s+extends\s+SugarBean/', $contents, $cm)) {
            $installdefs['beans'][] = [
                'module' => $m[1],
                'class' => $cm[1],
                'path' => $rel,
                'tab' => true,
            ];
            $installdefs['copy'][] = ['from' => "<basepath>/$rel", 'to' => $relAfterSrc];
            $zipQueue[] = [$real, $rel];
            continue;
        }
    }

    // 5c. relationship metadata → relationships
    if (preg_match('#^SugarModules/relationships/relationships/(.+)\.php$#', $relAfterSrc)) {
        $installdefs['relationships'][] = ['meta_data' => "<basepath>/$rel"];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // 5d. relationship vardefs → vardefs (per-module)
    if (preg_match('#^SugarModules/relationships/vardefs/.+_([^_]+)\.php$#', $relAfterSrc, $m)) {
        $installdefs['vardefs'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // 5e. Sidecar subpanel layouts → sidecar
    if (preg_match('#clients/base/layouts/subpanels/.+\.php$#', $relAfterSrc)) {
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

    // 5f. layoutdefs (legacy subpaneldefs)
    if (preg_match('#^SugarModules/modules/([^/]+)/metadata/subpaneldefs\.php$#', $relAfterSrc, $m)) {
        $installdefs['layoutdefs'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // 5g. language: application scope
    if (preg_match('#^SugarModules/language/application/(.+)\.lang\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => 'application',
            'language' => $m[1],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }
    // 5h. language: module scope
    if (preg_match('#^SugarModules/modules/([^/]+)/language/(.+)\.lang\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
            'language' => $m[2],
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }
    // 5i. language: relationship language
    if (preg_match('#^SugarModules/relationships/language/([^/]+)/(.+)\.php$#', $relAfterSrc, $m)) {
        $installdefs['language'][] = [
            'from' => "<basepath>/$rel",
            'to_module' => $m[1],
            'language' => 'en_us',
        ];
        $zipQueue[] = [$real, $rel];
        continue;
    }

    // 5j. fall-through → copy
    $installdefs['copy'][] = ['from' => "<basepath>/$rel", 'to' => $relAfterSrc];
    $zipQueue[] = [$real, $rel];
}

// ---- 6. image_dir + icon files ----
if ($iconDir !== null) {
    $installdefs['image_dir'] = "<basepath>/$iconDir";
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

// ---- 7. write zip ----
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
