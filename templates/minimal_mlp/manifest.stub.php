<?php
// manifest.stub.php — minimal manifest template for Sugar MLP
// Do not include instructional comments in generated outputs.

$manifest = array(
    'id' => '<package_id>',
    'name' => '<package_name>',
    'description' => '<description>',
    'version' => '<version>',
    'author' => '<author>',
    'is_uninstallable' => true,
    'published_date' => date('Y-m-d H:i:s'),
    'type' => 'module',
    'acceptable_sugar_versions' => array(
        'regex_matches' => array('10\..*$'),
    ),
    'acceptable_sugar_flavors' => array('ENT','PRO','ULT'),
);

$installdefs = array(
    'id' => '<package_id>',
    // ...
);

