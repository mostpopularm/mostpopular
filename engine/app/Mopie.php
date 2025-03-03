<?php

namespace App;

use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;

class Mopie
{
    public static function imgPoster($path)
    {
        if (empty($path)) {
            return asset('no-poster.png');
        }
        return '//i0.wp.com/image.tmdb.org/t/p/w300'.$path.'?resize=300,450';
    }

    public static function imgBackdrop($path, $size = 'w780')
    {
        if (empty($path)) {
            return asset('no-backdrop.png');
        }
        return '//i0.wp.com/image.tmdb.org/t/p/'.$size.$path;
    }

    public static function changeLang($currentUrl, $lang)
    {
        return URL::to('/'.$lang);
    }

    public static function subID()
    {
        $request = new Request();
        dd($request->all());
        if ($request->sub_id != null) {
            return ['sub_id' => $request->sub_id];
        }

        return [];
    }

    public static function route($name, $parameters = [], $absolute = true)
    {
        if (isset($_REQUEST['sub_id'])) {
            if (is_array($parameters)) {
                return app('url')->route($name, array_merge(['sub_id' => $_REQUEST['sub_id']], $parameters), $absolute);
            }

            return app('url')->route($name, ['sub_id' => $_REQUEST['sub_id']], $absolute);
        }

        return app('url')->route($name, $parameters, $absolute);
    }

    public static function blockID($results = [], $bad_id = [])
    {
        return collect($results)->whereNotIn('id', $bad_id);
    }
}
