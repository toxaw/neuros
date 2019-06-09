<?php
set_time_limit(3600);
$t = time();
$result = array_values(array_diff(scandir('candidates'), ['..', '.']));
$candidates = [];
echo '<pre>';
foreach ($result as $key => $value) {
	$open = json_decode(file_get_contents('candidates/' . $value), true);
	usort($open['coeffs'], function ($a, $b) 
	{
	    if ($a[0] == $b[0]) {
	        return 0;
	    }
	    return ($a[0] < $b[0]) ? -1 : 1;
	});
	$candidates[$open['text']] = $open['coeffs']; 
}

die('[' . count($candidates) . '][' . (time()-$t) . ']');
//ie(print_r($open['coeffs']));