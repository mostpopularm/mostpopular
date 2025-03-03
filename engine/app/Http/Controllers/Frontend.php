<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\TMDB\TMDB;
use Illuminate\Support\Facades\Cache;
use App\Mopie;
use Illuminate\Pagination\LengthAwarePaginator;
use SEO;
use Illuminate\Support\Facades\URL;

class Frontend extends Controller
{
    public function home(Request $request)
    {
        $this->getSEOMeta(
            __('seo.home_title'),
            __('seo.home_description')
        );

        $tmdb = new TMDB;
        $page = 1;

        if ($request->s != null) {
            $keyword = str_replace(' ', '-', $request->s);
            return redirect()->route('search', ['keyword' => $keyword]);
        }

        if ($this->isCache()) {
            $movie_popular = Cache::remember('popular-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getMovies('popular');
            });
            $movie_now_playing = Cache::remember('now_playing-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getMovies('now_playing');
            });
            $movie_top_rated = Cache::remember('top_rated-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getMovies('top_rated');
            });
            $movie_upcoming = Cache::remember('upcoming-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getMovies('upcoming');
            });

            $tv_popular = Cache::remember('tv-popular-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getTVShows('popular');
            });
            $tv_top_rated = Cache::remember('tv-top-rated-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getTVShows('top_rated');
            });
            $tv_on_the_air = Cache::remember('tv-on-the-air-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getTVShows('on_the_air');
            });
            $tv_airing_today = Cache::remember('tv-airing_today-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getTVShows('airing_today');
            });
        }else{
            $movie_popular = $tmdb->getMovies('popular', $page);
            $movie_now_playing = $tmdb->getMovies('now_playing', $page);
            $movie_top_rated = $tmdb->getMovies('top_rated', $page);
            $movie_upcoming = $tmdb->getMovies('now_playing', $page);

            $tv_popular = $tmdb->getTVShows('popular', $page);
            $tv_top_rated = $tmdb->getTVShows('top_rated', $page);
            $tv_on_the_air = $tmdb->getTVShows('on_the_air', $page);
            $tv_airing_today = $tmdb->getTVShows('airing_today', $page);
        }

        return view(
            config('tmdb.theme').'.home',
            compact(
                'movie_popular',
                'movie_upcoming',
                'movie_top_rated',
                'movie_now_playing',
                'tv_popular',
                'tv_top_rated',
                'tv_on_the_air',
                'tv_airing_today'
            ));
    }

    public function search($keyword, Request $request)
    {
        $tmdb = new TMDB;
        $page = $request->page;
        $keyword_title = str_replace('-', ' ', $keyword);

        $data = $tmdb->getSearch($keyword, $page);
        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('search', ['keyword' => $keyword])]
        );
        return view(config('tmdb.theme').'.search', compact('keyword_title', 'data'));
    }

    public function singleMovie($id, $slug = '')
    {
        $tmdb = new TMDB;

        if ($this->isCache()) {
            $movie = Cache::remember('movie-'.$id.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id) {
                return $tmdb->getMovie($id);
            });
        }else{
            $movie = $tmdb->getMovie($id);
        }

        $backdrop = Mopie::imgBackdrop($movie->getBackdrop(), 'original');

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($movie->getTitle())]),
            __('seo.movie_title', ['title' => title_case($movie->getTitle())]).' - '.$movie->getOverview(),
            $backdrop,
            $this->getSeoKeywords($movie->getKeywords())
        );

        return view(config('tmdb.theme'). '.single_movie', compact('movie', 'backdrop'));
    }

    public function singleTV($id, $slug = '')
    {
        $tmdb = new TMDB;
        $type = 'tv';

        if ($this->isCache()) {
            $tv = Cache::remember('tv-'.$id.'-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id) {
                return $tmdb->getTVShow($id);
            });
            $season      = $tv->getNumSeasons();
            $season_info = Cache::remember('tv-'.$id.'season-'.$season.'-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id, $season) {
                return $tmdb->getSeason($id, $season);
            });
            $season_select = $season_info->getEpisodes();
        }else{
            $tv            = $tmdb->getTVShow($id);
            $season        = $tv->getNumSeasons();
            $season_info   = $tmdb->getSeason($id, $season);
            $season_select = $season_info->getEpisodes();
        }

        $title = $tv->getName();
        $overview = $tv->getOverview();
        $backdrop = Mopie::imgBackdrop($tv->getBackdrop(), 'original');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)]).' - '.$overview,
            $backdrop,
            $this->getSeoKeywords($tv->getKeywords())
        );

        return view(
            config('tmdb.theme'). '.single_tv',
            compact(
                'tv',
                'season_select',
                'season_info',
                'season',
                'title',
                'type',
                'overview',
                'backdrop'
            )
        );
    }

    public function singleTVSeason($id, $season, $slug = '')
    {
        $tmdb = new TMDB;
        $type = 'season';

        if ($this->isCache()) {
            $tv = Cache::remember('tv-'.$id.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id) {
                return $tmdb->getTVShow($id);
            });
            $season_info = Cache::remember('tv-'.$id.'season-'.$season.'-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id, $season) {
                return $tmdb->getSeason($id, $season);
            });
            $season_select = $season_info->getEpisodes();
        }else{
            $tv = $tmdb->getTVShow($id);
            $season_info = $tmdb->getSeason($id, $season);
            $season_select = $season_info->getEpisodes();
        }

        $title = $tv->getName().' '.$season_info->getName();
        $overview = empty($season_info->getOverview()) ? $tv->getOverview() : $season_info->getOverview();
        $backdropShuffle = $tv->getRandomBackdrop()->getFilePath();
        $backdrop = Mopie::imgBackdrop($backdropShuffle, 'original');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)]).' - '.$overview,
            $backdrop,
            $this->getSeoKeywords($tv->getKeywords())
        );

        return view(
            config('tmdb.theme'). '.single_tv',
            compact(
                'tv',
                'season_info',
                'season_select',
                'season',
                'title',
                'type',
                'overview',
                'backdrop'
            )
        );
    }

    public function singleTVSeasonEpisode($id, $season, $episode, $slug)
    {
        $tmdb = new TMDB;
        $type = 'episode';

        if ($this->isCache()) {
            $tv = Cache::remember('tv-'.$id.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id) {
                return $tmdb->getTVShow($id);
            });
            $season_info = Cache::remember('tv-'.$id.'season-'.$season.'-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id, $season) {
                return $tmdb->getSeason($id, $season);
            });
            $season_select = $season_info->getEpisodes();
            $episode_info = Cache::remember('tv-'.$id.'season-'.$season.'-episode-'.$episode.'-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id, $season, $episode) {
                return $tmdb->getEpisode($id, $season, $episode);
            });
        }else{
            $tv = $tmdb->getTVShow($id);
            $season_info = $tmdb->getSeason($id, $season);
            $season_select = $season_info->getEpisodes();
            $episode_info = $tmdb->getEpisode($id, $season, $episode);
        }

        $title = $tv->getName().' '.
                $season_info->getName().' '.
                __('utilities.episode').' '.
                $episode_info->getEpisodeNumber().' '.
                $episode_info->getName();
        $overview = empty($episode_info->getOverview()) ? $tv->getOverview() : $episode_info->getOverview();
        $backdrop = empty( $episode_info->getStill() ) ? $tv->getBackdrop() : $episode_info->getStill();
        $backdrop = Mopie::imgBackdrop($backdrop, 'original');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)]).' - '.$overview,
            $backdrop,
            $this->getSeoKeywords($tv->getKeywords())
        );

        return view(
            config('tmdb.theme'). '.single_tv',
            compact(
                'tv',
                'season_info',
                'season_select',
                'season',
                'title',
                'type',
                'episode_info',
                'overview',
                'backdrop'
            )
        );
    }

    public function moviePopular(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('popular-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getMovies('popular', $page);
            });
        }else{
            $data = $tmdb->getMovies('popular', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('movie.popular')]
        );

        $title = __('section.title.popular');

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function movieNowPlaying(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('now_playing-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getMovies('now_playing', $page);
            });
        }else{
            $data = $tmdb->getMovies('now_playing', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('movie.now.playing')]
        );

        $title = __('section.title.now_playing');

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function movieTopRated(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('top_rated-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getMovies('top_rated', $page);
            });
        }else{
            $data = $tmdb->getMovies('top_rated', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('movie.top.rated')]
        );

        $title = __('section.title.movie_top_rated');

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function movieUpcoming(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('upcoming-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getMovies('upcoming', $page);
            });
        }else{
            $data = $tmdb->getMovies('upcoming', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('movie.upcoming')]
        );

        $title = __('section.title.movie_upcoming');

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function tvPopular(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'tv';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('tv-popular-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getTvShows('popular', $page);
            });
        }else{
            $data = $tmdb->getTvShows('popular', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('tv.popular')]
        );

        $title = __('section.title.tv_popular');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function tvTopRated(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'tv';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('tv-top-rated-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getTvShows('top_rated', $page);
            });
        }else{
            $data = $tmdb->getTvShows('top_rated', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('tv.top.rated')]
        );

        $title = __('section.title.tv_top_rated');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function tvAiringToday(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'tv';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('tv-airing-today-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getTvShows('airing_today', $page);
            });
        }else{
            $data = $tmdb->getTvShows('airing_today', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('tv.airing.to.day')]
        );

        $title = __('section.title.tv_airing_today');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function tvOnTheAir(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'tv';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('tv-on-the-air-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getTvShows('on_the_air', $page);
            });
        }else{
            $data = $tmdb->getTvShows('on_the_air', $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('tv.on.the.air')]
        );

        $title = __('section.title.tv_on_the_air');

        $this->getSEOMeta(
            __('seo.tv_title', ['title' => title_case($title)]),
            __('seo.tv_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function genre($id, $slug = '', Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page')?: 1;

        if ($this->isCache()) {
            $data = Cache::remember('genre-'.$id.'-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page, $id) {
                return $tmdb->getGenreMovie($id, $page);
            });
        }else{
            $data = $tmdb->getGenreMovie($id, $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('genre', ['id' => $id, 'slug' => $slug])]
        );

        if (str_slug($this->findNameGenre($id)) != str_slug($slug)) {
            return redirect(Mopie::route('genre', ['id' => $id, 'slug' => str_slug($this->findNameGenre($id))]));
        }

        $title = title_case( __('section.title.movie_genre')).' "'.title_case(str_replace('-', ' ', $this->findNameGenre($id))).'"';

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function keyword($id, Request $request)
    {
        $tmdb = new TMDB;
        $type = 'movie';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('keyword-'.$id.'-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page, $id) {
                return $tmdb->getKeywordMovie($id, $page);
            });
        }else{
            $data = $tmdb->getKeywordMovie($id, $page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('keyword')]
        );

        $title = title_case( str_replace('-', ' ', $slug) );

        $this->getSEOMeta(
            __('seo.movie_title', ['title' => title_case($title)]),
            __('seo.movie_title', ['title' => title_case($title)])
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function peoplePopular(Request $request)
    {
        $tmdb = new TMDB;
        $type = 'people';
        $page = $request->input('page');

        if ($this->isCache()) {
            $data = Cache::remember('people-popular-'.$page.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $page) {
                return $tmdb->getPeoplePopular($page);
            });
        }else{
            $data = $tmdb->getPeoplePopular($page);
        }

        $data = new LengthAwarePaginator(
            $data->getResults(),
            $data->getTotalResults(),
            20,
            $data->getPage(),
            [ 'path' => route('people.popular')]
        );

        $title = __('section.title.popular_people');

        $this->getSEOMeta(
            title_case($title),
            title_case($title)
        );

        return view(config('tmdb.theme').'.archive', compact('data', 'title', 'type'));
    }

    public function people($id)
    {
        $tmdb = new TMDB;

        if ($this->isCache()) {
            $data = Cache::remember('people-'.$id.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb, $id) {
                return $tmdb->getPeople($id);
            });
        }else{
            $data = $tmdb->getPeople($id);
        }

        $title = $data->getName();

        $this->getSEOMeta(
            title_case($title),
            title_case($title)
        );

        return view(config('tmdb.theme').'.people', compact('data', 'title'));
    }

    public function genreLists()
    {
        $tmdb = new TMDB;

        if ($this->isCache()) {
            $data = Cache::remember('genre-lists-'.$this->getLanguage(), config('tmdb.cache_exp'), function () use ($tmdb) {
                return $tmdb->getGenreLists();
            });
        }else{
            $data = $tmdb->getGenreLists();
        }

        return $data;
    }

    public function isCache()
    {
        return config('tmdb.is_cache');
    }

    private function paginate($results, $total, $perPage, $currentPage)
    {
        return new LengthAwarePaginator($results, $total, $perPage, $currentPage);
    }

    public function getLanguage()
    {
        return request()->segment(1);
    }

    private function findNameGenre($id)
    {
        foreach ($this->genreLists() as $data) {
            if ($data->getID() == $id) {
                $name = $data->getName();
            }
        }

        return $name;
    }

    private function getSEOMeta($title, $description = '', $img = '', $keyword = [])
    {
        SEO::setTitle($title);
        SEO::setCanonical(URL::current());
        (empty($description))?:SEO::setDescription($description);
        (empty($img))?:SEO::addImages($img);
        (empty($keyword))?:\SEOMeta::addKeyword($keyword);

        $this->getHrefLang();
    }

    private function getSeoKeywords($data = [])
    {
        $kwList = [];

        foreach ($data as $data) {
            $kwList[] = $data->getName();
        }

        return $kwList;
    }

    private function getHrefLang()
    {
        $hrefLang = [];

        foreach (config('app.locales') as $key => $value) {
            $replaceLang = str_replace(request()->segment(1), $key, request()->path());
            $url = request()->root().'/'.$replaceLang;
            $hrefLang[] = SEO::metatags()->addAlternateLanguage($key, $url);
        }

        return $hrefLang;
    }

}
