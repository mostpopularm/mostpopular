<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TMDB\Data\BaseObject;
use Illuminate\Support\Carbon;
use App\Mopie;

class TVShow extends BaseObject
{
    public function getName()
    {
        return $this->_data['name'];
    }

    public function getYear()
    {
        return Carbon::parse($this->getFirstAirDate())->year;
    }

    public function getLastEpisodeToAir()
    {
        return new Episode($this->_data['last_episode_to_air']);
    }

    public function getFirstAirDate()
    {
        return $this->_data['first_air_date'];
    }

    public function getLastAirDate()
    {
        return $this->_data['last_air_date'];
    }

    public function getEpisodeRunTime()
    {
        return isset($this->_data['episode_run_time'][0]) ? $this->_data['episode_run_time'][0] : 60;
    }

    public function getGenreComma()
    {
        $genres = [];

        foreach ($this->_data['genres'] as $data) {
            $genres[] = $data['name'];
        }

        return implode(', ',$genres);
    }

    public function getOriginalName()
    {
        return $this->_data['original_name'];
    }

    public function getNumSeasons()
    {
        return $this->_data['number_of_seasons'];
    }

    public function getNumEpisodes()
    {
        return $this->_data['number_of_episodes'];
    }

    public function getBackdrop()
    {
        return $this->_data['backdrop_path'];
    }

    public function getOverview()
    {
        return $this->_data['overview'];
    }

    public function getInProduction()
    {
        return $this->_data['in_production'];
    }

    public function getSeasons()
    {
        $seasons = [];

        foreach ($this->_data['seasons'] as $data) {
            $seasons[] = new Season($data);
        }

        return $seasons;
    }

    public function getRecommendations()
    {
        $recommendations = [];
        $results = Mopie::blockID($this->_data['recommendations']['results'], config('tmdb.block_tv'));

        foreach ($results as $data) {
            $recommendations[] = new TVShow($data);
        }

        return $recommendations;
    }

    public function getSimilars()
    {
        $similars = [];
        $results = Mopie::blockID($this->_data['similar']['results'], config('tmdb.block_tv'));

        foreach ($results as $data) {
            $similars[] = new TVShow($data);
        }

        return $similars;
    }

    public function getRandomBackdrop()
    {
        $backdrop = collect($this->_data['images']['backdrops'])->shuffle()->first();

        return new Image($backdrop);
    }

    public function getKeywords()
    {
        $keywords = array();

        foreach ($this->_data['keywords']['results'] as $data) {
            $keywords[] = new Keyword($data);
        }

        return $keywords;
    }

    public function getJSON()
    {
        return json_encode($this->_data, JSON_PRETTY_PRINT);
    }
}
