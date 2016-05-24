<?php


function update_date($date,$hour){
	
	$position_annee = strpos($date,'-');
	$annee=substr($date,0,$position_annee);
	$position_mois = strpos($date,'-',$position_annee+1);
	$mois=substr($date,$position_annee+1,$position_mois-$position_annee-1);
	$position_jour = strpos($date,'T',$position_mois+1);
	$jour=substr($date,$position_mois+1,$position_jour-$position_mois-1);
	$heure=substr($date,$position_jour+1,2);
	$heure = $heure+$hour;
	$dateupdate = $annee.'-'.$mois.'-'.$jour.'T'.$heure.substr($date,$position_jour+3);
	
	return $dateupdate;
}

/* Calcul Le nombre de points */
	  function calcul_points($id_utilisateur)
	  {
		   // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $winner_points, $score_points;
			
			$points = Array();
			$points['points'] = 0;
			$points['winners'] = 0;
			$points['scores'] = 0;
			
			 // Connexion, s�lection de la base de donn�es
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
			// On recup�re les param�tres de l'application
			if( $data_base_postgres ){
				$sql	= 'SELECT value FROM '.$data_base_schema.'."Parameters" ';
				$sql   .= "WHERE name='".$winner_points."'";
			}else{
				$sql	= 'SELECT value FROM Parameters ';
				$sql   .= "WHERE name='".$winner_points."'";
			}
			
			if( $data_base_postgres ){
				$data=pg_query($connexion,$sql);
			}else{
				$data=$connexion->query($sql);
			}
			
	
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$winnerPoints = $row['value'];
				}
			}else{
				if($row = $data->fetch())
				{
					$winnerPoints = $row['value'];
				}
			}
			
			if( $data_base_postgres ){
				$sql	= 'SELECT value FROM '.$data_base_schema.'."Parameters" ';
				$sql   .= "WHERE name='".$score_points."'";
			}else{
				$sql	= 'SELECT value FROM Parameters ';
				$sql   .= "WHERE name='".$score_points."'";
			}
			
			if( $data_base_postgres ){
				$data=pg_query($connexion,$sql);
			}else{
				$data=$connexion->query($sql);
			}
			
	
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$scorePoints = $row['value'];
				}
			}else{
				if($row = $data->fetch())
				{
					$scorePoints = $row['value'];
				}
			}
			
			// On calclul les points
			if( $data_base_postgres ){
				$sql    = 'SELECT SUM(points) AS points, SUM(winners) AS winners, SUM(scores) - SUM(winners) AS scores FROM (';
				$sql   .= ' SELECT CASE WHEN mat.n_goals_home_team = prono.n_goals_home_team AND mat.n_goals_away_team = prono.n_goals_away_team ';
				$sql   .= ' THEN '.$winnerPoints;
				$sql   .= ' ELSE ';
				$sql   .= ' CASE WHEN ( mat.n_goals_home_team > mat.n_goals_away_team AND prono.n_goals_home_team > prono.n_goals_away_team) ';
				$sql   .= ' OR';
				$sql   .= ' ( mat.n_goals_home_team < mat.n_goals_away_team AND prono.n_goals_home_team < prono.n_goals_away_team)';
				$sql   .= ' THEN '.$scorePoints;
				$sql   .= ' ELSE' ;
				$sql   .= ' CASE WHEN mat.n_goals_home_team = mat.n_goals_away_team AND prono.n_goals_home_team = prono.n_goals_away_team';
				$sql   .= ' THEN '.$scorePoints;
				$sql   .= ' ELSE 0';
				$sql   .= ' END';
				$sql   .= ' END';
				$sql   .= ' END as points ,';
				$sql   .= ' CASE WHEN mat.n_goals_home_team = prono.n_goals_home_team AND mat.n_goals_away_team = prono.n_goals_away_team THEN 1 ELSE 0 END AS winners,';
				$sql   .= '	CASE WHEN ( mat.n_goals_home_team > mat.n_goals_away_team AND prono.n_goals_home_team > prono.n_goals_away_team)';
				$sql   .= '	OR';
				$sql   .= '	( mat.n_goals_home_team < mat.n_goals_away_team AND prono.n_goals_home_team < prono.n_goals_away_team)';
				$sql   .= '	THEN 1';
				$sql   .= '	ELSE';
				$sql   .= '	CASE WHEN mat.n_goals_home_team = mat.n_goals_away_team AND prono.n_goals_home_team = prono.n_goals_away_team';
				$sql   .= '	THEN 1';
				$sql   .= '	ELSE 0';
				$sql   .= '	END';
				$sql   .= '	END AS scores';
				$sql   .= ' FROM '.$data_base_schema.'."Prognosis" prono';
				$sql   .= ' INNER JOIN '.$data_base_schema.'."Matches" mat on prono.n_id_match = mat.n_id_match';
				$sql   .= ' AND prono.n_id_user = '.$id_utilisateur;
				$sql   .= ' AND mat.n_goals_home_team IS NOT NULL  ';
				$sql   .= ' AND mat.n_goals_away_team IS NOT NULL ) AS req ';

			}else{
				$sql    = 'SELECT SUM(points) AS points , SUM(winners) AS winners, SUM(scores) - SUM(winners) AS scores FROM (';
				$sql   .= ' SELECT ';
				$sql   .= ' CASE WHEN mat.n_goals_home_team = prono.n_goals_home_team AND mat.n_goals_away_team = prono.n_goals_away_team ';
				$sql   .= ' THEN '.$winnerPoints;
				$sql   .= ' ELSE ';
				$sql   .= '	CASE WHEN 	( mat.n_goals_home_team > mat.n_goals_away_team AND prono.n_goals_home_team > prono.n_goals_away_team) ';
				$sql   .= '	OR';
				$sql   .= '	( mat.n_goals_home_team < mat.n_goals_away_team AND prono.n_goals_home_team < prono.n_goals_away_team)';
				$sql   .= '	THEN '.$scorePoints; 
				$sql   .= '	ELSE ';
				$sql   .= '	CASE WHEN mat.n_goals_home_team = mat.n_goals_away_team AND prono.n_goals_home_team = prono.n_goals_away_team';
				$sql   .= '	THEN '.$scorePoints;
				$sql   .= '	ELSE 0';
				$sql   .= '	END';
				$sql   .= '	END';
				$sql   .= '	END as points ,';
				$sql   .= ' CASE WHEN mat.n_goals_home_team = prono.n_goals_home_team AND mat.n_goals_away_team = prono.n_goals_away_team THEN 1 ELSE 0 END AS winners,';
				$sql   .= '	CASE WHEN ( mat.n_goals_home_team > mat.n_goals_away_team AND prono.n_goals_home_team > prono.n_goals_away_team)';
				$sql   .= '	OR';
				$sql   .= '	( mat.n_goals_home_team < mat.n_goals_away_team AND prono.n_goals_home_team < prono.n_goals_away_team)';
				$sql   .= '	THEN 1';
				$sql   .= '	ELSE';
				$sql   .= '	CASE WHEN mat.n_goals_home_team = mat.n_goals_away_team AND prono.n_goals_home_team = prono.n_goals_away_team';
				$sql   .= '	THEN 1';
				$sql   .= '	ELSE 0';
				$sql   .= '	END';
				$sql   .= '	END AS scores';
				$sql   .= ' FROM Prognosis prono';
				$sql   .= ' INNER JOIN Matches mat on prono.n_id_match = mat.n_id_match';
				$sql   .= ' AND prono.n_id_user = '.$id_utilisateur;
				$sql   .= ' AND mat.n_goals_home_team IS NOT NULL  ';
				$sql   .= ' AND mat.n_goals_away_team IS NOT NULL) AS req';
			}
									
			if( $data_base_postgres ){
				$data=pg_query($connexion,$sql);
			}else{
				$data=$connexion->query($sql);
			}
						
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					if( is_numeric($row['points']) && is_numeric($row['winners']) && is_numeric($row['scores']) )
					{
						$points['points'] = $row['points'];
						$points['winners'] = $row['winners'];
						$points['scores'] = $row['scores'];
					}
				}
			}else{
				if($row = $data->fetch()){
					if( is_numeric($row['points']) && is_numeric($row['winners']) && is_numeric($row['scores']) )
					{
						$points['points'] = $row['points'];
						$points['winners'] = $row['winners'];
						$points['scores'] = $row['scores'];
					}
				}
			}
			
		   // On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $points;
	  }

/* Mettre � jour la liste des matchs */
	  function mise_a_jour_bdd()
	  {	  
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version, $uri_soccer_season, $uri_soccer_fixtures, $api_key,$api_access_key;
		  
		  // Connexion, s�lection de la base de donn�es
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
		  // On v�rifie la connecxion � la base de donn�es
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Probl�me de connection � la base de donn�es";
				
				return $resultat;
			}
		   
		  // On recupere la date de derni�re mise � jour du WebServices 
			$uri = $uri_soccer_season;
			$reqPrefs['http']['method'] = 'GET';
			$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
			$stream_context = stream_context_create($reqPrefs);
			$response = file_get_contents($uri, false, $stream_context);
			$soccerseasons = json_decode($response,true);
			$dateLastUpdate = $soccerseasons['lastUpdated'];
			
			// On recupere la date de derni�re mise � jour de la base de donn�es
			if( $data_base_postgres ){
				$sql	= 'SELECT value FROM '.$data_base_schema.'."Parameters" ';
				$sql   .= "WHERE name='".$date_last_upate."'";
			}else{
				$sql	= 'SELECT value FROM Parameters ';
				$sql   .= "WHERE name='".$date_last_upate."'";
			}
			
			if( $data_base_postgres ){
				$data=pg_query($connexion,$sql);
			}else{
				$data=$connexion->query($sql);
			}
			
			// On v�rifie l'�xecution de la requ�te SQL
			if(!$data){
				$resultat['status'] = false;
				$resultat['message'] = "Erreur requ�te SQL";
				
				return $resultat;
			}
					
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$dateLastUpdateBdd = $row['value'];
				}
			}else{
				if($row = $data->fetch())
				{
					$dateLastUpdateBdd = $row['value'];
				}
			}
			// On met � jour la liste des matchs
			if( empty($dateLastUpdateBdd) || $dateLastUpdate > $dateLastUpdateBdd )
			{				
				$uri = $uri_soccer_fixtures;
				$reqPrefs['http']['method'] = 'GET';
				$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
				$stream_context = stream_context_create($reqPrefs);
				$response = file_get_contents($uri, false, $stream_context);
				$fixtures = json_decode($response,true);
			  
				// On r�cup�re le nombre de matchs
				$nb_matchs = $fixtures['count'];
				
				$team = Array();
						
				for($i = 0; $i < $nb_matchs; $i++)
				{
					// On r�cup�re les informations du WebServices
					$href = $fixtures['fixtures'][$i]['_links']['self']['href'];
					$id = substr($href,strripos($href,'/')+1);
					$date = $fixtures['fixtures'][$i]['date'];
					$date=update_date($date,2);
					$matchday = $fixtures['fixtures'][$i]['matchday'];
					$homeTeamName = $fixtures['fixtures'][$i]['homeTeamName'];
					$awayTeamName = $fixtures['fixtures'][$i]['awayTeamName'];
					$goalsHomeTeam = $fixtures['fixtures'][$i]['result']['goalsHomeTeam'];
					$goalsAwayTeam = $fixtures['fixtures'][$i]['result']['goalsAwayTeam'];
					
					// On récupère les images
					// homeTeam
					if( !array_key_exists($homeTeamName,$team)){
						$uri = $fixtures['fixtures'][$i]['_links']['homeTeam']['href'];
						$reqPrefs['http']['method'] = 'GET';
						$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
						$stream_context = stream_context_create($reqPrefs);
						$response = file_get_contents($uri, false, $stream_context);
						$team[$homeTeamName] = json_decode($response,true);
					}
					
					//awayTeamName
					if( !array_key_exists($awayTeamName,$team)){
						$uri = $fixtures['fixtures'][$i]['_links']['awayTeam']['href'];
						$reqPrefs['http']['method'] = 'GET';
						$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
						$stream_context = stream_context_create($reqPrefs);
						$response = file_get_contents($uri, false, $stream_context);
						$team[$awayTeamName] = json_decode($response,true);
					}
										
					// On v�rifie si le match est en base de donn�es
					if( $data_base_postgres ){
						$sql	= 'SELECT n_id_match FROM '.$data_base_schema.'."Matches" ';
						$sql   .= 'WHERE n_id_match='.$id;
					}else{
						$sql	= 'SELECT n_id_match FROM Matches ';
						$sql   .= 'WHERE n_id_match='.$id;
					}
					
					if( $data_base_postgres ){
						$data=pg_query($connexion,$sql);
					}else{
						$data=$connexion->query($sql);
					}
					
					// On v�rifie l'�xecution de la requ�te SQL
					if(!$data){
						$resultat['status'] = false;
						$resultat['message'] = "Erreur requ�te SQL";
						
						return $resultat;
					}
					
					// On v�rifie si le match est d�j� en base de donn�es
					if( $data_base_postgres ){
						if(pg_fetch_array($data, null, PGSQL_ASSOC))
						{
							// On met � jour si le score est renseign�
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){								
								$sql	= 'UPDATE '.$data_base_schema.'."Matches" ';
								$sql   .= 'SET n_goals_home_team= '.$goalsHomeTeam.' , n_goals_away_team= '.$goalsAwayTeam.' ';
								$sql   .= " WHERE n_id_match = ".$id;
						
								pg_query($connexion,$sql);				
							}
						}else{
							
							$sql	= 'INSERT INTO '.$data_base_schema.'."Matches" ';
							$sql   .= '( n_id_match, d_date, n_matchday, a_home_team_name, a_away_team_name ';
							
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){						
								$sql   .= ', n_goals_home_team, n_goals_away_team,';
							}
							$sql   .= ',a_home_team_href,a_away_team_href)';
							$sql   .= " VALUES ( ".$id.", '".$date."', '".$matchday."', '".$homeTeamName."', '".$awayTeamName."'";
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$sql   .= ', '.$goalsHomeTeam.', '.$goalsAwayTeam;
							}
							$sql   .= ",'".$team[$homeTeamName]['crestUrl']."', '".$team[$awayTeamName]['crestUrl']."')";
							pg_query($connexion,$sql);						
						}				
					}else{
						if($data->fetch())
						{
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								
								$sql 	= 'UPDATE Matches ';
								$sql   .= " SET n_goals_home_team  = :goalsHomeTeam ,";
								$sql   .= " n_goals_away_team  =  :goalsAwayTeam " ;
								$sql   .= ' WHERE n_id_match = : :id_match';
								
								$prepare = $connexion->prepare($sql);
								$prepare->bindParam(':goalsHomeTeam', $goalsHomeTeam, PDO::PARAM_INT);
								$prepare->bindParam(':goalsAwayTeam', $goalsAwayTeam, PDO::PARAM_INT);
								$prepare->bindParam(':id_match', $id, PDO::PARAM_INT);
								$res = $prepare ->execute();
														
							}
						}else{					
							$sql	= 'INSERT INTO Matches ';
							$sql   .= '( n_id_match, d_date, n_matchday, a_home_team_name, a_away_team_name';
							
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){						
								$sql   .= ', n_goals_home_team, n_goals_away_team ,';
							}
							$sql   .= ', a_home_team_href,a_away_team_href)';
							
							$sql   .= " VALUES ( :idMatch, :date, :matchday, :homeTeamName, :awayTeamName";
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$sql   .= ', :goalsHomeTeam, :goalsAwayTeam';
							}
							
							$sql   .= ', :homeTeamHref, :awayTeamHref )';
											
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':idMatch', $id, PDO::PARAM_INT);
							$prepare->bindParam(':date', $date, PDO::PARAM_STR,50);
							$prepare->bindParam(':matchday', $matchday, PDO::PARAM_INT);
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$prepare->bindParam(':goalsHomeTeam', $goalsHomeTeam, PDO::PARAM_INT);
								$prepare->bindParam(':goalsAwayTeam', $goalsAwayTeam, PDO::PARAM_INT);
							}
							$prepare->bindParam(':homeTeamName', $goalsHomeTeam, PDO::PARAM_STR,50);
							$prepare->bindParam(':awayTeamName', $awayTeamName, PDO::PARAM_STR,50);
							$prepare->bindParam(':homeTeamHref', $team[$homeTeamName]['crestUrl'], PDO::PARAM_STR,50);
							$prepare->bindParam(':awayTeamHref', $team[$awayTeamName]['crestUrl'], PDO::PARAM_STR,50);
							$res = $prepare ->execute();
						}			
					}
				}

				// Mise � jour du parametre de la date de derni�re mise � jour
				if( $data_base_postgres ){
					$sql	= 'UPDATE '.$data_base_schema.'."Parameters" ';
					$sql   .= "SET value='".$dateLastUpdate."'";
					$sql   .= " WHERE name='".$date_last_upate."'";
					
					pg_query($connexion,$sql);					
					
				}else{
					$sql	= 'UPDATE Parameters ';
					$sql   .= "SET value= :dateLastUpdate ";
					$sql   .= " WHERE name= :date_last_upate";
					
					$prepare = $connexion->prepare($sql);
					$prepare->bindParam(':dateLastUpdate', $dateLastUpdate, PDO::PARAM_STR,50);
					$prepare->bindParam(':date_last_upate', $date_last_upate, PDO::PARAM_STR,50);
					$res = $prepare ->execute();
				}
				
			}
			
			// On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
	  }

?>