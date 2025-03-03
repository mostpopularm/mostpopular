@php
    use Illuminate\Support\Arr;
    $tmdb = new App\Http\Controllers\TMDB\TMDB();
    $frontend = new App\Http\Controllers\Frontend();
    $genreLists = $frontend->genreLists();

    $author = 'Admin';
    $site_url = 'http://example.com/';
@endphp

{!! '<' . '?' . 'xml version="1.0" encoding="UTF-8"' !!}
<rss version="2.0"
	xmlns:excerpt="http://wordpress.org/export/1.0/excerpt/"
	xmlns:content="http://purl.org/rss/1.0/modules/content/"
	xmlns:wfw="http://wellformedweb.org/CommentAPI/"
	xmlns:dc="http://purl.org/dc/elements/1.1/"
	xmlns:wp="http://wordpress.org/export/1.0/"
>

<channel>
    <title>My Site</title>
    <link>http://example.com/</link>
    <description></description>
	<pubDate>Thu, 28 May 2009 16:06:40 +0000</pubDate>
	<wp:author><wp:author_id>1</wp:author_id><wp:author_login><![CDATA[admin]]></wp:author_login><wp:author_email><![CDATA[buchin@dropsugar.com]]></wp:author_email><wp:author_display_name><![CDATA[admin]]></wp:author_display_name><wp:author_first_name><![CDATA[]]></wp:author_first_name><wp:author_last_name><![CDATA[]]></wp:author_last_name></wp:author>

	<generator>http://wordpress.org/?v=2.7.1</generator>
	<language>en</language>
	<wp:wxr_version>1.0</wp:wxr_version>
	<wp:base_site_url>http://example.com/</wp:base_site_url>
    <wp:base_blog_url>http://example.com/</wp:base_blog_url>

    @for ($i = 1; $i <= $data['page']; $i++)
        @foreach ($tmdb->getMovies($data['sort'], $i, ['language' => $data['lang']])->getResults() as $items)
            @php
                $title = __('seo.movie_title', ['title' => title_case($items->getTitle())]);
                $backdate = is_null($data['backdate']) ? $items->getReleaseDate() : $data['backdate'];
                $uniqTime = rand(strtotime($backdate), time());
                $pubDate = date( 'D, d M Y H:i:s', $uniqTime )." +0000";
                $postDate = date( 'Y-m-d H:i:s', $uniqTime );
                $postID = $items->getID();
                $slug = str_slug($items->getTitle());

                $content = view('exports.templates.post-movie', compact('items'));
            @endphp
            <item>
                <title><![CDATA[{{ $title }}]]></title>
                <pubDate>{{ $pubDate }}</pubDate>
                <dc:creator><![CDATA[{{ $author }}]]></dc:creator>
                <wp:postmeta>
                    <wp:meta_key>_byline</wp:meta_key>
                    <wp:meta_value>{{ $author }}</wp:meta_value>
                </wp:postmeta>

                @foreach($genreLists as $genre)
                    @foreach ($items->get('genre_ids') as $genre_id)
                        @if ($genre->getID() == $genre_id)
                            <category><![CDATA[{{ $genre->getName() }}]]></category>
                            <category domain="category" nicename="{{ str_slug($genre->getName()) }}"><![CDATA[{{ $genre->getName() }}]]></category>
                            <category domain="tag" nicename="{{ str_slug($genre->getName()) }}"><![CDATA[{{ $genre->getName() }}]]></category>
                        @endif
                    @endforeach
                @endforeach

                <guid isPermaLink="false">{{ $site_url }}?p={{ $postID }}</guid>
                <description></description>
                <content:encoded><![CDATA[{!! $content !!}]]></content:encoded>
                <excerpt:encoded><![CDATA[]]></excerpt:encoded>
                <wp:post_id>{{ $postID }}</wp:post_id>
                <wp:post_date>{{ $postDate }}</wp:post_date>
                <wp:post_date_gmt>{{ $postDate }}</wp:post_date_gmt>
                <wp:comment_status>open</wp:comment_status>
                <wp:ping_status>closed</wp:ping_status>
                <wp:post_name>{{ $slug }}</wp:post_name>

                <wp:status>publish</wp:status>
                <wp:post_parent>0</wp:post_parent>
                <wp:menu_order>0</wp:menu_order>
                <wp:post_type>post</wp:post_type>
                <wp:post_password></wp:post_password>

                <wp:postmeta>
                    <wp:meta_key>_old_id</wp:meta_key>
                    <wp:meta_value>{{ $postID }}</wp:meta_value>
                </wp:postmeta>

            </item>
        @endforeach
    @endfor
</channel>
</rss>
