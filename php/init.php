<?php

	// Analyse avec sections
	$ini_array = parse_ini_file("properties.ini", true);
	$data_base_host = $ini_array['Base_de_donnes']['data_base_host'];
	$data_base_name = $ini_array['Base_de_donnes']['data_base_name'];
	$data_base_user = $ini_array['Base_de_donnes']['data_base_user'];;
	$data_base_password = $ini_array['Base_de_donnes']['data_base_password'];
	$data_base_schema = $ini_array['Base_de_donnes']['data_base_schema'];
	$data_base_postgres = boolval($ini_array['Base_de_donnes']['data_base_postgres']);
	
	$date_last_upate = $ini_array['Parametres']['date_last_upate'];
	$winner_points = $ini_array['Parametres']['winner_points'];
	$score_points = $ini_array['Parametres']['score_points'];
	
	$uri_soccer_season = $ini_array['WebServices']['uri_soccer_season'];
	$uri_soccer_fixtures = $ini_array['WebServices']['uri_soccer_fixtures'];
	$api_key = $ini_array['WebServices']['api_key'];
	
	$version = $ini_array['Version']['version'];
	
	
?>