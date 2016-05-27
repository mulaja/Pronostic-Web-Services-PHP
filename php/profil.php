<?php

/* Récupérer le profil d'un urilisateur */
    function get_profil_by_id($id_user){
          
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
			
			$user=Array();
			
		  // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, n_id_avatar FROM '.$data_base_schema.'."Users" ';
				$sql   .= ' WHERE n_id_user = '.$id_user;
			}else{
				$sql	= 'SELECT n_id_user, a_lastname_user, a_firstname_user, d_date_creation, a_pseudonyme, a_email, n_id_avatar FROM Users ';
				$sql   .= ' WHERE n_id_user = '.$id_user;
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
					$user['id'] = $row['n_id_user'];
					$user['lastname'] = $row['a_lastname_user'];
					$user['firstname'] = $row['a_firstname_user'];
					$user['create_date'] = $row['d_date_creation'];
					$user['pseudonyme'] = $row['a_pseudonyme'];
					$user['email'] = $row['a_email'];
                    $user['id_avatar'] = $row['n_id_avatar'];
					
				}
			}else{
				if($row = $data->fetch())
				{
					$user['id'] = $row['n_id_user'];
					$user['lastname'] = $row['a_lastname_user'];
					$user['firstname'] = $row['a_firstname_user'];
					$user['create_date'] = $row['d_date_creation'];
					$user['pseudonyme'] = $row['a_pseudonyme'];
					$user['email'] = $row['a_email'];
					$user['id_avatar'] = $row['n_id_avatar'];
				}
				
			}
            
            $listAvatar=Array();
			
		  // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
				$sql='SELECT n_id_avatar, a_name_avatar, a_path FROM '.$data_base_schema.'."Avatars"';
			}else{
				$sql='SELECT n_id_avatar, a_name_avatar, a_path FROM Avatars';
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
					$res['id_avatar'] = $row['n_id_avatar'];
					$res['name_avatar'] = $row['a_name_avatar'];
					$res['path'] = $row['a_path'];

					$listAvatar[] = $res;
				}
			}else{
				while($row = $data->fetch())
				{
					$res=Array();
					$res['id_avatar'] = $row['n_id_avatar'];
					$res['name_avatar'] = $row['a_name_avatar'];
					$res['path'] = $row['a_path'];

					$listAvatar[] = $res;
				}
			}
            
            $resultat['status'] = true;
			$resultat['profil'] = Array();
            $resultat['profil']['user'] = $user;
            $resultat['profil']['listAvatar'] = $listAvatar;
            
            // On ferme la connection
			if( $data_base_postgres ){
				pg_close($connexion);
			}else{
				unset($connexion);
			}
			
			return $resultat;
    }

/* Récupérer le profil d'un urilisateur */
    function modify_profil($profil,$id){
          
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
            
            // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
				$sql	= 'SELECT n_id_user, a_lastname_user FROM '.$data_base_schema.'."Users" ';
				$sql   .= " WHERE a_pseudonyme = '".$profil['pseudonyme']."'";
                $sql   .= " AND n_id_user <> ".$profil['id']."";
			}else{
				$sql	= 'SELECT n_id_user, a_lastname_user n_id_avatar FROM Users ';
				$sql   .= " WHERE a_pseudonyme = '".$profil['pseudonyme']."'";
                $sql   .= " AND n_id_user <> ".$profil['id']."";
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
					$resultat['status'] = false;
				    $resultat['message'] = "Le pseudonyme ".$profil['pseudonyme']." existe déjà - Enregistrement impossible";
				
				    return $resultat;	
				}
			}else{
				if($row = $data->fetch())
				{
					$resultat['status'] = false;
				    $resultat['message'] = "Le pseudonyme ".$profil['pseudonyme']." existe déjà - Enregistrement impossible";
				
				    return $resultat;	
				}
				
			}
			
		  // Cr�ation de la requ�te SQL
			if( $data_base_postgres ){
                $sql	= 'UPDATE '.$data_base_schema.'."Users" ';
				$sql   .= "SET a_lastname_user= '".$profil['lastname']."' , a_firstname_user= '".$profil['firstname']."' ,";
                $sql   .= " a_pseudonyme= '".$profil['pseudonyme']."' ,";
                
                if( array_key_exists('password', $profil) ){
                    $sql   .= "a_password= '".$profil['password']."',";    
                }
                
                $sql   .= " a_email= '".$profil['email']."' , n_id_avatar= ".$profil['id_avatar'];
				$sql   .= " WHERE n_id_user = ".$id;
			}else{
                $sql 	= 'UPDATE Users ';
                $sql   .= " SET a_lastname_user  = :lastname_user , a_firstname_user= :firstname_user,";
                $sql   .= " a_pseudonyme  =  :pseudonyme,";
                
                if( array_key_exists('password', $profil) ){
                    $sql   .= "a_password= :password,";    
                }
                
                $sql   .= " a_email  =  :email, n_id_avatar = :id_avatar";
                $sql   .= ' WHERE n_id_user = :id_user';
                
                $prepare = $connexion->prepare($sql);
                $prepare->bindParam(':lastname_user', $profil['lastname'], PDO::PARAM_STR,50);
                $prepare->bindParam(':firstname_user', $profil['firstname'], PDO::PARAM_STR,50);
                $prepare->bindParam(':pseudonyme', $profil['pseudonyme'], PDO::PARAM_STR,50);
                if( array_key_exists('password', $profil) ){
                    $prepare->bindParam(':password', $profil['password'], PDO::PARAM_STR,100);
                }
                $prepare->bindParam(':email', $profil['email'], PDO::PARAM_STR,50);
                $prepare->bindParam(':id_avatar', $profil['id_avatar'], PDO::PARAM_INT);
                $prepare->bindParam(':id_user', $id, PDO::PARAM_INT);
			}
            
            // Execution de la requ�te SQL
			if( $data_base_postgres ){
				pg_query($connexion,$sql);
			}else{
				$prepare ->execute();	
			}
			         
            // On recupère les informations de l'utilisateurs créer
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

?>