<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mopie;

class Person extends Controller
{
    private $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function getID()
    {
        return $this->_data['id'];
    }

    public function getBiography()
    {
        return $this->_data['biography'];
    }

    public function getBirthday()
    {
        return $this->_data['birthday'];
    }

    public function getKnownForDepartment()
    {
        return $this->_data['known_for_department'];
    }

    public function getCharacter()
    {
        return $this->_data['character'];
    }

    public function getGender()
    {
        return $this->_data['gender'];
    }

    public function getName()
    {
        return $this->_data['name'];
    }

    public function getProfilePath()
    {
        return $this->_data['profile_path'];
    }

    public function getPopularity()
    {
        return $this->_data['popularity'];
    }

    public function getPlaceOfBirth()
    {
        return $this->_data['place_of_birth'];
    }

    public function getAlsoKnownAs()
    {
        return implode(', ', $this->_data['also_known_as']);
    }

    public function getMovieCreditCasts()
    {
        $movies = [];

        foreach ($this->_data['movie_credits']['cast'] as $data) {
            $movies[] = new Movie($data);
        }

        return $movies;
    }
}
