<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Export extends Command
{
    protected $signature = 'export';

    protected $description = '';

    public function __construct()
    {
        parent::__construct();
    }

    public function prepare()
    {
        $this->comment("
 __  __  ____  _____ _____ ______   ____
|  \/  |/ __ \|  __ \_   _|  ____| |___ \
| \  / | |  | | |__) || | | |__      __) |
| |\/| | |  | |  ___/ | | |  __|    |__ <
| |  | | |__| | |    _| |_| |____   ___) |
|_|  |_|\____/|_|   |_____|______| |____/
        ");
        $this->comment('=== By Nano Scripts ===');
        $this->comment('*** www.nanoscripts.com ***');
        $this->comment('============================');
        $this->comment('============================');
    }

    public function handle()
    {
        $this->info('Export');
    }
}
