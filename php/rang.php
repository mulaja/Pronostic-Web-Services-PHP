<?php
	  
	  /* Recup�rer le classement */
	  
	  function get_rangs(){
	  
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
			$sql 	= 'SELECT n_id_user, a_pseudonyme FROM '.$data_base_schema.'."Users" ';
		}else{
			$sql 	= 'SELECT n_id_user, a_pseudonyme FROM Users ';
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