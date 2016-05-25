<?php

	require_once __DIR__.'/silex/vendor/autoload.php';
	require_once __DIR__.'/php/common.php';
	require_once __DIR__.'/php/init.php';
	require_once __DIR__.'/php/user.php';
	require_once __DIR__.'/php/login.php';
	require_once __DIR__.'/php/match.php';
	require_once __DIR__.'/php/prognosis.php';
	require_once __DIR__.'/php/rang.php';
	require_once __DIR__.'/php/group.php';
	require_once __DIR__.'/php/profil.php';
	
	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;
	
	$app = new Silex\Application();
	$app['debug']=true;
	
	/* Recuperer l'ensemble des utilisateurs */
	$app->get('/Users', function () {
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données la liste des utilisateurs
		$resultat=get_users();
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_INTERNAL_SERVER_ERROR
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['utilisateurs']));
		}else{
			$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer les pronotics d'un utilisateur */
	$app->get('/Prognosis', function (Request $request) {
		
		// On récupère les paramètres
		$id_user = $request->get('id_user');
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les pronostics des utilisateurs
		$resultat=get_pronostics($id_user);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_INTERNAL_SERVER_ERROR
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['pronostics']));
		}else{
			$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer les matchs */
	$app->get('/Match', function (Request $request) {
			
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les pronostics des utilisateurs
		$resultat=get_matches();
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_INTERNAL_SERVER_ERROR
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['pronostics']));
		}else{
			$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer les données d'un utilisateur */
	$app->get('/Users/{id}', function ($id) {
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les données de l'utilisateur
		$resultat=get_user_by_id($id);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['utilisateur']));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer les groupe d'un utilisateur */
	$app->get('/Groups/{id}', function ($id) {
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les données de l'utilisateur
		$resultat=get_groups_by_id($id);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['groups']));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer les données d'un utilisateur */
	$app->get('/Rangs', function () {
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les données de l'utilisateur
		$resultat=get_rangs();
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['rangs']));
		}else{
			$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer le profil d'un utilisateur */
	$app->get('/Profil/{id}', function ($id) {
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On récupère en base de données les données de l'utilisateur
		$resultat=get_profil_by_id($id);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['profil']));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Recuperer le numéro de version */
	$app->get('/Version', function () {
		
		global $version;
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
	
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND
		
		$response->setStatusCode(Response::HTTP_OK);
		$response->setContent($version);
		
		return $response;
	});
	
	/* Verifier les données d'authentofications */
	$app->post('/Login', function (Request $request) {
		
		// On récupère les paramètres
		$utilisateur = Array();
		$utilisateur['pseudonyme'] = $request->get('pseudonyme');
		$utilisateur['password'] = $request->get('password');
		
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On vérifié en base de données les données d'authentification
		$resultat=login($utilisateur);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_NOT_FOUND		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat['utilisateur']));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_FOUND);
			$response->setContent(json_encode($resultat));
		}

		return $response;
	});
	
	/* Ajouter un utilisateur */
	$app->post('/Users', function (Request $request) {

		// On récupère les paramètres
		$utilisateur = Array();
		$utilisateur['lastname'] = $request->get('lastname');
		$utilisateur['firstname'] = $request->get('firstname');
		$utilisateur['pseudonyme'] = $request->get('pseudonyme');
		$utilisateur['password'] = $request->get('password');
		$utilisateur['email'] = $request->get('email');

		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On ajoute l'utilisateur en données les données
		$resultat = add_user($utilisateur);
		
		// Envoi de la réponse HTTP Response::HTTP_CREATED Response::HTTP_FOUND			
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_CREATED);
			$response->setContent(json_encode($resultat['utilisateur']));
		}else{
			$response->setStatusCode(Response::HTTP_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Enregister les pronostics */
	$app->post('/Prognosis', function (Request $request) {

		// On récupère les données
		$pronostics = json_decode($request->getContent(), true);
				
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On enregistre les pronostics
		$resultat = save_prognosis($pronostics);
		
		// Envoi de la réponse HTTP Response::HTTP_ACCEPTED Response::HTTP_NOT_MODIFIED		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_ACCEPTED);
			$response->setContent(json_encode($resultat));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_MODIFIED);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Enregister un groupe */
	$app->post('/Groups', function (Request $request) {

		// On récupère les données
		$group = json_decode($request->getContent(), true);
						
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On enregistre les pronostics
		$resultat = add_group($group);
		
		// Envoi de la réponse HTTP Response::HTTP_ACCEPTED Response::HTTP_FOUND		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_ACCEPTED);
			$response->setContent(json_encode($resultat));
		}else{
			$response->setStatusCode(Response::HTTP_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Modifier un groupe */
	$app->post('/Groups/{id}', function (Request $request,$id) {

		// On récupère les données
		$group = json_decode($request->getContent(), true);
						
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On enregistre les pronostics
		$resultat = modify_group($group,$id);
		
		// Envoi de la réponse HTTP Response::HTTP_ACCEPTED Response::HTTP_FOUND		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_ACCEPTED);
			$response->setContent(json_encode($resultat));
		}else{
			$response->setStatusCode(Response::HTTP_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Modifier un groupe */
	$app->post('/Profil/{id}', function (Request $request,$id) {

		// On récupère les données
		$profil = json_decode($request->getContent(), true);
				
		/*$profil = Array();
		$profil['email'] = 'ssss';
		$profil['firstname'] = 'ssss';
		$profil['id_avatar'] = 43;
		$profil['lastname'] = 'ssss';
		$profil['pseudonyme'] = 'aborun';*/           
								
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On enregistre les pronostics
		$resultat = modify_profil($profil,$id);
		
		// Envoi de la réponse HTTP Response::HTTP_OK Response::HTTP_FOUND		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat));
		}else{
			$response->setStatusCode(Response::HTTP_FOUND);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	/* Modifier un groupe */
	$app->delete('/Groups/{id}', function (Request $request,$id) {

		// On récupère les paramètres
		$id_user = $request->get('id_user');
							
		// On cree la réponse HTTP
		$response = new Response();
		$response->headers->set("Access-Control-Allow-Origin","*");
		$response->headers->set("Access-Control-Allow-Methods","GET,POST,PUT,DELETE,OPTIONS");
		$response->headers->set("Access-Control-Allow-Headers","Content-Type");
		
		// On enregistre les pronostics
		$resultat = unsubcribe_group($id,$id_user);
		
		// Envoi de la réponse HTTP Response::HTTP_ACCEPTED Response::HTTP_NOT_MODIFIED		
		if( $resultat['status'] ){
			$response->setStatusCode(Response::HTTP_OK);
			$response->setContent(json_encode($resultat));
		}else{
			$response->setStatusCode(Response::HTTP_NOT_MODIFIED);
			$response->setContent(json_encode($resultat));
		}
		
		return $response;
	});
	
	$app->run();
?>