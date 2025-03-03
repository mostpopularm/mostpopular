<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Export;
use Illuminate\Support\Facades\Storage;

class ExportBlogspotMovie extends Export
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'export:blogspot-movie
                            {sort : Available "popular, upcoming, top_rated, now_playing"}
                            {page=1 : Will generate up to the page}
                            {lang=en : Use language code for ex. en, fr, it, es}
                            {backdate? : Specify the date of posting}';

    /**
     * The console Generating TV Shows xml for Blogspot.
     *
     * @var string
     */
    protected $description = 'Generating Movies xml for Blogspot';

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
        $this->info('Generating Movies xml for Blogspot');

        $data = $this->arguments();
        $view = view('exports.templates.blogspot-movie', compact('data'))->render();
        Storage::disk('exports')->put('blogspot-movie.xml', $view);
    }
}
