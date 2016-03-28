<?php
require_once('autoload.php');

use ColorTools\Color as Color;

$urls[] = 'http://en.wikipedia.org/wiki/List_of_colors:_A%E2%80%93F';
$urls[] = 'https://en.wikipedia.org/wiki/List_of_colors:_G%E2%80%93M';
$urls[] = 'https://en.wikipedia.org/wiki/List_of_colors:_N%E2%80%93Z';

function getColorsFromWikiTablePage($url) {
    $content = file_get_contents($url);
    $colors=[];
    preg_match_all("/<tr>\s*<th.*>(.*)<\/th>[\s.]*<td.*>(.*)<\/td>[.\s\S]*<\/tr>/U", $content, $foundColors);
    foreach($foundColors[2] as $key=>$color) {
        $name = $foundColors[1][$key];
        if(strpos($name, '<a')===0) {
            preg_match("/<a.*href=\"(.*)\".*>(.*)<\/a>/U", $name, $name);

            $url = $name[1];
            $name = $name[2];

            if(stripos($url, 'http')!==0) {
                $url = 'http://en.wikipedia.org'.$url;
            }
        }

        $color = ['hex'=>$color, 'name'=>$name];
        if(isset($url)) {
            $color['url'] = $url;
            unset($url);
        }

        /*
         * Not sure what key to use - HEX is the logical option but there are at this date, 1265 colors
         * but only 1130 unique HEX codes. 11% is a significant, but some examples of duplicates will give you
         * a clearer idea:
         *
         * https://en.wikipedia.org/wiki/Azure_(color)#Azure_.28web_color.29
         * https://en.wikipedia.org/wiki/Azure_mist -> alias of Azure(Web Color)
         *
         * Please write me if you believe this was not the best approach.
         * Thanks
         */

        $colors[$color['hex']] = $color;
    }
    return $colors;
}

function buildArrayString($array)
{
    $output ='[';
    foreach($array as $key=>$value) {
        $output.='\''.str_replace('\'', '\\\'', $key).'\'=>';
        if(count($value)==1) {
            $output.=$value[0];
        } else {
            $output.='['.$value[0].',[';
            foreach($value[1] as $p=>$v) {
                $output.='\''.str_replace('\'', '\\\'', $p).'\'=>\''.str_replace('\'', '\\\'', $v).'\'';
            }
            $output.=']]';
        }
        $output.=','.PHP_EOL;
    }
    $output = substr($output, 0, -2).']';

    return $output;
}

$colors = [];
foreach($urls as $url) {
    $colors = array_merge($colors, getColorsFromWikiTablePage($url));
}


$colorsArray=[];
foreach($colors as $hex=>$color) {
    $colorsArray[$color['name']][]='0x'.strtolower(ltrim($hex, '#'));
    if(isset($color['url'])) {
        $colorsArray[$color['name']][]=['url'=>$color['url']];
    }
}

echo buildArrayString($colorsArray);
