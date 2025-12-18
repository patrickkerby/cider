<?php

namespace App\Controllers;

use Sober\Controller\Controller;

class Page extends Controller
{
    protected $acf = true;

    /**
     * Return colour scheme for front page
     * Reuse the FrontPage controller logic
     */
    public function colourScheme()
    {
        // Default colour scheme for non-front pages
        $default = [
            'colour' => 'rgba(255, 255, 255, 1)',
            'image' => false,
            'colour_overlay' => 'rgba(255, 255, 255, 0.5)'
        ];
        
        // Only run full logic for the front page
        if (!is_front_page()) {
            return $default;
        }

        $repeater = get_field('background_images');
        
        // Check if repeater field exists and has items
        if (!$repeater || !is_array($repeater) || empty($repeater)) {
            // Return default values when field is not available
            return [
                'colour' => 'rgba(255, 255, 255, 1)',
                'image' => false,
                'colour_overlay' => 'rgba(255, 255, 255, 0.5)'
            ];
        }
        
        $rand = rand(0, (count($repeater) - 1));
        $image = isset($repeater[$rand]['image']) ? $repeater[$rand]['image'] : false;
        $colour_picker = isset($repeater[$rand]['colour_picker']) ? $repeater[$rand]['colour_picker'] : null;
        
        // Set default colour values if colour_picker is not available
        if ($colour_picker && is_array($colour_picker)) {
            $colour = "rgba(" . $colour_picker['red'] . "," . $colour_picker['green'] . "," . $colour_picker['blue'] . ", 1)";
            $colour_overlay = "rgba(" . $colour_picker['red'] . "," . $colour_picker['green'] . "," . $colour_picker['blue'] . "," . $colour_picker['alpha'] . ")";
        } else {
            $colour = 'rgba(255, 255, 255, 1)';
            $colour_overlay = 'rgba(255, 255, 255, 0.5)';
        }

        return compact('colour', 'image', 'colour_overlay');
    }

}

