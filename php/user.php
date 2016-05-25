<?php

/* On r�cup�re en base de donn�es la liste des utilisateurs */
	function get_users()
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
		  
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
			
			$users=Array();
			
		  // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
				$sql  ='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM '.$data_base_schema.'."Users"';
				$sql .=' INNER JOIN '.$data_base_schema.'."Avatars" ON '.$data_base_schema.'."Users".n_id_avatar = '.$data_base_schema.'."Avatars".n_id_avatar';
			}else{
				$sql  ='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM Users';
				$sql .=' INNER JOIN Avatars ON Avatars.n_id_avatar = Users.n_id_avatar';
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
			
		// R�cuperation des donn�es
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
					$res['path'] = $row['a_path'];

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
					$res['path'] = $row['a_path'];

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
	  
	/* On r�cup�re en base de donn�es les donn�es de l'utilisateur */
	function get_user_by_id($id)
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
		  
		  // Connexion, s�lection de la base de donn�es
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
		// Pr�paration de la r�ponse	
			$resultat = Array();	
			$resultat['status'] = false;
			$resultat['message'] = "L'utilisateur n'existe pas";
			
		// On v�rifie la connecxion � la base de donn�es
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Probl�me de connection � la base de donn�es";
				
				return $resultat;
			}
			
		 // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
				
				$sql  	='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM '.$data_base_schema.'."Users"';
				$sql 	.=' INNER JOIN '.$data_base_schema.'."Avatars" ON '.$data_base_schema.'."Users".n_id_avatar = '.$data_base_schema.'."Avatars".n_id_avatar';
				$sql   .= ' WHERE n_id_user = '.$id;
				
			}else{
				
				$sql  	='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM Users';
				$sql 	.=' INNER JOIN Avatars ON Avatars.n_id_avatar = Users.n_id_avatar';
				$sql   .= ' WHERE n_id_user = '.$id;
				
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
			
		 // R�cuperation des donn�es
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
					$res['path'] = $row['a_path'];
					
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
					$res['path'] = $row['a_path'];
					
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
	  

	  
	   /* On ajoute l'utilisateur en donn�es les donn�es */
	function add_user($utilisateur)
	  {
		  // Variables globales
			global $data_base_host, $data_base_name, $data_base_user, $data_base_password, $data_base_schema, $data_base_postgres, $date_last_upate, $version;
		  
		  // Connexion, s�lection de la base de donn�es
			if( $data_base_postgres ){
				$connexion = pg_pconnect("host=".$data_base_host." dbname=".$data_base_name." user=".$data_base_user);
			}else{
				$connexion =  new PDO('mysql:host='.$data_base_host.';dbname='.$data_base_name, $data_base_user, $data_base_password);
			}
			
			// Pr�paration de la r�ponse	
			$resultat = Array();	
			
			// On v�rifie la connecxion � la base de donn�es
			if(!$connexion){
				$resultat['status'] = false;
				$resultat['message'] = "Probl�me de connection � la base de donn�es";
				
				return $resultat;
			}
				
		 //	On v�rfie les param�tres
			if( !$utilisateur['lastname'] || !$utilisateur['firstname'] || !$utilisateur['pseudonyme'] || !$utilisateur['password'] || !$utilisateur['email'] )
			{
				$resultat['status'] = false;
				$resultat['message'] = 'Mauvais format';
				return $resultat;
			}
		
		// On v�rifie l'unicit� du pseudonyme
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
			
			// On v�rifie l'�xecution de la requ�te SQL
			if(!$data){
				$resultat['status'] = false;
				$resultat['message'] = "Erreur requ�te SQL";
				
				return $resultat;
			}
			
			// Si on au moins un r�sultat c'est que le pseudonyme est d�j� utilis�
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
 			
		 // On ajoute le nouvel utilisateur en base de donn�es
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
				
				// On v�rifie l'�xecution de la requ�te SQL
				if(!$data){
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requ�te SQL";
					
					return $resultat;
				}
				
				// On r�cup�re l'identifiant
				if($row = pg_fetch_array($data, null, PGSQL_ASSOC)){
					$id_utilisateur = $row['n_id_user'];
				}
				
			}else{
				
				// On recup�re le prochain identifiant disponible
				$sql	= 'SELECT MAX(n_id_user) AS max_id FROM Users ';
				
				$data=$connexion->query($sql);
				
				// On v�rifie l'�xecution de la requ�te SQL
				if(!$data){
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requ�te SQL";
					
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
					$id_utilisateur;
				}else{
					$resultat['status'] = false;
					$resultat['message'] = "Erreur requ�te SQL";
					
					return $resultat;
				}
			}
			
			// On recupère les informations de l'utilisateurs créer
			if( $data_base_postgres ){
				
				$sql  	='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM '.$data_base_schema.'."Users"';
				$sql 	.=' INNER JOIN '.$data_base_schema.'."Avatars" ON '.$data_base_schema.'."Users".n_id_avatar = '.$data_base_schema.'."Avatars".n_id_avatar';
				$sql   .= ' WHERE n_id_user = '.$id_utilisateur;
				
			}else{
				
				$sql  	='SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, a_path FROM Users';
				$sql 	.=' INNER JOIN Avatars ON Avatars.n_id_avatar = Users.n_id_avatar';
				$sql   .= ' WHERE n_id_user = '.$id_utilisateur;
				
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
			
		 // R�cuperation des donn�es
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
					$res['path'] = $row['a_path'];
					
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
					$res['path'] = $row['a_path'];
					
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