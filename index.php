<?php
/*
	Service Web pour l'application Android Aozan
	Paramètres acceptés :
		type : string
		user : int
		value : string
		value2 : string
	Retour au format JSON, dans l'élement - reponse -
*/

header('Content-type: application/json; charset=utf-8');
// Obligation de fournir le type de requête
if(isset($_GET['type'])) {
	// Récupère et traite les paramètres
	$type = $_GET['type'];

	// Connexion à la db
	$link = mysql_connect('mysqlserver','login','pass') or die('Cannot connect to the DB');
	mysql_select_db('aozan',$link) or die('Cannot select the DB');
	
	// Authentification
	if($type == "Authentification") {
		$typeQuery = 1;
		if(isset($_GET['user']) || isset($_GET['value'])) {
			$login = $_GET['user'];
			$password = $_GET['value'];
			// Création de la requete
			$query = "SELECT id, nom, login FROM utilisateur WHERE login = '".$login."' AND password = '".$password."'";
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());			
		} else {
			exit('Manque : user ou value (password)');
		}
	// Autres types de demandes
	} elseif($type == "GetListes") {
		if(isset($_GET['user'])) {
			$typeQuery = 1;
			// Récupération de l'utilisateur
			$idUser = intval($_GET['user']);
			// Création de la requete
			if(isset($_GET['admin'])) {
				$query = "SELECT id, nom FROM `liste`";
			} else {
				$query = "SELECT l.id, l.nom FROM `liste` l INNER JOIN `autorisation` a ON l.id = a.idListe WHERE a.idUtilisateur = ".$idUser;
			}
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : user');
		}
	} elseif($type == "GetElements") {
			$typeQuery = 1;
		if(isset($_GET['value'])) {
			// Récupération du numéro de liste
			$idListe = $_GET['value'];
			// Création de la requete
			$query = "SELECT id, nom FROM `element` WHERE idListe = ".$idListe;
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : value');
		}
	} elseif($type == "AddListe") {
		$typeQuery = 2;
		if(isset($_GET['value']) && isset($_GET['user'])) {
			// Récupération du numéro de liste
			$nom = $_GET['value'];
			// Récupération de l'utilisateur
			$idUser = intval($_GET['user']);
			// Création de la requete
			$query = "INSERT INTO `liste`(`nom`) VALUES ('".$nom."')";
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
			$id = mysql_insert_id();
			// Création de la requete
			$query = "INSERT INTO `autorisation` (`idUtilisateur`, `idListe`) VALUES ('".$idUser."', '".$id."');";
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : value, user');
		}
	} elseif($type == "AddElement") {
		$typeQuery = 2;
		if(isset($_GET['value']) && isset($_GET['value2'])) {
			// Récupération des params
			$nom = $_GET['value'];
			$idListe = $_GET['value2'];
			// Création de la requete
			$query = "INSERT INTO `element` (`nom`, `idListe`) VALUES ('".$nom."', '".$idListe."');";
			// Execution de la requete	
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : value, value2');
		}
	} elseif($type == "DelListe") {
		$typeQuery = 2;
		if(isset($_GET['value'])) {
			$idListe = $_GET['value'];
			$query = "DELETE FROM `liste` WHERE id = '".$idListe."'";
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
			$query = "DELETE FROM `autorisation` WHERE idListe = '".$idListe."'";
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : value');
		}
	}  elseif($type == "DelElement") {
		$typeQuery = 2;
		if(isset($_GET['value'])) {
			$idElement = $_GET['value'];
			$query = "DELETE FROM `element` WHERE id = '".$idElement."'";
			$results = mysql_query($query,$link) or die('Erreur BDD : ' . mysql_error());
		} else {
			exit('Manque : value');
		}
	} else {
		exit('Incorrect : type');
	}


	if($typeQuery == 1) {
		// Rangement de la réponse
		$retour = array();
		if(mysql_num_rows($results)) {
			while($result = mysql_fetch_assoc($results)) {
				$retour[] = $result;
			}
			// Permet de conserver les accents au décodage
			array_walk_recursive( $retour, function (&$entry) { $entry = utf8_encode($entry); } );
		} else {
			exit('Empty');
		}
	} elseif ($typeQuery == 2) {
		if($results) {
			$retour = true;
		} else {
			$retour = false;
		}
	}
	
	// Affichage de la réponse en json
	echo json_encode(array('reponse'=>$retour));

	// Déconnexion de la db à la fin
	@mysql_close($link);
}
else {
	exit('Manque : type');
}
?>