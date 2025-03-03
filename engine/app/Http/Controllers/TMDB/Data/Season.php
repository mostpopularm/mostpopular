<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;

class Season extends Controller
{
    private $_data;

    public function __construct($data, $idTVShow = 0)
    {
        $this->_data = $data;
        $this->_data['tvshow_id'] = $idTVShow;
    }

    public function getID()
    {
        return $this->_data['id'];
    }

    public function getOverview()
    {
        return $this->_data['overview'];
    }

    public function getYear()
    {
        return Carbon::parse($this->getAirDate())->year;
    }

    public function getName()
    {
        return $this->_data['name'];
    }

    public function getTVShowID()
    {
        return $this->_data['tvshow_id'];
    }

    public function getSeasonNumber()
    {
        return $this->_data['season_number'];
    }

    public function getNumEpisodes()
    {
        return count($this->_data['episodes']);
    }

    public function getEpisode($numEpisode)
    {
        return new Episode($this->_data['episodes'][$numEpisode]);
    }

    public function getEpisodes()
    {
        $episodes = array();
        foreach($this->_data['episodes'] as $data){
            $episodes[] = new Episode($data, $this->getTVShowID());
        }
        return $episodes;
    }

    public function getPoster()
    {
        return $this->_data['poster_path'];
    }

    public function getAirDate()
    {
        return $this->_data['air_date'];
    }

    public function get($item = '')
    {
        return (empty($item)) ? $this->_data : $this->_data[$item];
    }

    public function getJSON()
    {
        return json_encode($this->_data, JSON_PRETTY_PRINT);
    }
}
