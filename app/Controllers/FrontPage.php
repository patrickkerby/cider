<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class FrontPage extends Controller
{

  public function colourScheme()
    {
        $repeater = get_field( 'background_images' );
        $rand = rand(0, (count($repeater) - 1));
        $image = ($repeater[$rand]['image']);
        $colour_picker = ($repeater[$rand]['colour_picker']);
        $colour = "rgba(". $colour_picker['red'] . "," . $colour_picker['green'] . "," . $colour_picker['blue'] . ", 1)";
        $colour_overlay = "rgba(". $colour_picker['red'] . "," . $colour_picker['green'] . "," . $colour_picker['blue'] . "," . $colour_picker['alpha'] . ")";

        return compact('colour','image','colour_overlay');

    }  

}
