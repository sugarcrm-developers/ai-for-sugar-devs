<?php
// SINGULAR class name, PLURAL module_dir/module_name/table_name.
// Escalation pattern: data/app/sugar/modules/Escalations/Escalation.php

class {{Bean}} extends SugarBean
{
    public $object_name = '{{Bean}}';
    public $table_name = '{{module_table}}';
    public $module_dir = '{{Module}}';
    public $module_name = '{{Module}}';
    public $importable = true;

    // standard fields covered by `uses => ['basic', 'assignable', 'team_security']` in vardefs.php
    public $id;
    public $name;
    public $description;
    public $date_entered;
    public $date_modified;
    public $modified_user_id;
    public $created_by;
    public $deleted;
    public $assigned_user_id;
    public $assigned_user_name;
    public $team_id;
    public $team_name;
    public $team_set_id;

    // custom fields — add here
    public $status;
}
