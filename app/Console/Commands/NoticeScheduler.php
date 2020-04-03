<?php

namespace App\Console\Commands;

use App\Helpers\NoticeHelper;
use App\Model\Notice;
use App\Repositories\NoticeRepository;
use Illuminate\Console\Command;

class NoticeScheduler extends Command
{
    private $notice;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xshop:notice-scheduler';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送通知计划任务';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(NoticeRepository $notice)
    {
        parent::__construct();
        $this->notice = $notice;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $notices = $this->notice->getWaitSendNotices(100);
        foreach ($notices as $notice) {
            NoticeHelper::send($notice->id);
        }
    }
}
