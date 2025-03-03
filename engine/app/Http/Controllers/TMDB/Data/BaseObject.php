<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mopie;

class BaseObject extends Controller
{
    protected $_data;

    public function __construct($data) {
        $this->_data = $data;
    }

    public function getID() {
        return $this->_data['id'];
    }

    public function getPoster() {
        return $this->_data['poster_path'];
    }

    public function getVoteAverage() {
        return $this->_data['vote_average'];
    }

    public function getVoteCount() {
        return $this->_data['vote_count'];
    }

    public function get($item = ''){
        if(empty($item)){
            return $this->_data;
        }
        if(array_key_exists($item, $this->_data)){
            return $this->_data[$item];
        }
        return null;
    }

    public function getCast(){
        return $this->getCredits('cast');
    }

    public function getCrew(){
        return $this->getCredits('crew');
    }

    protected function getCredits($key){
        $persons = [];

        foreach ($this->_data['credits'][$key] as $data) {
            $persons[] = new Person($data);
        }

        return $persons;
    }

    public function getImageBackdrops()
    {
        return $this->getImages('backdrops');
    }

    public function getImagePosters()
    {
        return $this->getImages('posters');
    }

    protected function getImages($key)
    {
        $posters = array();

        foreach ($this->_data['images'][$key] as $data) {
            $posters[] = new Image($data);
        }

        return $posters;
    }

    public function getStarComma($count = 5)
    {
        $stars = [];

        foreach (collect($this->_data['credits']['cast'])->take($count) as $data) {
            $stars[] = '<a href="'.Mopie::route('people.single', ['id' => $data['id']]).'" title="'.$data['name'].'">'.$data['name'].'</a>';
        }

        return implode(', ',$stars);
    }

    public function getDirectorComma($count = 5)
    {
        $directors = [];

        foreach (collect($this->_data['credits']['crew'])->take($count) as $data) {
            $directors[] = '<a href="'.Mopie::route('people.single', ['id' => $data['id']]).'" title="'.$data['name'].'">'.$data['name'].'</a>';
        }

        return implode(', ',$directors);
    }
}
