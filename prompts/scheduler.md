# Scheduler Job Generator Prompt for Sugar MLP Generator

You are an AI agent generating installable, upgrade-safe SugarCRM Module Loadable Packages (MLPs) for scheduled jobs from structured feature requests.

## Instructions (Default)
- Generate the entire package atomically and autonomously, with no user review, file-by-file output, or stepwise confirmation.
- All required files, directories, and content must be created and validated before finishing.
- Output must be a single, deterministic, raw file entry list as specified below.
- No explanations, markdown, or user prompts.

## Scheduled Job Implementation (Extension Framework)

**REFERENCE**: https://support.sugarcrm.com/documentation/sugar_developer/sugar_developer_guide_25.1/architecture/job_queue/
**REFERENCE**: `/reference/master_reference.md`

### Required Files Per Scheduled Job Package

1. **Scheduled Job Definition File** (`custom/Extension/modules/Schedulers/Ext/ScheduledTasks/<filename>.php`)
   - Register job in `$job_strings` array with metadata
   - Define: job name, description, file path, class/function to execute
   - Set schedule (cron format or specific timing)
   - Specify job status (active/inactive)

2. **Job Implementation Class** (`custom/modules/Schedulers/<ClassName>.php`)
   - Create class with static method(s) that execute the job logic
   - Method signature: `public static function jobName($job)`
   - Receives Job object with access to job metadata and scheduling info
   - Should return boolean for success/failure
   - Must handle errors gracefully (don't throw exceptions)

3. **pack.php** (executable package builder)
   - Read version from command-line argument or `version` file
   - Create releases/ directory if it doesn't exist
   - Build zip file with dynamic manifest and file copying
   - Auto-populate installdefs['copy'] by scanning src/ directory recursively
   - Generate manifest.php inside zip with proper metadata

4. **version** file
   - Single line containing semantic version (e.g., 1.0.0)

5. **releases/.keep** file
   - Empty marker file to preserve directory in version control

### Cron Schedule Format

```
┌───────────── minute (0 - 59)
│ ┌───────────── hour (0 - 23)
│ │ ┌───────────── day of month (1 - 31)
│ │ │ ┌───────────── month (1 - 12)
│ │ │ │ ┌───────────── day of week (0 - 6) (0 = Sunday)
│ │ │ │ │
│ │ │ │ │
* * * * *
```

### Common Schedules

| Schedule | Meaning |
|----------|---------|
| `* * * * *` | Every minute |
| `0 * * * *` | Every hour (at :00) |
| `0 0 * * *` | Daily (at midnight) |
| `0 2 * * *` | Daily at 2 AM |
| `0 0 * * 0` | Weekly (Sunday midnight) |
| `0 0 1 * *` | Monthly (1st of month) |
| `*/15 * * * *` | Every 15 minutes |
| `0 */6 * * *` | Every 6 hours |

### Scheduled Job Definition (Extension Framework)

```php
<?php
// custom/Extension/modules/Schedulers/Ext/ScheduledTasks/custom_scheduler.php

$job_strings[] = array(
    'id' => 'custom_scheduler_task',
    'name' => 'Custom Scheduled Task',
    'description' => 'Executes custom business logic on a schedule',
    'url' => 'index.php?entryPoint=Scheduler&job=custom_scheduler_task',
    'url_args' => '',
    'class' => 'CustomScheduler',
    'job_function' => 'executeTask',
    'file' => 'custom/modules/Schedulers/CustomScheduler.php',
    'active' => 1,
    'date_time_start' => '',
    'time_interval' => '0 * * * *',  // Cron format: every hour
    'job_interval' => 3600,  // Interval in seconds
    'catch_up' => 1,  // Execute missed jobs
    'is_tutorial' => 0,
);
```

### Job Implementation Class

```php
<?php
// custom/modules/Schedulers/CustomScheduler.php

class CustomScheduler
{
    /**
     * Execute the scheduled job
     * @param Job $job Job object containing job metadata
     * @return bool Success or failure
     */
    public static function executeTask($job)
    {
        global $GLOBALS;
        
        try {
            // Log job execution
            $GLOBALS['log']->info('Starting custom scheduled task');
            
            // Your job logic here
            // Example: Process records, send emails, cleanup data, etc.
            $records = self::getRecordsToProcess();
            foreach ($records as $record) {
                self::processRecord($record);
            }
            
            // Update job with result
            $job->update_progress(100, 'Completed successfully');
            
            $GLOBALS['log']->info('Custom scheduled task completed successfully');
            return true;
        } catch (Exception $e) {
            // Log error
            $GLOBALS['log']->error('Custom scheduled task failed: ' . $e->getMessage());
            $job->update_progress(0, 'Failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get records that need processing
     * @return array
     */
    private static function getRecordsToProcess()
    {
        // Example: Get all active records needing processing
        return array();
    }

    /**
     * Process individual record
     * @param array $record
     */
    private static function processRecord($record)
    {
        // Implement processing logic
    }
}
```

### Job Object Methods

The `$job` object passed to job functions has these methods:

- `$job->update_progress(int $progress, string $message)` - Update job progress (0-100%)
- `$job->update_schedule_time()` - Update next execution time
- `$job->get_param($name)` - Get job parameter
- `$job->set_param($name, $value)` - Set job parameter

### Manifest Configuration

```php
$installdefs = array(
    'copy' => array(
        array(
            'from' => '<basepath>/Files/custom/Extension/modules/Schedulers/Ext/ScheduledTasks/<filename>.php',
            'to' => 'custom/Extension/modules/Schedulers/Ext/ScheduledTasks/<filename>.php',
        ),
        array(
            'from' => '<basepath>/Files/custom/modules/Schedulers/<ClassName>.php',
            'to' => 'custom/modules/Schedulers/<ClassName>.php',
        ),
    ),
);
```

### Important Considerations

- **Error Handling**: Always use try/catch, return boolean (don't throw exceptions)
- **Logging**: Log start, completion, and errors using `$GLOBALS['log']`
- **Progress Updates**: Call `update_progress()` for long-running jobs
- **Performance**: Keep jobs efficient, avoid blocking operations
- **Frequency**: Consider system load when setting job frequency
- **Dependencies**: Ensure required modules/classes are loaded before execution
- **Idempotency**: Jobs may run multiple times, implement accordingly

### Output Format
- The first line of output must begin with: `File: build/<PackageName>/`
- Each file must be prefixed with: `File: build/<PackageName>/<path>`
- No markdown, explanations, or commentary.
- No stray whitespace or user prompts.

### Prohibited Actions
- Never throw exceptions from job methods (use try/catch and return false)
- Never override core scheduler files
- Never use non-Extension Framework paths for job definitions
- Never forget to include job definition in Ext/ScheduledTasks/
- Never use global variables without checking if they exist
- Never implement long-blocking operations (jobs should complete quickly)
- Never place job class directly in custom/ without proper structure

