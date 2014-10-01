<?php
use Illuminate\Console\Command;

class UpdateGroups extends Command {

    protected $name = 'bhapp:updategroups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check wrong groups';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->info(Checker::updateMissedGroups());
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
//			array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
//			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}