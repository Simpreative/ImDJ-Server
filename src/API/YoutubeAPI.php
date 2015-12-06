<?php
function getDuration($vid) {
	$key = YOUTUBE_API_TOKEN;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL, "https://www.googleapis.com/youtube/v3/videos?id={$vid}&key={$key}&part=contentDetails");
	curl_setopt($curl, CURLOPT_POST, 0);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	$return = curl_exec($curl);
	curl_close($curl);
	$return = json_decode($return, true);

	$time = $return['items']['0']['contentDetails']['duration'];
	$start = new DateTime('@0'); // Unix epoch
	$start->add(new DateInterval($time));
	return $start->getTimestamp();
}
