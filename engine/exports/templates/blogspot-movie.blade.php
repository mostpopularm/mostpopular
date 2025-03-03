@php
    use Illuminate\Support\Arr;
    $tmdb = new App\Http\Controllers\TMDB\TMDB();
    $frontend = new App\Http\Controllers\Frontend();
    $genreLists = $frontend->genreLists();
@endphp

{!! '<' . '?' . "xml version='1.0' encoding='UTF-8'?>" !!}
<ns0:feed xmlns:ns0="http://www.w3.org/2005/Atom">
<ns0:title type="html">wpan.com</ns0:title>
<ns0:generator>Blogger</ns0:generator>
<ns0:link href="http://localhost/wpan" rel="self" type="application/atom+xml" />
<ns0:link href="http://localhost/wpan" rel="alternate" type="text/html" />
<ns0:updated>2016-06-10T04:33:36Z</ns0:updated>
@for ($i = 1; $i <= $data['page']; $i++)
    @foreach ($tmdb->getMovies($data['sort'], $i, ['language' => $data['lang']])->getResults() as $items)
        @php
            $timestamp = strtotime($items->getReleaseDate());
        @endphp
        <ns0:entry>
            @foreach($genreLists as $genre)
                @foreach ($items->get('genre_ids') as $genre_id)
                    @if ($genre->getID() == $genre_id)
                        <ns0:category scheme="http://www.blogger.com/atom/ns#" term="{{ $genre->getName() }}" />
                    @endif
                @endforeach
            @endforeach
            <ns0:category scheme="http://schemas.google.com/g/2005#kind" term="http://schemas.google.com/blogger/2008/kind#post" />
            <ns0:id>post-{{ $items->getID() }}</ns0:id>
            <ns0:author>
                <ns0:name>admin</ns0:name>
            </ns0:author>
            <ns0:content type="html">{{ view('exports.templates.post-movie', compact('items')) }}</ns0:content>
            <ns0:published>{{ date('Y-m-d', $timestamp) }}T{{ date('H:i:s', $timestamp) }}Z</ns0:published>
            <ns0:title type="html">{{ __('seo.movie_title', ['title' => title_case($items->getTitle())]) }}</ns0:title>
            <ns0:link href="http://localhost/wpan/{{ $items->getID() }}/" rel="self" type="application/atom+xml" />
            <ns0:link href="http://localhost/wpan/{{ $items->getID() }}/" rel="alternate" type="text/html" />
        </ns0:entry>
    @endforeach
@endfor
</ns0:feed>
