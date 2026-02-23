<?php
// pack.stub.php — Template for package-local pack.php
// All generated packages must inline a complete, executable pack.php per /AGENTS.md.
// Each package has its own pack.php; no global or shared pack.php is allowed.
// This is a reference template. Replace placeholders with actual package metadata.

#!/usr/bin/env php
<?php

$packageID = "{{PACKAGE_ID}}";
$packageLabel = "{{PACKAGE_LABEL}}";
$supportedVersionRegex = '(26|25|14)\\..*$';
$acceptableSugarFlavors = array('ENT');
$description = '{{PACKAGE_DESCRIPTION}}';
/******************************/

if (empty($argv[1])) {
    if (file_exists("version")) {
        $version = file_get_contents("version");
        $version = trim($version);
    }
} else {
    $version = $argv[1];
}

if (empty($version)){
    die("Usage: {$argv[0]} [version]\n");
}

$id = "{$packageID}-{$version}";

$directory = "releases";
if(!is_dir($directory)){
    mkdir($directory);
}

$zipFile = $directory . "/sugarcrm-{$id}.zip";

if (file_exists($zipFile)) {
    die("Error: Release $zipFile already exists. To generate a new zip, either delete the"
        . " existing zip file or update the version number in the version file, then run this script again.\n");
}

$manifest = array(
    'id' => $packageID,
    'name' => $packageLabel,
    'description' => $description,
    'version' => $version,
    'author' => 'SugarCRM, Inc.',
    'is_uninstallable' => 'true',
    'published_date' => date("Y-m-d H:i:s"),
    'type' => 'module',
    'acceptable_sugar_versions' => array(
        'exact_matches' => array(
        ),
        'regex_matches' => array(
            $supportedVersionRegex,
        ),
    ),
    'acceptable_sugar_flavors' => $acceptableSugarFlavors,
);

$installdefs = array(
    'beans' => array (),
    'id' => $packageID
);

echo "Creating {$zipFile} ... \n";
$zip = new ZipArchive();
$zip->open($zipFile, ZipArchive::CREATE);
$basePath = realpath('src/');
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($basePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);
foreach ($files as $name => $file) {
    if ($file->isFile()) {
        $fileReal = $file->getRealPath();
        $fileRelative = 'src' . str_replace($basePath, '', $fileReal);
        echo " [*] $fileRelative \n";
        $zip->addFile($fileReal, $fileRelative);
        $installdefs['copy'][] = array(
            'from' => '<basepath>/' . $fileRelative,
            'to' => preg_replace('/^src[\/\\\](.*)/', '$1', $fileRelative),
        );
    }
}
$manifestContent = sprintf(
    "<?php\n\$manifest = %s;\n\$installdefs = %s;\n",
    var_export($manifest, true),
    var_export($installdefs, true)
);
$zip->addFromString('manifest.php', $manifestContent);
$zip->close();
echo "Done creating {$zipFile}\n\n";
exit(0);

