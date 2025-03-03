<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\TMDB\TMDB;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Frontend;
use Illuminate\Support\Arr;

class SitemapController extends Controller
{
    public function index()
    {
        return response()->view('sitemaps.index')->header('Content-Type', 'text/xml');
    }

    public function moviePopular()
    {
        $items = $this->getMovies('popular');
        return response()->view('sitemaps.movie', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function movieTopRated()
    {
        $items = $this->getMovies('top_rated');
        return response()->view('sitemaps.movie', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function movieUpcoming()
    {
        $items = $this->getMovies('upcoming');
        return response()->view('sitemaps.movie', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function movieNowPlaying()
    {
        $items = $this->getMovies('now_playing');
        return response()->view('sitemaps.movie', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function tvPopular()
    {
        $items = $this->getTVShows('tv-popular', 'popular');
        return response()->view('sitemaps.tv', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function tvTopRated()
    {
        $items = $this->getTVShows('tv-top-rated', 'top_rated');
        return response()->view('sitemaps.tv', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function tvOnTheAir()
    {
        $items = $this->getTVShows('tv-on-the-air', 'on_the_air');
        return response()->view('sitemaps.tv', compact('items'))->header('Content-Type', 'text/xml');
    }

    public function tvAiringToday()
    {
        $items = $this->getTVShows('tv-airing_today', 'airing_today');
        return response()->view('sitemaps.tv', compact('items'))->header('Content-Type', 'text/xml');
    }

    private function getMovies($type = '')
    {
        $frontend = new Frontend();
        $tmdb = new TMDB;

        $movies = [];
        for ($i=1; $i <= config('tmdb.max_limit_page_sitemap'); $i++) {
            $movies[] = Cache::remember( $type.'-'.$i.$frontend->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $type) {
                return $tmdb->getMovies($type);
            });
        }

        $datas = [];
        foreach ($movies as  $data) {
            $datas[] = $data->getResults();
        }

        return Arr::collapse($datas);
    }

    private function getTVShows($keyCache = '', $type = '')
    {
        $frontend = new Frontend();
        $tmdb = new TMDB;

        $movies = [];
        for ($i=1; $i <= config('tmdb.max_limit_page_sitemap'); $i++) {
            if ($frontend->isCache()) {
                $movies[] = Cache::remember( $keyCache.'-'.$i.$frontend->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $type) {
                    return $tmdb->getTVShows($type);
                });
            }else{
                $movies[] = $tmdb->getTVShows($type, $i);
            }
        }

        $datas = [];
        foreach ($movies as  $data) {
            $datas[] = $data->getResults();
        }

        return Arr::collapse($datas);
    }
}
