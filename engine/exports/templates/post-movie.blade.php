<img class="img-fluid" src="{{ \Mopie::imgBackdrop($items->getBackdrop()) }}" alt="{{ __('seo.movie_title', ['title' => title_case($items->getTitle())]) }}"/>

    <div class="col d-flex justify-content-center my-3">
        <a href="{{ config('tmdb.export_button_watch') }}" class="btn btn-success mx-1 text-white">Watch Now</a>
        <a href="{{ config('tmdb.export_button_download') }}" class="btn btn-danger mx-1 text-white">Download</a>
    </div>

<p>{{ __('seo.movie_title', ['title' => title_case($items->getTitle())]) }} - {{ $items->getOverview() }}</p>

<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
