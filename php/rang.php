<?php

/* Calcul Le nombre de points */
	  function calcul_points($id_utilisateur)
	  {
		   // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $winner_points, $score_points;
			
			$points = Array();
			$points['points'] = 0;
			$points['winners'] = 0;
			$points['scores'] = 0;
			
			 // Connexion, sélection de la base de données
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
			// On recupère les paramètres de l'application
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
	  
	  /* Recupérer le classement */
	  
	  function get_rangs(){
	  
	   // Variables globales
		global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
	  
	  // Connexion, sélection de la base de données
		if( $data_base_postgres ){
			$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
		}else{
			$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
		}
		
		// Préparation de la réponse	
		$resultat = Array();
		$rangs=Array();		
		
		// On vérifie la connecxion à la base de données
		if(!$connexion){
			$resultat['status'] = false;
			$resultat['message'] = "Problème de connection à la base de données";
			
			return $resultat;
		}
		
		// On parcours la liste des utilisateurs
		if( $data_base_postgres ){
			$sql 	= 'SELECT n_id_user, a_pseudonyme FROM '.$data_base_schema.'."Users" ';
		}else{
			$sql 	= 'SELECT n_id_user, a_pseudonyme FROM Users ';
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
			while($row = pg_fetch_array($data, null, PGSQL_ASSOC))
			{
				$res=Array();
				$res['id'] = $row['n_id_user'];
				$res['pseudonyme'] = $row['a_pseudonyme'];
				
				$points = calcul_points($res['id']);
				
				$res['points'] = $points['points'];
				$res['winners'] = $points['winners'];
				$res['scores'] = $points['scores'];
				
				
				$rangs[] = $res;
			}
		}else{
			while($row = $data->fetch())
			{
				$res=Array();
				$res['id'] = $row['n_id_user'];
				$res['pseudonyme'] = $row['a_pseudonyme'];
				
				$points = calcul_points($res['id']);
				
				$res['points'] = $points['points'];
				$res['winners'] = $points['winners'];
				$res['scores'] = $points['scores'];
				
				$rangs[] = $res;
			}
		}
		
		$resultat['status'] = true;
		$resultat['rangs'] = $rangs;
		
		// On ferme la connection
		if( $data_base_postgres ){
			pg_close($connexion);
		}else{
			unset($connexion);
		}
	  
		return $resultat;
	  
	}

?>