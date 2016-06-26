<?php

/* Recupere la liste des pronostics */
	  function get_pronostics($id_user)
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version, $uri_soccer_season, $uri_soccer_fixtures, $api_key;
		  
		  // Pr�paration de la r�ponse	
			$resultat = Array();
		  
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
			
			// Mise � jour des matches en base de donn�es
			mise_a_jour_bdd();

		  // On recup�re les pronostics de l'utilisateur
			if( $data_base_postgres ){
				$sql	= "SELECT pronostic.n_id_prognosis, n_matchday,a_winner,match.n_id_match, d_date, a_home_team_name, a_home_team_href, a_away_team_href, pronostic.n_goals_away_team, a_away_team_name , pronostic.n_goals_home_team , case when current_timestamp < to_timestamp(replace(replace(d_date,'T',' '),'Z',''), 'YYYY-MM-DD HH24:MI:SS') then 'O' else 'N' end as available "; 
				$sql   .= 'FROM (SELECT n_id_prognosis, a_winner,n_id_user, n_id_match, n_goals_away_team,n_goals_home_team  FROM '.$data_base_schema.'."Prognosis" WHERE n_id_user ='.$id_user.') pronostic ';
				$sql   .= 'RIGHT JOIN '.$data_base_schema.'."Matches" match on match.n_id_match = pronostic.n_id_match HAVING available = \'O\'';
			}else{
				$sql	= "SELECT pronostic.n_id_prognosis, n_matchday, a_winner, Matches.n_id_match, d_date, a_home_team_name, a_home_team_href, a_away_team_href, pronostic.n_goals_away_team, a_away_team_name , pronostic.n_goals_home_team , case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) = 0 then  case when SUBSTRING( SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) FROM 1 FOR POSITION(  ':' IN SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) ) -1 ) +1 - extract(HOUR from STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''),'%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end else 'N' end end as available "; 
				$sql   .= 'FROM (SELECT n_id_prognosis, a_winner,n_id_user, n_id_match, n_goals_away_team,n_goals_home_team  FROM Prognosis WHERE n_id_user ='.$id_user.') pronostic ';
				$sql   .= "RIGHT JOIN Matches on Matches.n_id_match = pronostic.n_id_match HAVING available = 'O'";
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
			
			$pronostics=Array();
			if( $data_base_postgres ){
				while($pro = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$res=Array();
					$res['idPrognosis'] = $pro['n_id_prognosis'];
					$res['idMatch'] = $pro['n_id_match'];
					$res['date'] = $pro['d_date'];
					$res['homeTeamName'] = $pro['a_home_team_name'];
					$res['homeTeamHref'] = $pro['a_home_team_href'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['awayTeamHref'] = $pro['a_away_team_href'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['available'] = $pro['available'];
					$res['matchday'] = $pro['n_matchday'];
					$res['winner'] = $pro['a_winner'];
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
					$res['homeTeamHref'] = $pro['a_home_team_href'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['awayTeamHref'] = $pro['a_away_team_href'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['available'] = $pro['available'];
					$res['matchday'] = $pro['n_matchday'];
					$res['winner'] = $pro['a_winner'];
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
		  
		  // Pr�paration de la r�ponse	
			$resultat = Array();
			$resultat['status'] = true;			
			$resultat['change'] = 0;
			$resultat['unchange'] = 0;			
		  
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
						
			$id_utilisateur = $pronostics['id_user'];

			// On parcours les pronostics re�us
			foreach ( $pronostics['pronostics'] as $prono )
			{
				$id_match = $prono['id_match'];
				$goals_away_team = $prono['goalsAwayTeam'];
				$goals_home_team = $prono['goalsHomeTeam'];
				$winner = $prono['winner'];
				
				// On v�rfie la validite
				if( $data_base_postgres ){		
					$sql 	= "SELECT case when current_timestamp < to_timestamp(replace(replace(d_date,'T',' '),'Z',''), 'YYYY-MM-DD HH24:MI:SS') then 'O' else 'N' end as available FROM ".$data_base_schema.".\"Matches\" ";
					$sql   .= " WHERE n_id_match = ".$id_match;
				}else{
					$sql 	= "SELECT case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) = 0 then  case when SUBSTRING( SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) FROM 1 FOR POSITION(  ':' IN SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) ) -1 ) +1 - extract(HOUR from STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''),'%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end else 'N' end end as available FROM Matches";
					$sql   .= " WHERE n_id_match = ".$id_match;
				}
				
				// Execution de la requ�te SQL
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
						$available = $row['available'];						
					}
				}else{
					if($row = $data->fetch()){
						$available = $row['available'];	
					}
				}
				if( $available == 'O' ){
					
					// Cr�ation de la requ�te SQL
					if( $data_base_postgres ){
						$sql 	= 'SELECT n_id_prognosis FROM '.$data_base_schema.'."Prognosis" ';
						$sql   .= " WHERE n_id_user = ".$id_utilisateur;
						$sql   .= " AND n_id_match = ".$id_match;
					}else{
						$sql 	= 'SELECT n_id_prognosis FROM Prognosis ';
						$sql   .= " WHERE n_id_user = ".$id_utilisateur;
						$sql   .= " AND n_id_match = ".$id_match;
					}
					
					// Execution de la requ�te SQL
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
						if($row = pg_fetch_array($data, null, PGSQL_ASSOC)){
							$sql 	= 'UPDATE '.$data_base_schema.'."Prognosis" ';
							$sql   .= " SET n_goals_away_team = ".$goals_away_team.",";
							$sql   .= " n_goals_home_team = ".$goals_home_team;
							if($winner){
								$sql   .= " ,a_winner = ".$winner;
							}
							$sql   .= " WHERE n_id_user = ".$id_utilisateur." ";
							$sql   .= " AND n_id_match = ".$id_match;
						}else{
							$sql 	= 'INSERT INTO '.$data_base_schema.'."Prognosis" ';
							if($winner){
								$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team,a_winner) ";
								$sql   .= " VALUES(nextval('".$data_base_schema.".\"Prognosis_sequence\"'), ".$id_utilisateur.",".$id_match.",".$goals_away_team.",".$goals_home_team.",'".$winner."') ";
							}else{
								$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team) ";
								$sql   .= " VALUES(nextval('".$data_base_schema.".\"Prognosis_sequence\"'), ".$id_utilisateur.",".$id_match.",".$goals_away_team.",".$goals_home_team.") ";
							}
						}

						$res = pg_query($connexion,$sql);
						$count=pg_affected_rows ($res);
						$resultat['change'] += $count;
						
					}else{
						if($row = $data->fetch()){
							$sql 	= 'UPDATE Prognosis ';
							$sql   .= " SET n_goals_away_team = :goalsAwayTeam ,";
							$sql   .= " n_goals_home_team = :goalsHomeTeam ";
							if($winner){
								$sql   .= " ,a_winner = :winner";
							}
							$sql   .= " WHERE n_id_user = :idUtilisateur";
							$sql   .= " AND n_id_match = :idMatch";
							
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':goalsAwayTeam', $goals_away_team, PDO::PARAM_INT);
							$prepare->bindParam(':goalsHomeTeam', $goals_home_team, PDO::PARAM_INT);
							$prepare->bindParam(':idUtilisateur', $id_utilisateur, PDO::PARAM_INT);
							$prepare->bindParam(':idMatch', $id_match, PDO::PARAM_INT);
							if($winner){
								$prepare->bindParam(':winner', $winner, PDO::PARAM_STR,50);
							}
							$prepare ->execute();
							
						}else{
							
							// On recup�re le prochain identifiant disponible
							$sql	= 'SELECT MAX(n_id_prognosis) AS max_id FROM Prognosis ';
							
							$data=$connexion->query($sql);
							
							// On v�rifie l'�xecution de la requ�te SQL
							if(!$data){
								$resultat['status'] = false;
								$resultat['message'] = "Erreur requ�te SQL";
								
								return $resultat;
							}
							
							$id_pronostic = 0;
							if( $row = $data->fetch() ){
								$id_pronostic = $row['max_id'];
							}
							$id_pronostic = $id_pronostic + 1;
							
							$sql 	= 'INSERT INTO Prognosis';
							if($winner){
								$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team,a_winner) ";
								$sql   .= " VALUES(:idPrognosis , :idUtilisateur , :idMatch , :goalsAwayTeam , :goalsHomeTeam, :winner) ";
							}else{
								$sql   .= " (n_id_prognosis, n_id_user,n_id_match,n_goals_away_team,n_goals_home_team) ";
								$sql   .= " VALUES(:idPrognosis , :idUtilisateur , :idMatch , :goalsAwayTeam , :goalsHomeTeam) ";
							}
													
							$prepare = $connexion->prepare($sql);
							$prepare->bindParam(':idPrognosis', $id_pronostic, PDO::PARAM_INT);
							$prepare->bindParam(':idUtilisateur', $id_utilisateur, PDO::PARAM_INT);
							$prepare->bindParam(':idMatch', $id_match, PDO::PARAM_INT);
							$prepare->bindParam(':goalsAwayTeam', $goals_away_team, PDO::PARAM_INT);
							$prepare->bindParam(':goalsHomeTeam', $goals_home_team, PDO::PARAM_INT);
							if($winner){
								$prepare->bindParam(':winner', $winner, PDO::PARAM_STR,50);
							}
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
	  
	 function get_all_pronostics()
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version, $uri_soccer_season, $uri_soccer_fixtures, $api_key,$winner_points, $score_points;
		  
		  // Pr�paration de la r�ponse	
			$resultat = Array();
		  
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
			
			// Mise � jour des matches en base de donn�es
			mise_a_jour_bdd();

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

		  // On recup�re les pronostics de l'utilisateur
			if( $data_base_postgres ){
				$sql	= "SELECT req.*, CASE WHEN score_home = n_goals_home_team AND score_away = n_goals_away_team THEN CASE WHEN n_matchday > 3 THEN ".$winnerPoints." * (n_matchday - 1) ELSE ".$winnerPoints." END ELSE CASE WHEN ( score_home > score_away AND n_goals_home_team > n_goals_away_team) OR ( score_home < score_away AND n_goals_home_team < n_goals_away_team) THEN CASE WHEN n_matchday > 3 THEN ".$scorePoints." * (n_matchday - 1) ELSE ".$scorePoints." END ELSE CASE WHEN score_home = score_away AND n_goals_home_team = n_goals_away_team THEN CASE WHEN n_matchday > 3 THEN ".$scorePoints." * (n_matchday - 1) ELSE ".$scorePoints." END ELSE 0 END END END as points FROM ( SELECT n_matchday,Prognosis.n_id_user,Prognosis.n_id_match,Prognosis.n_goals_away_team,Prognosis.n_goals_home_team,a_home_team_name,a_away_team_name,Matches.n_goals_home_team AS score_home, Matches.n_goals_away_team AS score_away,d_date,a_home_team_href,a_away_team_href, a_path, a_pseudonyme FROM Prognosis INNER JOIN Matches ON Prognosis.n_id_match = Matches.n_id_match INNER JOIN Users ON Prognosis.n_id_user = Users.n_id_user INNER JOIN Avatars ON Users.n_id_avatar = Avatars.n_id_avatar and Prognosis.n_id_match in (SELECT n_id_match FROM Matches where case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) = 0 then  case when SUBSTRING( SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) FROM 1 FOR POSITION(  ':' IN SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) ) -1 ) +1 - extract(HOUR from STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''),'%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end else 'N' end end = 'N') ) req";
				
			}else{
				$sql	= "SELECT req.*, CASE WHEN score_home = n_goals_home_team AND score_away = n_goals_away_team THEN CASE WHEN n_matchday > 3 THEN ".$winnerPoints." * (n_matchday - 1) ELSE ".$winnerPoints." END ELSE CASE WHEN ( score_home > score_away AND n_goals_home_team > n_goals_away_team) OR ( score_home < score_away AND n_goals_home_team < n_goals_away_team) THEN CASE WHEN n_matchday > 3 THEN ".$scorePoints." * (n_matchday - 1) ELSE ".$scorePoints." END ELSE CASE WHEN score_home = score_away AND n_goals_home_team = n_goals_away_team THEN CASE WHEN n_matchday > 3 THEN ".$scorePoints." * (n_matchday - 1) ELSE ".$scorePoints." END ELSE 0 END END END as points FROM ( SELECT n_matchday,Prognosis.n_id_user,Prognosis.n_id_match,Prognosis.n_goals_away_team,Prognosis.n_goals_home_team,a_home_team_name,a_away_team_name,Matches.n_goals_home_team AS score_home, Matches.n_goals_away_team AS score_away,d_date,a_home_team_href,a_away_team_href, a_path, a_pseudonyme FROM Prognosis INNER JOIN Matches ON Prognosis.n_id_match = Matches.n_id_match INNER JOIN Users ON Prognosis.n_id_user = Users.n_id_user INNER JOIN Avatars ON Users.n_id_avatar = Avatars.n_id_avatar and Prognosis.n_id_match in (SELECT n_id_match FROM Matches where case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) < 0 then 'O' else case when DATEDIFF(NOW(), STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''), '%Y-%m-%d %H:%i:%S')) = 0 then  case when SUBSTRING( SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) FROM 1 FOR POSITION(  ':' IN SUBSTRING( SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) FROM POSITION(  ' ' IN SUBSTRING( SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) FROM POSITION(  '-' IN SUBSTRING( NOW( ) FROM POSITION(  '-' IN NOW( ) ) + 1) ) + 1) ) + 1) ) -1 ) +1 - extract(HOUR from STR_TO_DATE(replace(replace(d_date,'T',' '),'Z',''),'%Y-%m-%d %H:%i:%S')) < 0 then 'O' else 'N' end else 'N' end end = 'N') ) req";	
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
			
			$pronostics=Array();
			if( $data_base_postgres ){
				while($pro = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$res=Array();
					$res['n_id_user'] = $pro['n_id_user'];
					$res['n_id_match'] = $pro['n_id_match'];
					$res['n_goals_away_team'] = $pro['n_goals_away_team'];
					$res['n_goals_home_team'] = $pro['n_goals_home_team'];
					$res['a_home_team_name'] = $pro['a_home_team_name'];
					$res['a_away_team_name'] = $pro['a_away_team_name'];
					$res['score_home'] = $pro['score_home'];
					$res['score_away'] = $pro['score_away'];
					$res['d_date'] = $pro['d_date'];
					$res['a_home_team_href'] = $pro['a_home_team_href'];
					$res['a_away_team_href'] = $pro['a_away_team_href'];
					$res['a_path'] = $pro['a_path'];
					$res['a_pseudonyme'] = $pro['a_pseudonyme'];
					$res['points'] = $pro['points'];
					$pronostics[] = $res;
				 }
			}else{
				while($pro = $data->fetch())
				{
					$res=Array();
					$res['n_id_user'] = $pro['n_id_user'];
					$res['n_id_match'] = $pro['n_id_match'];
					$res['n_goals_away_team'] = $pro['n_goals_away_team'];
					$res['n_goals_home_team'] = $pro['n_goals_home_team'];
					$res['a_home_team_name'] = $pro['a_home_team_name'];
					$res['a_away_team_name'] = $pro['a_away_team_name'];
					$res['score_home'] = $pro['score_home'];
					$res['score_away'] = $pro['score_away'];
					$res['d_date'] = $pro['d_date'];
					$res['a_home_team_href'] = $pro['a_home_team_href'];
					$res['a_away_team_href'] = $pro['a_away_team_href'];
					$res['a_path'] = $pro['a_path'];
					$res['a_pseudonyme'] = $pro['a_pseudonyme'];
					$res['points'] = $pro['points'];
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

?>
