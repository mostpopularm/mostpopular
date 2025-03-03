<img class="img-fluid" src="{{ \Mopie::imgBackdrop($items->getBackdrop()) }}" alt="{{ __('seo.tv_title', ['title' => title_case($items->getName())]) }}">

<div class="col d-flex justify-content-center my-3">
    <a href="//moviefone.xyz/loading?source=blogspot" class="btn btn-success mx-1 text-white">Watch Now</a>
    <a href="//moviefone.xyz/loading?source=blogspot" class="btn btn-danger mx-1 text-white">Download</a>
</div>

<p>{{ __('seo.tv_title', ['title' => title_case($items->getName())]) }} - {{ $items->getOverview() }}</p>

<link rel="stylesheet" href="//stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
