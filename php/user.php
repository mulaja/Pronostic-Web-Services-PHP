<?php

/* On récupère en base de données la liste des utilisateurs */
	function get_users()
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
		  
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
			
			$users=Array();
			
		  // Création de la requête SQL
			if( $data_base_postgres ){
				$sql='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email FROM '.$data_base_schema.'."Users"';
			}else{
				$sql='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email FROM Users';
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
			
		// Récuperation des données
			if( $data_base_postgres ){
				while($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$res=Array();
					$res['id'] = $row['n_id_user'];
					$res['lastname'] = $row['a_lastname_user'];
					$res['firstname'] = $row['a_firstname_user'];
					$res['create_date'] = $row['d_date_creation'];
					$res['pseudonyme'] = $row['a_pseudonyme'];
					$res['email'] = $row['a_email'];

					$users[] = $res;
				}
			}else{
				while($row = $data->fetch())
				{
					$res=Array();
					$res['id'] = $row['n_id_user'];
					$res['lastname'] = $row['a_lastname_user'];
					$res['firstname'] = $row['a_firstname_user'];
					$res['create_date'] = $row['d_date_creation'];
					$res['pseudonyme'] = $row['a_pseudonyme'];
					$res['email'] = $row['a_email'];

					$users[] = $res;
				}
			}
			
			$resultat['status'] = true;
			$resultat['utilisateurs'] = $users;
						
		// On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $resultat;
	  }
	  
	/* On récupère en base de données les données de l'utilisateur */
	function get_user_by_id($id)
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
			$resultat['message'] = "L'utilisateur n'existe pas";
			
		// On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
			
		 // Création de la requête SQL
			if( $data_base_postgres ){
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email FROM '.$data_base_schema.'."Users" ';
				$sql   .= ' WHERE n_id_user = '.$id;
			}else{
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email FROM Users ';
				$sql   .= ' WHERE n_id_user = '.$id;
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
			
		 // Récuperation des données
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$res=Array();
					$res['id'] = $row['n_id_user'];
					$res['lastname'] = $row['a_lastname_user'];
					$res['firstname'] = $row['a_firstname_user'];
					$res['create_date'] = $row['d_date_creation'];
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
					$res['create_date'] = $row['d_date_creation'];
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
	  

	  
	   /* On ajoute l'utilisateur en données les données */
	function add_user($utilisateur)
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
			
			// On vérifie la connecxion à la base de données
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Problème de connection à la base de données";
				
				return $resultat;
			}
				
		 //	On vérfie les paramètres
			if( !$utilisateur['lastname'] || !$utilisateur['firstname'] || !$utilisateur['pseudonyme'] || !$utilisateur['password'] || !$utilisateur['email'] )
			{
				$resultat['status'] = false;
				$resultat['message'] = 'Mauvais format';
				return $resultat;
			}
		
		// On vérifie l'unicité du pseudonyme
			if( $data_base_postgres ){
				$sql 	= 'SELECT a_pseudonyme FROM '.$data_base_schema.'."Users" ';
				$sql   .= " WHERE a_pseudonyme = '".pg_escape_string($utilisateur['pseudonyme'])."'";
			}else{
				$sql 	= 'SELECT a_pseudonyme FROM Users ';
				$sql   .= " WHERE a_pseudonyme = '".$utilisateur['pseudonyme']."'";
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
			
			// Si on au moins un résultat c'est que le pseudonyme est déjà utilisé
			if( $data_base_postgres ){
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC))
				{
					$resultat['status'] = false;
					$resultat['message'] = 'Le pseudonyme "'.$utilisateur['pseudonyme'].'" existe deja';
					
					return $resultat;
				}
			}else{
				if($row = $data->fetch())
				{
					$resultat['status'] = false;
					$resultat['message'] = 'Le pseudonyme "'.$utilisateur['pseudonyme'].'" existe deja';
					
					return $resultat;
				}
			}
 			
		 // On ajoute le nouvel utilisateur en base de données
			if( $data_base_postgres ){
				
				$sql	= 'INSERT INTO '.$data_base_schema.'."Users" ';
				$sql   .= '( n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme , a_password, a_email)';
				$sql   .= " VALUES (nextval('".$data_base_schema.".\"Users_sequence\"') , '";
				$sql   .= pg_escape_string($utilisateur['lastname'])."', '";
				$sql   .= pg_escape_string($utilisateur['firstname']);
				$sql   .= "',current_date, '";
				$sql   .= pg_escape_string($utilisateur['pseudonyme'])."', '";
				$sql   .= pg_escape_string($utilisateur['password'])."', '";
				$sql   .= pg_escape_string($utilisateur['email'])."')";
				$sql   .= ' RETURNING n_id_user';
				
				$data = pg_query($connexion,$sql);
				
				// On vérifie l'éxecution de la requête SQL
				if(!$data){
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requête SQL";
					
					return $resultat;
				}
				
				// On récupère l'identifiant
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC)){
					$resultat['id_utilisateur'] = $row['n_id_user'];
				}
				
			}else{
				
				// On recupère le prochain identifiant disponible
				$sql	= 'SELECT MAX(n_id_user) AS max_id FROM Users ';
				
				$data=$connexion->query($sql);
				
				// On vérifie l'éxecution de la requête SQL
				if(!$data){
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requête SQL";
					
					return $resultat;
				}
				
				$id_utilisateur = 0;
				if( $row = $data->fetch() )
				{
					$id_utilisateur = $row['max_id'];
				}
				$id_utilisateur = $id_utilisateur + 1;
								
				$sql	= 'INSERT INTO Users ';
				$sql   .= '( n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme , a_password, a_email)';
				$sql   .= " VALUES ( :id, :lastname, :firstname, current_date, :pseudonyme, :password, :email)";
				
				$prepare = $connexion->prepare($sql);
				$prepare->bindParam(':id', $id_utilisateur, PDO::PARAM_INT);
				$prepare->bindParam(':lastname', $utilisateur['lastname'], PDO::PARAM_STR,50);
				$prepare->bindParam(':firstname', $utilisateur['firstname'], PDO::PARAM_STR,50);
				$prepare->bindParam(':pseudonyme', $utilisateur['pseudonyme'], PDO::PARAM_STR,50);
				$prepare->bindParam(':password', $utilisateur['password'], PDO::PARAM_STR,100);
				$prepare->bindParam(':email', $utilisateur['email'], PDO::PARAM_STR,50);
				$res = $prepare->execute();
				
				$count = $prepare->rowCount();
				
				if( $count === 1 ){
					$resultat['id_utilisateur'] = $id_utilisateur;
				}else{
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requête SQL";
					
					return $resultat;
				}
			}
								
			$resultat['status'] = true;
					
		// On ferme la connection			
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $resultat;
	  }

?>