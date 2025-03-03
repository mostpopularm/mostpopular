<?php

namespace App\Http\Controllers\TMDB;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TMDB\Data\WithPagination;
use App\Http\Controllers\TMDB\Data\Movie;
use App\Http\Controllers\TMDB\Data\TVShow;
use App\Http\Controllers\TMDB\Data\Season;
use App\Http\Controllers\TMDB\Data\Episode;
use App\Http\Controllers\TMDB\Data\Person;
use App\Http\Controllers\TMDB\Data\Genre;

class TMDB extends Controller
{
    const _API_URL_ = "http://api.themoviedb.org/3/";
    const _ADULT_ = false;
    const _APPENDER_MOVIE_ = ['account_states', 'alternative_titles', 'credits', 'images','keywords', 'release_dates', 'videos', 'translations', 'similar', 'reviews', 'lists', 'changes', 'rating', 'recommendations'];
    const _APPENDER_TV_SHOW_ = ['account_states', 'alternative_titles', 'changes', 'content_rating', 'credits', 'external_ids', 'images', 'keywords', 'rating', 'similar', 'translations', 'videos', 'recommendations'];
    const _APPENDER_SEASON_ = ['changes', 'account_states', 'credits', 'external_ids', 'images', 'videos'];
    const _APPENDER_EPISODE_ = ['changes', 'account_states', 'credits', 'external_ids', 'images', 'rating', 'videos'];
    const _APPENDER_PEOPLE_ = ['movie_credits', 'tv_credits', 'images'];

    public static function getMovie($idMovie, $appendToResponse = null)
    {
		$appendToResponse = self::_APPENDER_MOVIE_;
		return new Movie(self::_call('movie/' . $idMovie, $appendToResponse));
    }

    /**
     * MOVIE
     * popular, top_rated, now_playing, upcoming
     */
    public static function getMovies($string, $page = 1, $options = [])
    {
        $result = self::_call('movie/'.$string, '&page='. $page, $options);
        return new WithPagination($result, 'movie');
    }

    public static function getTVShow($idTVShow, $appendToResponse = null)
    {
        $appendToResponse = self::_APPENDER_TV_SHOW_;
        return new TVShow(self::_call('tv/' . $idTVShow, $appendToResponse));
    }

    public static function getSeason($idTVShow, $numSeason, $appendToResponse = null)
    {
        $appendToResponse = self::_APPENDER_TV_SHOW_;
        return new Season(self::_call('tv/' .$idTVShow. '/season/' .$numSeason, $appendToResponse), $idTVShow);
    }

    public static function getEpisode($idTVShow, $idSeason, $idEpisode, $appendToResponse = null)
    {
        $appendToResponse = self::_APPENDER_EPISODE_;
        return new Episode(self::_call('tv/' .$idTVShow. '/season/' .$idSeason. '/episode/' .$idEpisode, $appendToResponse), $idTVShow);
    }

    /**
     * TVShows
     * airing_today, on_the_air, popular, top_rated
     */
    public static function getTvShows($string, $page = 1, $options = [])
    {
        $result = self::_call('tv/'.$string, '&page='. $page, $options);
        return new WithPagination($result, 'tv');
    }

    public static function getGenreMovie($id, $page = 1)
    {
        $result = self::_call('genre/'.$id.'/movies', '&page='. $page);
        return new WithPagination($result, 'movie');
    }

    public static function getKeywordMovie($id, $page = 1)
    {
        $result = self::_call('keyword/'.$id.'/movies', '&page='. $page);
        return new WithPagination($result, 'movie');
    }

    public static function getPeoplePopular($page = 1)
    {
        $result = self::_call('person/popular', '&page='. $page);
        return new WithPagination($result, 'people');
    }

    public static function getPeople($id, $page = 1)
    {
        $appendToResponse = self::_APPENDER_PEOPLE_;
        $result = self::_call('person/'.$id, $appendToResponse);
        return new Person($result);
    }

    public static function getGenreLists()
    {
        $result = self::_call('genre/movie/list');
        $genres = [];

        foreach ($result['genres'] as $data) {
            $genres[] = new Genre($data);
        }

        return $genres;
    }

    public function getSearch($keyword = '', $page = 1)
    {
        $result = self::_call('search/movie', '&page='. $page.'&query='.$keyword);
        return new WithPagination($result, 'movie');
    }

    public static function _call($action, $appendToResponse = '', $options = [])
    {
        $url = self::_API_URL_.$action .'?api_key='. config('tmdb.api_key') .'&append_to_response='. implode(',', (array) $appendToResponse) .'&include_adult='. self::_ADULT_ .'&include_image_language=en,null';

        if (!empty($options)) {
            $url = $url.'&'.http_build_query($options);
        }else{
            $options = ['language' => request()->segment(1) ];
            $url = $url.'&'.http_build_query($options);
        }

        if ( config('tmdb.tmdb_debug') ) {
            echo '<pre><a href="' . $url . '">check request</a></pre>';
        }

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FAILONERROR, 1);
		$results = curl_exec($ch);
		curl_close($ch);
		return (array) json_decode(($results), true);
	}
}
