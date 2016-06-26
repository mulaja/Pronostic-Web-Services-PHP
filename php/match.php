<?php

/* Recupere la liste des matchs */
	  function get_matches()
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
				$sql	= "SELECT n_id_match, d_date, n_goals_away_team, n_goals_home_team,a_home_team_name, a_home_team_href, a_away_team_href, a_away_team_name,a_status "; 
				$sql   .= 'FROM '.$data_base_schema.'."Matches"';
			}else{
				$sql	= "SELECT n_id_match, d_date,n_goals_away_team, n_goals_home_team, a_home_team_name, a_home_team_href, a_away_team_href,a_away_team_name,a_status "; 
				$sql   .= 'FROM Matches';
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
					$res['idMatch'] = $pro['n_id_match'];
					$res['date'] = $pro['d_date'];
					$res['homeTeamName'] = $pro['a_home_team_name'];
					$res['homeTeamHref'] = $pro['a_home_team_href'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['awayTeamHref'] = $pro['a_away_team_href'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['status'] = $pro['a_status'];
					$pronostics[] = $res;
				 }
			}else{
				while($pro = $data->fetch())
				{
					$res=Array();
					$res['idMatch'] = $pro['n_id_match'];
					$res['date'] = $pro['d_date'];
					$res['homeTeamName'] = $pro['a_home_team_name'];
					$res['homeTeamHref'] = $pro['a_home_team_href'];
					$res['goalsAwayTeam'] = $pro['n_goals_away_team'];
					$res['awayTeamName'] = $pro['a_away_team_name'];
					$res['awayTeamHref'] = $pro['a_away_team_href'];
					$res['goalsHomeTeam'] = $pro['n_goals_home_team'];
					$res['status'] = $pro['a_status'];
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