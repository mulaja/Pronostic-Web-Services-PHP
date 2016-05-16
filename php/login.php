<?php

	  /* Recupere un utilisateurs */
	function login($utilisateur)
	  {
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
			$resultat['status'] = false;
			$resultat['message'] = 'Le login et/ou le mot de passe est incorrecte';
			
			// On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
				
			// On vérifie les données d'authentification
			if( $data_base_postgres ){
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, a_pseudonyme, a_email FROM '.$data_base_schema.'."Users" ';
				$sql   .= " WHERE a_pseudonyme = '".$utilisateur['pseudonyme']."'";
				$sql   .= " AND a_password = '".$utilisateur['password']."'";
			}else{
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, a_pseudonyme, a_email FROM Users ';
				$sql   .= " WHERE a_pseudonyme = '".$utilisateur['pseudonyme']."'";
				$sql   .= " AND a_password = '".$utilisateur['password']."'";
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
					$res=Array();
					$res['id'] = $row['n_id_user'];
					$res['lastname'] = $row['a_lastname_user'];
					$res['firstname'] = $row['a_firstname_user'];
					$res['pseudonyme'] = $row['a_pseudonyme'];
					$res['email'] = $row['a_email'];
					
					$resultat['status'] = true;
					$resultat['message'] = "OK";
					$resultat['utilisateur'] = $res;
										
				}
			}else{
				if($row = $data->fetch())
				{	
					$res=Array();
					$res['id'] = $row['n_id_user'];
					$res['lastname'] = $row['a_lastname_user'];
					$res['firstname'] = $row['a_firstname_user'];
					$res['pseudonyme'] = $row['a_pseudonyme'];
					$res['email'] = $row['a_email'];
					
					$resultat['status'] = true;
					$resultat['message'] = "OK";
					$resultat['utilisateur'] = $res;
			
				}			
			}
			
		// On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $resultat;
	  }

?>