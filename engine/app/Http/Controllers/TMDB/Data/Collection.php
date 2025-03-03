<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Collection extends Controller
{
    private $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function getName()
    {
        return $this->_data['name'];
    }

    public function getID()
    {
        return $this->_data['id'];
    }

    public function getOverview()
    {
        return $this->_data['overview'];
    }

    public function getPoster()
    {
        return $this->_data['poster_path'];
    }

    public function getBackdrop()
    {
        return $this->_data['backdrop_path'];
    }

    public function getMovies()
    {
        $movies = array();
        foreach($this->_data['parts'] as $data){
            $movies[] = new Movie($data);
        }
        return $movies;
    }

    public function get($item = '')
    {
        return (empty($item)) ? $this->_data : $this->_data[$item];
    }
}
