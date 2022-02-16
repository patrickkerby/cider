<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class App extends Controller
{
    public function siteName()
    {
        return get_bloginfo('name');
    }

    public static function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }
            return __('Latest Posts', 'sage');
        }
        if (is_archive()) {
            return get_the_archive_title();
        }
        if (is_search()) {
            return sprintf(__('Search Results for %s', 'sage'), get_search_query());
        }
        if (is_404()) {
            return __('Not Found', 'sage');
        }
        return get_the_title();
    }

    protected $acf = true;

    public function acf()
    {
        add_filter('sober/controller/acf/array', function () {
            return true;
        });
    }

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
