<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TMDB\Data\Movie;
use App\Mopie;

class WithPagination extends Controller
{
    protected $data;

    protected $type;

    public function __construct($data, $type) {
        $this->_data = $data;
        $this->_type = $type;
    }

    public function getPage()
    {
        return $this->_data['page'];
    }

    public function getTotalResults()
    {
        return $this->_data['total_results'];
    }

    public function getTotalPage()
    {
        return $this->_data['total_pages'];
    }

    public function getDateMaximum()
    {
        return $this->_data['dates']['maximum'];
    }

    public function getDateMinimum()
    {
        return $this->_data['dates']['minimum'];
    }

    public function getResults()
    {
        if ($this->_type === 'movie') {
            $movies = [];
            $results = Mopie::blockID($this->_data['results'], config('tmdb.block_movie'));

            foreach ($results as $data) {
                $movies[] = new Movie($data);
            }

            return $movies;
        }elseif($this->_type === 'people'){
            $peoples = [];

            foreach ($this->_data['results'] as $data) {
                $peoples[] = new Person($data);
            }

            return $peoples;
        }else{
            $TVShows = [];
            $results = Mopie::blockID($this->_data['results'], config('tmdb.block_tv'));

            foreach ($results as $data) {
                $TVShows[] = new TVShow($data);
            }

            return $TVShows;
        }
    }
}
