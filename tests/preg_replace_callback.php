<?php

//require_once('../../../config.php');

$string = "<p>Zoom link: https://zoom.us/recording/play/9S4ijO8RI2OTjI6HOSVdgjGDRnRjnLzO-m7aPFS_MV3zMuQpo4Vj4TK9rVljO1mq?continueMode=true</p>
	<p>Zoom link 2: https://zoom.us/recording/play/9S4ijO8RI2OTjI6HOSVdgjGDRnRjnLzO-m7aPFS_MV3zMuQpo4Vj4TK9rVljO1mq?continueMode=true</p>
	<p>A Zoom link 3: https://zoom.us/recording/play/9S4ijO8RI2OTjI6HOSVdgjGDRnRjnLzO-m7aPFS_MV3zMuQpo4Vj4TK9rVljO1mq?continueMode=true</p>";

$string = preg_replace_callback(
        '/(https:\/\/zoom\.us\/recording\/play\/)([A-Za-z0-9\-\_]+)(\?continueMode=true)?/',
        "callback",
        $string
    );

echo $string;

function callback($matches){
	return '<video width="100%" controls="controls" src="https://api.zoom.us/recording/download/'
	. $matches[2] .
	'" poster="https://my.cbd.edu/pluginfile.php/1070/mod_page/content/4/Thumbnail%20Image.png"></video>';
}