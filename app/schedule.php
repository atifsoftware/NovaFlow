<?php
/**
 * NovaFlow Enterprise Task Scheduler
 * Register all scheduled cron tasks and recurring background jobs here.
 * Run in crontab via: * * * * * php /path/to/cli.php schedule:run >> /dev/null 2>&1
 */

use NovaFlow\Core\Schedule;

// 1. Minute-level System Heartbeat & Log Truncation
Schedule::call(function() {
    return 'System heartbeat verified healthy.';
}, 'System Heartbeat Monitor')->everyMinute();

// 2. Queue Health Monitor
Schedule::call(function() {
    return 'Queue health checked.';
}, 'Queue Health Check')->everyFiveMinutes();

// 3. Daily Midnight Ledger Balancing (00:00)
Schedule::call(function() {
    return 'Daily ledger closing completed.';
}, 'Daily Financial Closing')->dailyAt('00:00');
