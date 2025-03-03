<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Export;
use Illuminate\Support\Facades\Storage;

class ExportBlogspotTV extends Export
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:blogspot-tv
                            {sort : Available "popular, top_rated, airing_today, on_the_air"}
                            {page=1 : Will generate up to the page}
                            {lang=en : Use language code for ex. en, fr, it, es}
                            {backdate? : Specify the date of posting}';

    /**
     * The console Generating TV Shows xml for Blogspot.
     *
     * @var string
     */
    protected $description = 'Generating TV Shows xml for Blogspot';

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
    public function handle()
    {
        if (array_key_exists($this->argument('lang'), config('app.locales'))) {
            app()->setLocale($this->argument('lang'));
        }

        $this->prepare();
        $this->info('Generating TV Shows xml for Blogspot');

        $data = $this->arguments();
        $view = view('exports.templates.blogspot-tv', compact('data'))->render();
        Storage::disk('exports')->put('blogspot-tv.xml', $view);
    }
}
