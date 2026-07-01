<?php
// Default subpanel layout for the {{Module}} record view.
// Add subpanel components here (Notes, activities, related modules, etc.)
//
// This file MUST be registered in installdefs['sidecar'], NOT installdefs['copy'].
// See skills/sugar-mlp-anatomy/SKILL.md.

$viewdefs['{{Module}}']['base']['layout']['subpanels'] = [
    'components' => [
        // Example: attach Notes 1:M (requires sugar-notes-attachment setup)
        // [
        //     'layout' => 'subpanel',
        //     'label' => 'LBL_NOTES_SUBPANEL_TITLE',
        //     'context' => ['link' => 'notes'],
        // ],
    ],
    'type' => 'subpanels',
    'span' => 12,
];
