<?php


namespace Fridde;


class ColorConverter
{
    public const KELLY_COLORS = [
        'white' => 'F2F3F4',
        'black' => '222222',
        'yellow' => 'F3C300',
        'purple' => '875692',
        'orange' => 'F38400',
        'light_blue' => 'A1CAF1',
        'red' => 'BE0032',
        'buff' => 'C2B280',
        'grey' => '848482',
        'green' => '008856',
        'purplish_pink' => 'E68FAC',
        'blue' => '0067A5',
        'yellowish_pink' => 'F99379',
        'violet' => '604E97',
        'orange_yellow' => 'F6A600',
        'purplish_red' => 'B3446C',
        'greenish_yellow' => 'DCD300',
        'reddish_brown' => '882D17',
        'yellow_green' => '8DB600',
        'yellowish_brown' => '654522',
        'reddish_orange' => 'E25822',
        'olive_green' => '2B3D26',
    ];

    public static function rgbToHsl($r, $g, $b)
    {
        $oldR = $r;
        $oldG = $g;
        $oldB = $b;

        $r /= 255;
        $g /= 255;
        $b /= 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);

        $h;
        $s;
        $l = ($max + $min) / 2;
        $d = $max - $min;

        if ($d == 0) {
            $h = $s = 0; // achromatic
        } else {
            $s = $d / (1 - abs(2 * $l - 1));

            switch ($max) {
                case $r:
                    $h = 60 * fmod((($g - $b) / $d), 6);
                    if ($b > $g) {
                        $h += 360;
                    }
                    break;

                case $g:
                    $h = 60 * (($b - $r) / $d + 2);
                    break;

                case $b:
                    $h = 60 * (($r - $g) / $d + 4);
                    break;
            }
        }

        return [round($h, 2), round($s, 2), round($l, 2)];
    }



    public static function hslToRgb($h, $s, $l)
    {
        $r;
        $g;
        $b;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = $l - ($c / 2);

        if ($h < 60) {
            $r = $c;
            $g = $x;
            $b = 0;
        } else {
            if ($h < 120) {
                $r = $x;
                $g = $c;
                $b = 0;
            } else {
                if ($h < 180) {
                    $r = 0;
                    $g = $c;
                    $b = $x;
                } else {
                    if ($h < 240) {
                        $r = 0;
                        $g = $x;
                        $b = $c;
                    } else {
                        if ($h < 300) {
                            $r = $x;
                            $g = 0;
                            $b = $c;
                        } else {
                            $r = $c;
                            $g = 0;
                            $b = $x;
                        }
                    }
                }
            }
        }

        $r = ($r + $m) * 255;
        $g = ($g + $m) * 255;
        $b = ($b + $m) * 255;

        return [floor($r), floor($g), floor($b)];
    }


}