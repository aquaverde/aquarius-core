<?php 
/** Execute jobs at specific time intervals
  *
  * "Those who do not understand Unix are condemned to reinvent it, poorly."
  * Let's poorly reinvent Cron :-)
  *
  *  */
class Cron {

    /** Makes the cron hook run on shutdown of the script's execution */
    static function run_on_shutdown() {
        register_shutdown_function(array(new self(), 'run'));
    }

    /** Call the hooks registered under 'daily', but only once a day. */
    function run() {
        global $aquarius;
        $DB = $aquarius->db;

        // Proceed only if there are hooks to be run
        if (isset($aquarius->hooks['daily']) && count($aquarius->hooks['daily']) > 0) {
            $now = getdate();
            $today = mktime(0, 0, 0, $now['mon'], $now['mday'], $now['year']);

            $DB->query('BEGIN');
            try {
                /* The 'cron' table stores the status. The two fields 'start_run' and 'end_run' are timestamps of the last time a cron run started and ended. When this class starts a run, start_run is set to the current time and end_run is set to zero. When the run finishes, end_run is set to the current time. 'end_run' is used to detect incomplete runs that didn't finish. If a run did not set 'end_run' after ten minutes, another run is started. */
                $last_run = $DB->singlequery('SELECT end_run FROM cron WHERE type=\'daily\'');

                $run = $last_run < $today;

                if ($run) {
                    $start_run = $DB->singlequery('SELECT start_run FROM cron WHERE type=\'daily\'');

                    if ($start_run > time() - 600) return false; // Do not execute if it looks like it's already running

                    $DB->query('REPLACE INTO cron SET type=\'daily\', end_run = 0, start_run = '.time()); // We assume that the transaction will be killed if another transaction tries to do the same thing

                    Log::info('Cron: Running daily jobs');
                    $aquarius->execute_hooks('daily');
                    Log::info('Cron: Finished daily jobs');

                    $DB->query('UPDATE cron SET end_run = '.time().' WHERE type=\'daily\'');
                    $DB->query('COMMIT');
                    return true;
                }
            } catch (Exception $e) { Log::fail($e); }

            $DB->query('ROLLBACK');
        }
        return false;
    }
}
