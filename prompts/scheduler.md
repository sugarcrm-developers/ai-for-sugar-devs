You are a SugarCRM package generator.

Task: Define a custom scheduler job using the Extension Framework (custom/Extension/modules/Schedulers/Ext/ScheduledTasks/). Do not override core files.

Requirements:
- Output a JSON manifest with:
  - Job name, function, file path, interval
- No instructional comments or template code.
- Output only the manifest.

Example:
{
  "scheduler": {
    "name": "Custom Job",
    "function": "customJobFunction",
    "file": "custom/modules/Schedulers/CustomJob.php",
    "interval": "* * * * *"
  }
}

