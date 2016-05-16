<?php

/* Mettre à jour la liste des matchs */
	  function mise_a_jour_bdd()
	  {	  
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version, $uri_soccer_season, $uri_soccer_fixtures, $api_key;
		  
		  // Connexion, sélection de la base de données
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
		  // On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
		   
		  // On recupere la date de dernière mise à jour du WebServices 
			$uri = $uri_soccer_season;
			$reqPrefs['http']['method'] = 'GET';
			$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
			$stream_context = stream_context_create($reqPrefs);
			$response = file_get_contents($uri, false, $stream_context);
			$soccerseasons = json_decode($response,true);
			$dateLastUpdate = $soccerseasons['lastUpdated'];
			
			// On recupere la date de dernière mise à jour de la base de données
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
			
			// On vérifie l'éxecution de la requête SQL
			if(!$data){
				$resultat['status'] = false;
				$resultat['message'] = "Erreur requête SQL";
				
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
			// On met à jour la liste des matchs
			if( empty($dateLastUpdateBdd) || $dateLastUpdate > $dateLastUpdateBdd )
			{				
				$uri = $uri_soccer_fixtures;
				$reqPrefs['http']['method'] = 'GET';
				$reqPrefs['http']['header'] = 'X-Auth-Token: '.$api_key;
				$stream_context = stream_context_create($reqPrefs);
				$response = file_get_contents($uri, false, $stream_context);
				$fixtures = json_decode($response,true);
			  
				// On récupère le nombre de matchs
				$nb_matchs = $fixtures['count'];
						
				for($i = 0; $i < $nb_matchs; $i++)
				{
					// On récupère les informations du WebServices
					$href = $fixtures['fixtures'][$i]['_links']['self']['href'];
					$id = substr($href,strripos($href,'/')+1);
					$date = $fixtures['fixtures'][$i]['date'];
					$matchday = $fixtures['fixtures'][$i]['matchday'];
					$homeTeamName = $fixtures['fixtures'][$i]['homeTeamName'];
					$awayTeamName = $fixtures['fixtures'][$i]['awayTeamName'];
					$goalsHomeTeam = $fixtures['fixtures'][$i]['result']['goalsHomeTeam'];
					$goalsAwayTeam = $fixtures['fixtures'][$i]['result']['goalsAwayTeam'];
					
					// On vérifie si le match est en base de données
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
					
					// On vérifie l'éxecution de la requête SQL
					if(!$data){
						$resultat['status'] = false;
						$resultat['message'] = "Erreur requête SQL";
						
						return $resultat;
					}
					
					// On vérifie si le match est déjà en base de données
					if( $data_base_postgres ){
						if(pg_fetch_array($data, null, PGSQL_ASSOC))
						{
							// On met à jour si le score est renseigné
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){								
								$sql	= 'UPDATE '.$data_base_schema.'."Matches" ';
								$sql   .= 'SET n_goals_home_team= '.$goalsHomeTeam.' , n_goals_away_team= '.$goalsAwayTeam.' ';
								$sql   .= " WHERE n_id_match = ".$id;
						
								pg_query($connexion,$sql);				
							}
						}else{
							
							$sql	= 'INSERT INTO '.$data_base_schema.'."Matches" ';
							$sql   .= '( n_id_match, d_date, n_matchday, a_home_team_name, a_away_team_name';
							
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){						
								$sql   .= ', n_goals_home_team, n_goals_away_team )';
							}else{
								$sql   .= ')';
							}
							$sql   .= " VALUES ( ".$id.", '".$date."', '".$matchday."', '".$homeTeamName."', '".$awayTeamName."'";
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$sql   .= ', '.$goalsHomeTeam.', '.$goalsAwayTeam.' )';
							}else{
								$sql   .= ')';
							}
							
							pg_query($connexion,$sql);
						}				
					}else{
						if($data->fetch())
						{
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								
								$sql 	= 'UPDATE Matches ';
								$sql   .= " SET n_goals_home_team  = :goalsHomeTeam ,";
								$sql   .= " n_goals_away_team  =  :goalsAwayTeam " ;
								$sql   .= ' WHERE n_id_match = : :id_match'.$id;
								
								$prepare = $connexion->prepare($sql);
								$prepare->bindParam(':goalsHomeTeam', $goalsHomeTeam, PDO::PARAM_INT);
								$prepare->bindParam(':goalsAwayTeam', $goalsAwayTeam, PDO::PARAM_INT);
								$prepare->bindParam(':id_match', $id, PDO::PARAM_INT);
								$res = $prepare ->execute();							
							}
						}else{					
							$sql	= 'INSERT INTO '.$data_base_schema.'."Matches" ';
							$sql   .= '( n_id_match, d_date, n_matchday, a_home_team_name, a_away_team_name';
							
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){						
								$sql   .= ', n_goals_home_team, n_goals_away_team )';
							}else{
								$sql   .= ')';
							}
							$sql   .= " VALUES ( :idMatch, :date, :matchday";
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$sql   .= ', :homeTeamName, :awayTeamName )';
							}else{
								$sql   .= ')';
							}
							
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':idMatch', $goalsHomeTeam, PDO::PARAM_INT);
							$prepare->bindParam(':date', $goalsAwayTeam, PDO::PARAM_STR,50);
							$prepare->bindParam(':matchday', $id, PDO::PARAM_INT);
							if( !empty($goalsHomeTeam) && !empty($goalsAwayTeam) ){
								$prepare->bindParam(':homeTeamName', $goalsHomeTeam, PDO::PARAM_STR,50);
								$prepare->bindParam(':awayTeamName', $goalsHomeTeam, PDO::PARAM_STR,50);
							}
							$res = $prepare ->execute();
						}				
					}
				}

				// Mise à jour du parametre de la date de dernière mise à jour
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

/* Recupere la liste des pronostics */
	  function get_pronostics($id_user)
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version, $uri_soccer_season, $uri_soccer_fixtures, $api_key;
		  
		  // Préparation de la réponse	
			$resultat = Array();
		  
		  // Connexion, sélection de la base de données
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
		  // On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
			
			// Mise à jour des matches en base de données
			mise_a_jour_bdd();

		  // On recupère les pronostics de l'utilisateur
			if( $data_base_postgres ){
				$sql	= "SELECT pronostic.n_id_prognosis, match.n_id_match, d_date, a_home_team_name, pronostic.n_goals_away_team, a_away_team_name , pronostic.n_goals_home_team , case when current_timestamp < to_timestamp(replace(replace(d_date,'T',' '),'Z',''), 'YYYY-MM-DD HH24:MI:SS') then 'O' else 'N' end as available "; 
				$sql   .= 'FROM (SELECT n_id_prognosis, n_id_user, n_id_match, n_goals_away_team,n_goals_home_team  FROM '.$data_base_schema.'."Prognosis" WHERE n_id_user ='.$id_user.') pronostic ';
				$sql   .= 'RIGHT JOIN '.$data_base_schema.'."Matches" match on match.n_id_match = pronostic.n_id_match';
			}else{
				$sql	= "SELECT pronostic.n_id_prognosis, Matches.n_id_match, d_date, a_home_team_name, pronostic.n_goals_away_team, a_away_team_name , pronostic.n_goals_home_team , case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end as available "; 
				$sql   .= 'FROM (SELECT n_id_prognosis, n_id_user, n_id_match, n_goals_away_team,n_goals_home_team  FROM Prognosis WHERE n_id_user ='.$id_user.') pronostic ';
				$sql   .= 'RIGHT JOIN Matches on Matches.n_id_match = pronostic.n_id_match';
			}
		  
			if( $data_base_postgres ){
				$data=pg_query($connexion,$sql);
			}else{
				$data=$connexion->query($sql);
			}
			
			// On vérifie l'éxecution de la requête SQL
			if(!$data){
				$resultat['status'] = false;
				$resultat['message'] = "Erreur requête SQL";
				
				return $resultat;
			}
			
			$pronostics=Array();
			if( $data_base_postgres ){
				while($pro = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$res=Array();
					$res['idPrognosis'] = $pro['n_id_prognosis'];
					$res['idMatch'] = $pro['n_id_match'];
					$res['date'] = $pro['d_date'];
					$res['homeTeamName'] = $pro['a_home_team_name'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['available'] = $pro['available'];
					$pronostics[] = $res;
				 }
			}else{
				while($pro = $data->fetch())
				{
					$res=Array();
					$res['idPrognosis'] = $pro['n_id_prognosis'];
					$res['idMatch'] = $pro['n_id_match'];
					$res['date'] = $pro['d_date'];
					$res['homeTeamName'] = $pro['a_home_team_name'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['available'] = $pro['available'];
					$pronostics[] = $res;
				}
			}
			
			$resultat['status'] = true;
			$resultat['pronostics'] = $pronostics;
			
		  // On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $resultat;
	  }
	 
	/* Enregistrer les pronostics */	 
	function save_prognosis($pronostics)
	 {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
		  
		  // Préparation de la réponse	
			$resultat = Array();
			$resultat['status'] = true;			
			$resultat['change'] = 0;
			$resultat['unchange'] = 0;			
		  
		  // Connexion, sélection de la base de données
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
			// On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
						
			$id_utilisateur = $pronostics['id_user'];

			// On parcours les pronostics reçus
			foreach ( $pronostics['pronostics'] as $prono )
			{
				$id_match = $prono['id_match'];
				$goals_away_team = $prono['goalsAwayTeam'];
				$goals_home_team = $prono['goalsHomeTeam'];
				
				// On vérfie la validite
				if( $data_base_postgres ){		
					$sql 	= "SELECT case when current_timestamp < to_timestamp(replace(replace(d_date,'T',' '),'Z',''), 'YYYY-MM-DD HH24:MI:SS') then 'O' else 'N' end as available FROM ".$data_base_schema.".\"Matches\" ";
					$sql   .= " WHERE n_id_match = ".$id_match;
				}else{
					$sql 	= "SELECT case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end as available FROM Matches";
					$sql   .= " WHERE n_id_match = ".$id_match;
				}
				
				// Execution de la requête SQL
				if( $data_base_postgres ){
					$data=pg_query($connexion,$sql);
				}else{
					$data=$connexion->query($sql);
				}
				
				// On vérifie l'éxecution de la requête SQL
				if(!$data){
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requête SQL";
					
					return $resultat;
				}
				
				if( $data_base_postgres ){
					if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
					{
						$available = $row['available'];						
					}
				}else{
					if($row = $data->fetch()){
						$available = $row['available'];	
					}
				}
				if( $available == 'O' ){
					
					// Création de la requête SQL
					if( $data_base_postgres ){
						$sql 	= 'SELECT n_id_prognosis FROM '.$data_base_schema.'."Prognosis" ';
						$sql   .= " WHERE n_id_user = ".$id_utilisateur;
						$sql   .= " AND n_id_match = ".$id_match;
					}else{
						$sql 	= 'SELECT n_id_prognosis FROM Prognosis ';
						$sql   .= " WHERE n_id_user = ".$id_utilisateur;
						$sql   .= " AND n_id_match = ".$id_match;
					}
					
					// Execution de la requête SQL
					if( $data_base_postgres ){
						$data=pg_query($connexion,$sql);
					}else{
						$data=$connexion->query($sql);
					}
					// On vérifie l'éxecution de la requête SQL
					if(!$data){
						$resultat['status'] = false;
						$resultat['message'] = "Erreur requête SQL";
						
						return $resultat;
					}
					
					if( $data_base_postgres ){
						if($row = pg_fetch_array($data, null, PGSQL_ASSOC)){
							$sql 	= 'UPDATE '.$data_base_schema.'."Prognosis" ';
							$sql   .= " SET n_goals_away_team = ".$goals_away_team.",";
							$sql   .= " n_goals_home_team = ".$goals_home_team;
							$sql   .= " WHERE n_id_user = ".$id_utilisateur." ";
							$sql   .= " AND n_id_match = ".$id_match;
						}else{
							$sql 	= 'INSERT INTO '.$data_base_schema.'."Prognosis" ';
							$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team) ";
							$sql   .= " VALUES(nextval('".$data_base_schema.".\"Prognosis_sequence\"'), ".$id_utilisateur.",".$id_match.",".$goals_away_team.",".$goals_home_team.") ";
						}

						$res = pg_query($connexion,$sql);
						$count=pg_affected_rows ($res);
						$resultat['change'] += $count;
						
					}else{
						if($row = $data->fetch()){
							$sql 	= 'UPDATE Prognosis ';
							$sql   .= " SET n_goals_away_team = :goalsAwayTeam ,";
							$sql   .= " n_goals_home_team = :goalsHomeTeam ";
							$sql   .= " WHERE n_id_user = :idUtilisateur";
							$sql   .= " AND n_id_match = :idMatch";
							
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':goalsAwayTeam', $goals_away_team, PDO::PARAM_INT);
							$prepare->bindParam(':goalsHomeTeam', $goals_home_team, PDO::PARAM_INT);
							$prepare->bindParam(':idUtilisateur', $id_utilisateur, PDO::PARAM_INT);
							$prepare->bindParam(':idMatch', $id_match, PDO::PARAM_INT);
							$prepare ->execute();
							
						}else{
							
							// On recupère le prochain identifiant disponible
							$sql	= 'SELECT MAX(n_id_prognosis) AS max_id FROM Prognosis ';
							
							$data=$connexion->query($sql);
							
							// On vérifie l'éxecution de la requête SQL
							if(!$data){
								$resultat['status'] = false;
								$resultat['message'] = "Erreur requête SQL";
								
								return $resultat;
							}
							
							$id_pronostic = 0;
							if( $row = $data->fetch() )
							{
								$id_pronostic = $row['max_id'];
							}
							$id_pronostic = $id_pronostic + 1;
							
							$sql 	= 'INSERT INTO Prognosis';
							$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team) ";
							$sql   .= " VALUES(:idPrognosis , :idUtilisateur , :idMatch , :goalsAwayTeam , :goalsHomeTeam) ";
													
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':idPrognosis', $id_pronostic, PDO::PARAM_INT);
							$prepare->bindParam(':idUtilisateur', $id_utilisateur, PDO::PARAM_INT);
							$prepare->bindParam(':idMatch', $id_match, PDO::PARAM_INT);
							$prepare->bindParam(':goalsAwayTeam', $goals_away_team, PDO::PARAM_INT);
							$prepare->bindParam(':goalsHomeTeam', $goals_home_team, PDO::PARAM_INT);
							$prepare ->execute();
						}
						
						$count = $prepare->rowCount();
						$resultat['change'] += $count;
					}
				}else{
					$resultat['unchange'] += 1;
				}
			}
		
		return $resultat;
	 }

?>
