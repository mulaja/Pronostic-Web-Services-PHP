<?php
    /* On ajoute un group en donn�es les donn�es */
    function add_group($group)
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
            

    // On v�rifie l'unicit� du pseudonyme
        if( $data_base_postgres ){
            $sql 	= 'SELECT a_name_group FROM '.$data_base_schema.'."Groups" ';
            $sql   .= " WHERE a_name_group = '".pg_escape_string($group['nom'])."'";
        }else{
            $sql 	= 'SELECT a_name_group FROM Groups ';
            $sql   .= " WHERE a_name_group = '".$group['nom']."'";
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
                $resultat['message'] = 'Le groupe "'.$group['nom'].'" existe deja';
                
                return $resultat;
            }
        }else{
            if($row = $data->fetch())
            {
                $resultat['status'] = false;
                $resultat['message'] = 'Le groupe "'.$group['nom'].'" existe deja';
                
                return $resultat;
            }
        }
        
        // On ajoute le nouveau groupe en base de donn�es
        if( $data_base_postgres ){
            
            $sql	= 'INSERT INTO '.$data_base_schema.'."Groups" ';
            $sql   .= '( n_id_group, a_name_group)';
            $sql   .= " VALUES (nextval('".$data_base_schema.".\"Groups_sequence\"') , '";
            $sql   .= pg_escape_string($group['nom'])."')";
            $sql   .= ' RETURNING n_id_group';
            
            $data = pg_query($connexion,$sql);
            
            // On v�rifie l'�xecution de la requ�te SQL
            if(!$data){
                $resultat['status'] = false;
                $resultat['message'] = "Erreur requ�te SQL";
                
                return $resultat;
            }
            
            // On r�cup�re l'identifiant
            if($row = pg_fetch_array($data, null, PGSQL_ASSOC)){
                $id_group = $row['n_id_group'];
            }
            
            // On ajout le mapping
            foreach ( $group['user'] as $user ){
                $sql	= 'INSERT INTO '.$data_base_schema.'."Group_Users" ';
                $sql   .= '( n_id_user,n_id_group)';
                $sql   .= " VALUES (";
                $sql   .= $user['id'];
                $sql   .= ",";
                $sql   .= $id_group;
                $sql   .= ")" ;
                
                 $data = pg_query($connexion,$sql);
            
                // On v�rifie l'�xecution de la requ�te SQL
                if(!$data){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
            }
                       
        }else{
            
            // On recup�re le prochain identifiant disponible
            $sql	= 'SELECT MAX(n_id_group) AS max_id FROM Groups ';
            
            $data=$connexion->query($sql);
            
            // On v�rifie l'�xecution de la requ�te SQL
            if(!$data){
                $resultat['status'] = false;
                $resultat['message'] = "Erreur requ�te SQL";
                
                return $resultat;
            }
            
            $id_group = 0;
            if( $row = $data->fetch() )
            {
                $id_group = $row['max_id'];
            }
            $id_group = $id_group + 1;
                            
            $sql	= 'INSERT INTO Groups ';
            $sql   .= '( n_id_group, a_name_group)';
            $sql   .= " VALUES ( :id, :groupname)";
            
            $prepare = $connexion->prepare($sql);
            $prepare->bindParam(':id', $id_group, PDO::PARAM_INT);
            $prepare->bindParam(':groupname', $group['nom'], PDO::PARAM_STR,50);
            $res = $prepare->execute();
            
            $count = $prepare->rowCount();
            
            if( $count !== 1 ){
                $resultat['status'] = false;
                $resultat['message'] = "Erreur requ�te SQL";
                
                return $resultat;
            }
            
            // On ajout le mapping
            foreach ( $group['user'] as $user ){
                $sql	= 'INSERT INTO Group_Users ';
                $sql   .= '( n_id_group, n_id_user)';
                $sql   .= " VALUES ( :idGroup, :idUser)";
                     
                $prepare = $connexion->prepare($sql);
                $prepare->bindParam(':idGroup', $id_group, PDO::PARAM_INT);
                $prepare->bindParam(':idUser', $user['id'], PDO::PARAM_INT);
                $res = $prepare->execute();
                
                $count = $prepare->rowCount();
            
                if( $count !== 1 ){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
                
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
    
     /* On mofifier un groupe en donn�es les donn�es */
    function modify_group($group,$id_group)
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
              
        // On modifie le groupe en base de donn�es
        if( $data_base_postgres ){
            
            // On ajout le mapping
            foreach ( $group['user'] as $user ){
                $sql	= 'INSERT INTO '.$data_base_schema.'."Group_Users" ';
                $sql   .= '( n_id_user,n_id_group)';
                $sql   .= " VALUES (";
                $sql   .= $user['id'];
                $sql   .= ",";
                $sql   .= $id_group;
                $sql   .= ")" ;
                
                $data = pg_query($connexion,$sql);
            
                // On v�rifie l'�xecution de la requ�te SQL
                if(!$data){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
            }
                       
        }else{
                        
            // On ajout le mapping
            foreach ( $group['user'] as $user ){
                $sql	= 'INSERT INTO Group_Users ';
                $sql   .= '( n_id_group, n_id_user)';
                $sql   .= " VALUES ( :idGroup, :idUser)";
                     
                $prepare = $connexion->prepare($sql);
                $prepare->bindParam(':idGroup', $id_group, PDO::PARAM_INT);
                $prepare->bindParam(':idUser', $user['id'], PDO::PARAM_INT);
                $res = $prepare->execute();
                
                $count = $prepare->rowCount();
            
                if( $count !== 1 ){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
                
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
    
     /* On mofifier un groupe en donn�es les donn�es */
    function unsubcribe_group($id_group,$id_utilisateur)
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
              
        // On modifie le groupe en base de donn�es
        if( $data_base_postgres ){
            
            $sql	= 'DELETE FROM '.$data_base_schema.'."Group_Users" ';
            $sql   .= ' WHERE n_id_user ='.$id_utilisateur;
            $sql   .= ' AND n_id_group ='.$id_group;
            
            echo $sql;
            
            $data = pg_query($connexion,$sql);
            
            // On v�rifie l'�xecution de la requ�te SQL
            if(!$data){
                $resultat['status'] = false;
                $resultat['message'] = "Erreur requ�te SQL";
                
                return $resultat;
            }
            
            $sql	= 'SELECT n_id_group FROM '.$data_base_schema.'."Group_Users" ';
            $sql   .= ' WHERE n_id_group ='+$id_group;
            
            $data=pg_query($connexion,$sql);
            
            if(!$row = pg_fetch_array($data, null, PGSQL_ASSOC))
            {
                $sql	= 'DELETE FROM '.$data_base_schema.'."Groups" ';
                $sql   .= ' WHERE n_id_group ='.$id_group;
                
                $data = pg_query($connexion,$sql);
            
                // On v�rifie l'�xecution de la requ�te SQL
                if(!$data){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
            }
                      
        }else{
            
            $sql	= 'DELETE FROM Group_Users ';
            $sql   .= " WHERE n_id_group= :idGroup AND n_id_user = :idUser";
            
            $prepare = $connexion->prepare($sql);
            $prepare->bindParam(':idGroup', $id_group, PDO::PARAM_INT);
            $prepare->bindParam(':idUser', $id_utilisateur, PDO::PARAM_INT);
            $res = $prepare->execute();
            
            $count = $prepare->rowCount();
        
            if( $count !== 1 ){
                $resultat['status'] = false;
                $resultat['message'] = "Erreur requ�te SQL";
                
                return $resultat;
            }
            
            $sql	= 'SELECT n_id_group FROM Group_Users ';
            $sql   .= ' WHERE n_id_group ='.$id_group;
            
            $data=$connexion->query($sql);
            
             if( !$row = $data->fetch() ){
                $sql	= 'DELETE FROM Groups ';
                $sql   .= ' WHERE n_id_group = :idGroup';
                
                $prepare = $connexion->prepare($sql);
                $prepare->bindParam(':idGroup', $id_group, PDO::PARAM_INT);
                
                $res = $prepare->execute();
            
                $count = $prepare->rowCount();
            
                if( $count !== 1 ){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
                
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
    
     /* On récupère les groupes d'un utilisateuren donn�es les donn�es */
    function get_groups_by_id($id_user){
	  
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
		$rangs=Array();		
		
		// On v�rifie la connecxion � la base de donn�es
		if(!$connexion){
			$resultat['status'] = false;
			$resultat['message'] = "Probl�me de connection � la base de donn�es";
			
			return $resultat;
		}
		
		// On parcours la liste des utilisateurs
		if( $data_base_postgres ){
			$sql 	= 'SELECT '.$data_base_schema.'."Groups".n_id_group, a_name_group FROM '.$data_base_schema.'."Group_Users" ';
            $sql   .= ' INNER JOIN '.$data_base_schema.'."Groups"'.' ON '.$data_base_schema.'."Group_Users".n_id_group = '.$data_base_schema.'."Groups".n_id_group';
            $sql   .= ' WHERE n_id_user = ';
            $sql   .= $id_user ;
		}else{
			$sql 	= 'SELECT Group_Users.n_id_group, a_name_group FROM Group_Users ';
            $sql   .= ' INNER JOIN Groups on Groups.n_id_group = Group_Users.n_id_group';
            $sql   .= ' WHERE n_id_user = ';
            $sql   .= $id_user ;
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
		
         $liste_group = Array();
        
		if( $data_base_postgres ){
			
            while($row = pg_fetch_array($data, null, PGSQL_ASSOC))
            {			
                $group = Array();
                $group['id'] = $row['n_id_group'];
                $group['nom'] = $row['a_name_group'];
                
                $id_group = $row['n_id_group'];
                
                $sql 	= 'SELECT '.$data_base_schema.'."Users".n_id_user, a_pseudonyme, a_path FROM '.$data_base_schema.'."Group_Users" ';
                $sql   .= ' INNER JOIN '.$data_base_schema.'."Users"'.' ON '.$data_base_schema.'."Group_Users".n_id_user = '.$data_base_schema.'."Users".n_id_user';
                $sql   .= ' INNER JOIN '.$data_base_schema.'."Avatars" ON '.$data_base_schema.'."Users".n_id_avatar = '.$data_base_schema.'."Avatars".n_id_avatar ';
                $sql   .= ' WHERE n_id_group = ';
                $sql   .= $id_group ;          
                
                $data2=pg_query($connexion,$sql);
                // On v�rifie l'�xecution de la requ�te SQL
                if(!$data2){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
                
                $group['classement'] = Array();
                
                while($row2 = pg_fetch_array($data2, null, PGSQL_ASSOC))
			    {
                    $res=Array();
				    $res['id'] = $row2['n_id_user'];
                    $res['pseudonyme'] = $row2['a_pseudonyme'];
                    $res['path'] = $row2['a_path'];
                    
                    $points = calcul_points($row2['n_id_user']);
                    
                    $res['points'] = intval ($points['points']) + intval(calcul_winners($res['id']));
                    $res['winners'] = $points['winners'];
                    $res['scores'] = $points['scores'];
                    
                    $group['classement'][] = $res;
                }
				$liste_group[] = $group;
			}
            
		}else{
			while($row = $data->fetch())
			{
				$group = Array();
                $group['id'] = $row['n_id_group'];
                $group['nom'] = $row['a_name_group'];
                
                $id_group = $row['n_id_group'];
                
                $sql 	= 'SELECT Users.n_id_user , a_pseudonyme , a_path FROM Group_Users ';
                $sql   .= ' INNER JOIN Users on Users.n_id_user = Group_Users.n_id_user';
                $sql   .= ' INNER JOIN Avatars ON Users.n_id_avatar = Avatars.n_id_avatar ';
                $sql   .= ' WHERE n_id_group = ';
                $sql   .= $id_group ;  
                
                $data2=$connexion->query($sql);
                // On v�rifie l'�xecution de la requ�te SQL
                if(!$data2){
                    $resultat['status'] = false;
                    $resultat['message'] = "Erreur requ�te SQL";
                    
                    return $resultat;
                }
                
                $group['classement'] = Array();
                
                while($row2 = $data2->fetch())
			    {
                    $res=Array();
				
                    $res['id'] = $row2['n_id_user'];              
                    $res['pseudonyme'] = $row2['a_pseudonyme'];
                    $res['path'] = $row2['a_path'];
                    
                    $points = calcul_points($row2['n_id_user']);
                    
                    $res['points'] = intval ($points['points']) + intval(calcul_winners($res['id']));
                    $res['winners'] = $points['winners'];
                    $res['scores'] = $points['scores'];
                    
                    $group['classement'][] = $res;
                }
				$liste_group[] = $group;
			}
		}
		
		$resultat['status'] = true;
		$resultat['groups'] = $liste_group;
		
		// On ferme la connection
		if( $data_base_postgres ){
			pg_close($connexion);
		}else{
			unset($connexion);
		}
	  
		return $resultat;
	}
?>