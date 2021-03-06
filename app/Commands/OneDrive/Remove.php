<?php

namespace App\Commands\OneDrive;

use App\Helpers\OneDrive;
use App\Helpers\Tool;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class Remove extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'rm 
                            {remote? : Remote path}
                            {--id= : ID}
                            {--f|force : Force Delete}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Delete Item';

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $this->call('refresh:token');
        if ($this->option('force')) {
            return $this->delete();
        }
        if ($this->confirm('You can not restore,continue?')) {
            return $this->delete();
        }
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function delete()
    {
        if ($this->option('id')) {
            $id = $this->option('id');
        } else {
            $remote = $this->argument('remote');
            if (!$remote) {
                $this->warn('Parameter Missing!');
                exit;
            }
            $graphPath = Tool::getRequestPath($remote);
            $id_response
                = OneDrive::responseToArray(OneDrive::pathToItemId($graphPath));
            if ($id_response['code'] === 200) {
                $id = $id_response['data']['id'];
            } else {
                $this->warn('Path Abnormal!');
                exit;
            }
        }
        $response = OneDrive::responseToArray(OneDrive::delete($id));
        $this->call('cache:clear');
        $response['code'] === 200 ? $this->info("Deleted!")
            : $this->warn("Failed!\n{$response['msg']} ");
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
