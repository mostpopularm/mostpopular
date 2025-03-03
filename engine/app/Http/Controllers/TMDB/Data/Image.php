<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Image extends Controller
{
    private $data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function getFilePath()
    {
        return $this->_data['file_path'];
    }
}
