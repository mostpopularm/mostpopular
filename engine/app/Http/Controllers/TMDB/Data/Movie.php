<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TMDB\Data\BaseObject;
use App\Mopie;
use Illuminate\Support\Carbon;

class Movie extends BaseObject
{
    private $_tmdb;

    public function getTranslations()
    {
        return collect($this->_data['translations']['translations'])->pluck('iso_639_1');
    }

    public function getTitle()
    {
        return $this->_data['title'];
    }

    public function getTagline()
    {
		return $this->_data['tagline'];
    }

    public function getRuntime()
    {
        return $this->_data['runtime'];
    }

    public function getTrailers()
    {
		return $this->_data['trailers'];
    }

    public function getTrailer()
    {
		$trailers = $this->getTrailers();
		return $trailers['youtube'][0]['source'];
	}

    public function getPopularity()
    {
        return $this->_data['popularity'];
    }

    public function getOriginalTitle()
    {
        return $this->_data['original_title'];
    }

    public function getBackdrop()
    {
        return $this->_data['backdrop_path'];
    }

    public function getOverview()
    {
        return $this->_data['overview'];
    }

    public function getReleaseDate()
    {
        return empty($this->_data['release_date']) ? '' : $this->_data['release_date'];
    }

    public function getYear()
    {
        return Carbon::parse($this->getReleaseDate())->year;
    }

    public function getGenres()
    {
		$genres = array();
		foreach ($this->_data['genres'] as $data) {
			$genres[] = new Genre($data);
		}
		return $genres;
    }

    public function getGenreComma()
    {
        $genres = [];

        foreach ($this->_data['genres'] as $data) {
            $genres[] = '<a href="'.Mopie::route('genre', ['id' => $data['id']]).'" title="'.$data['name'].'">'.$data['name'].'</a>';
        }

        return implode(', ',$genres);
    }

    public function getRecommendations()
    {
        $recommendations = [];
        $results = Mopie::blockID($this->_data['recommendations']['results'], config('tmdb.block_movie'));

        foreach ($results as $data) {
            $recommendations[] = new Movie($data);
        }

        return $recommendations;
    }

    public function getSimilars()
    {
        $similars = [];
        $results = Mopie::blockID($this->_data['similar']['results'], config('tmdb.block_movie'));

        foreach ($results as $data) {
            $similars[] = new Movie($data);
        }

        return $similars;
    }

    public function getReviews()
    {
		$reviews = array();
		foreach ($this->_data['reviews']['results'] as $data) {
			$reviews[] = new Review($data);
		}
		return $reviews;
    }

    public function getCompanies()
    {
		$companies = array();

		foreach ($this->_data['production_companies'] as $data) {
			$companies[] = new Company($data);
		}

		return $companies;
    }

    public function getKeywords()
    {
        $keywords = array();

        foreach ($this->_data['keywords']['keywords'] as $data) {
            $keywords[] = new Keyword($data);
        }

        return $keywords;
    }

    public function setAPI($tmdb)
    {
		$this->_tmdb = $tmdb;
    }

    public function getJSON()
    {
		return json_encode($this->_data, JSON_PRETTY_PRINT);
	}
}
