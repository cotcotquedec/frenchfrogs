<?php namespace Frenchfrogs\App\Console;

use Illuminate\Console\Scheduling\Schedule as SchedulingSchedule;
use FrenchFrogs\Models\Db\Schedule\Schedule;
use FrenchFrogs\Models\Db\Schedule\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Class SchedulerKernel
 *
 *
 * @property \Illuminate\Foundation\Application $app
 *
 *
 * @method  \Illuminate\Console\Application getArtisan()
 * @method  void  reportException() reportException(\Exception $e)
 * @method  void  renderException() renderException($output, \Exception $e)
 *
 *
 * @package FrenchFrogs\Scheduler\Console
 */
trait SchedulerKernel
{


    /**
     * True if schedule is active
     *
     * @var bool
     */
    protected $has_schedule;


    /**
     * Return TRU if schedule module is active
     *
     * @return bool
     */
    public function hasSchedule()
    {
        return !is_null($this->has_schedule) ? $this->has_schedule : $this->has_schedule = env('FRENCHFROG_SCHEDULER_ENABLED', true);
    }

    /**
     * @var Log
     */
    protected $log;

    /**
     * Getter for $log
     *
     * @return \FrenchFrogs\Models\Db\Schedule\Log
     */
    public function getLog()
    {
        return $this->log;
    }

    /**
     * Return TRUE if $log is set
     *
     * @return bool
     */
    public function hasLog()
    {
        return isset($this->log);
    }


    /**
     * Setter for $log
     *
     * @param \FrenchFrogs\Models\Db\Schedule\Log $log
     * @return $this
     */
    public function setLog(Log $log)
    {
        $this->log = $log;
        return $this;
    }

    /**
     * Define the application's command schedule.
     *
     * @param SchedulingSchedule $schedule
     * @return void
     */
    protected function schedule(SchedulingSchedule $schedule)
    {
        if ($this->hasSchedule()) {
            // Add command to scheduler
            foreach (Schedule::active()->get() as $model) {

                /**@var Schedule $model */
                $command = $schedule->command($model->command)->cron($model->schedule);

                if (!$model->can_overlapping) {
                    $command->withoutOverlapping();
                }
            }
        }
    }

    /**
     * Run the console application.
     *
     * @param \Symfony\Component\Console\Input\InputInterface  $input
     * @param \Symfony\Component\Console\Output\OutputInterface  $output
     * @return int
     */
    public function handleSchedule($input, $output = null)
    {
        try {
            $this->bootstrap();

            $command = $input->getFirstArgument();

            if (!is_null($command)) {
                // create log
                $log = Log::create([
                    'command' => $input->getFirstArgument(),
                    'options' => empty($options = $input->getOptions()) ? null : json_encode($options),
                    'arguments' => empty($arguments = $input->getArguments()) ? null : json_encode($arguments)
                ]);

                $this->setLog($log);
            }

            return $this->getArtisan()->run($input, $output);

        } catch (\Exception $e) {
           $this->getLog()->message = $e->getMessage();
            $this->reportException($e);
            $this->renderException($output, $e);
            return 1;
        }
    }

    /**
     * Terminate the application.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  int  $status
     * @return void
     */
    public function terminateSchedule($input, $status)
    {
        if ($this->hasLog()) {
            $this->getLog()->finish(!$status)->save();
        }
        $this->app->terminate();
    }
}
