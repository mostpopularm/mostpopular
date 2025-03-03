<?php

namespace App\Http\Controllers\TMDB\Data;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Keyword extends Controller
{
    private $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function getName()
    {
        return $this->_data['name'];
    }

    public function getID()
    {
        return $this->_data['id'];
    }
}
