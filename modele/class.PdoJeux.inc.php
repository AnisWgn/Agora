<?php

/**
 *  AGORA
 * 	©  Logma, 2019
 * @package default
 * @author MD
 * @version    1.0
 * @link       http://www.php.net/manual/fr/book.pdo.php
 * 
 * Classe d'accès aux données. 
 * Utilise les services de la classe PDO
 * pour l'application AGORA
 * Les attributs sont tous statiques,
 * $monPdo de type PDO 
 * $monPdoJeux qui contiendra l'unique instance de la classe
 */
class PdoJeux {

    private static $monPdo;
    private static $monPdoJeux = null;

    /**
     * Constructeur privé, crée l'instance de PDO qui sera sollicitée
     * pour toutes les méthodes de la classe
     */
    private function __construct() {
		// A) >>>>>>>>>>>>>>>   Connexion au serveur et à la base
		try {   
			// encodage
			$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''); 
			// Crée une instance (un objet) PDO qui représente une connexion à la base
			PdoJeux::$monPdo = new PDO(DSN,DB_USER,DB_PWD, $options);
			// configure l'attribut ATTR_ERRMODE pour définir le mode de rapport d'erreurs 
			// PDO::ERRMODE_EXCEPTION: émet une exception 
			PdoJeux::$monPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// configure l'attribut ATTR_DEFAULT_FETCH_MODE pour définir le mode de récupération par défaut 
			// PDO::FETCH_OBJ: retourne un objet anonyme avec les noms de propriétés 
			//     qui correspondent aux noms des colonnes retournés dans le jeu de résultats
			PdoJeux::$monPdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
		}
		catch (PDOException $e)	{	// $e est un objet de la classe PDOException, il expose la description du problème
			die('<section id="main-content"><section class="wrapper"><div class = "erreur">Erreur de connexion à la base de données !<p>'
				.$e->getmessage().'</p></div></section></section>');
		}
    }
	
    /**
     * Destructeur, supprime l'instance de PDO  
     */
    public function _destruct() {
        PdoJeux::$monPdo = null;
    }

    /**
     * Fonction statique qui crée l'unique instance de la classe
     * Appel : $instancePdoJeux = PdoJeux::getPdoJeux();
     * 
     * @return l'unique objet de la classe PdoJeux
     */
    public static function getPdoJeux() {
        if (PdoJeux::$monPdoJeux == null) {
            PdoJeux::$monPdoJeux = new PdoJeux();
        }
        return PdoJeux::$monPdoJeux;
    }

    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES PLATEFORMES
    //
    //==============================================================================

    public function getLesGenresComplet() {
$requete = 'SELECT G.idGenre as identifiant, G.libGenre as libelle, G.idSpecialiste AS
idSpecialiste, CONCAT(M.prenomMembre, " ", M.nomMembre) AS nomSpecialiste,
(SELECT COUNT(refJeu) FROM jeu_video AS J WHERE J.idGenre = G.idGenre) AS nbJeux
FROM genre AS G
LEFT OUTER JOIN membre AS M ON G.idSpecialiste = M.idMembre
ORDER BY G.libGenre';
try {
$resultat = PdoJeux::$monPdo->query($requete);
$tbGenres = $resultat->fetchAll();
return $tbGenres;
}
catch (PDOException $e) {
die('<div class = "erreur">Erreur dans la requête !<p>'
.$e->getmessage().'</p></div>');
}
}
/**
* Retourne l'utilisateur authentifié selon le login et le mot de passe haché.
*
* @param string $login
* @param string $mdpHash
* @return object|false l'objet utilisateur (avec alias idPersonne, nomPersonne, prenomPersonne) ou false si non trouvé
*/
public function getLesPersonnes(string $login, string $mdpHash) {
$requete = 'SELECT 
idMembre AS idPersonne,
nomMembre AS nomPersonne,
prenomMembre AS prenomPersonne
FROM membre
WHERE loginMembre = :login AND mdpMembre = :mdp';
try {
$stmt = PdoJeux::$monPdo->prepare($requete);
$stmt->bindParam(':login', $login, PDO::PARAM_STR);
$stmt->bindParam(':mdp', $mdpHash, PDO::PARAM_STR);
$stmt->execute();
$utilisateur = $stmt->fetch();
return $utilisateur ?: false;
}
catch (PDOException $e) {
die('<div class = "erreur">Erreur dans la requête !<p>'
.$e->getmessage().'</p></div>');
}
}

/**
* Retourne l'identifiant et le nom complet de tous les membres (alias attendu par les contrôleurs)
*
* @return le tableau d'objets
*/
public function getLesMembres() {
$requete = 'SELECT idMembre as identifiant, CONCAT(prenomMembre, " ", nomMembre) AS
libelle
FROM membre
ORDER BY nomMembre';
try {
$resultat = PdoJeux::$monPdo->query($requete);
$tbMembres = $resultat->fetchAll();
return $tbMembres;
}
catch (PDOException $e) {
die('<div class = "erreur">Erreur dans la requête !<p>'
.$e->getmessage().'</p></div>');
}
}

    /**
     * Retourne toutes les plateformes sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Plateforme)
     */

    public function getLesPlateformes(): array {
  		$requete =  'SELECT idPlateforme as identifiant, libPlateforme as libelle 
                        FROM plateforme 
                        ORDER BY libPlateforme';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbPlateformes  = $resultat->fetchAll();    
            return $tbPlateformes;
        }
        catch (PDOException $e)	{  
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }

    }

    /**
     * Ajoute une nouvelle plateforme avec le libellé donné en paramètre
     * 
     * @param string $libPlateforme : le libelle de la plateforme à ajouter
     * @return int l'identifiant de la plateforme crée
     */

    public function ajouterPlateforme(string $libPlateforme): int {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO plateforme "
                    . "(idPlateforme, libPlateforme) "
                    . "VALUES (0, :unLibPlateforme) ");
            $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return PdoJeux::$monPdo->lastInsertId();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Modifie le libellé de la plateforme donnée en paramètre
     * 
     * @param int $idPlateforme : l'identifiant de la plateforme à modifier  
     * @param string $libPlateforme : le libellé modifié
     */

    public function modifierPlateforme(int $idPlateforme, string $libPlateforme): void { // Correction de "funtion" en "function"
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE plateforme "
                    . "SET libPlateforme = :unLibPlateforme "
                    . "WHERE plateforme.idPlateforme = :unIdPlateforme");
            $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibPlateforme', $libPlateforme, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Supprime la plateforme donnée en paramètre
     * 
     * @param int $idPlateforme :l'identifiant de la plateforme à supprimer 
     */

    public function supprimerPlateforme(int $idPlateforme): void {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM plateforme "
                    . "WHERE plateforme.idPlateforme = :unIdPlateforme");
            $requete_prepare->bindParam(':unIdPlateforme', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES JEUX
    //
    //==============================================================================

    /**
     * Retourne tous les jeux avec les détails des tables associées.
     * 
     * @return array le tableau d'objets (Jeu)
     */
    public function getLesJeux(): array {
        $requete = 'SELECT 
                        jeu_video.refJeu,
                        jeu_video.nom,
                        jeu_video.prix,
                        jeu_video.dateParution,
                        genre.libGenre,
                        marque.nomMarque,
                        plateforme.libPlateforme,
                        pegi.ageLimite
                    FROM jeu_video
                    INNER JOIN genre ON jeu_video.idGenre = genre.idGenre
                    INNER JOIN marque ON jeu_video.idMarque = marque.idMarque
                    INNER JOIN plateforme ON jeu_video.idPlateforme = plateforme.idPlateforme
                    INNER JOIN pegi ON jeu_video.idPegi = pegi.idPegi
                    ORDER BY jeu_video.nom';
        try {
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbJeux = $resultat->fetchAll();
            return $tbJeux;
        } catch (PDOException $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getMessage() . '</p></div>');
        }
    }

    /**
     * Ajoute un nouveau jeu dans la base de données.
     * 
     * @param string $refJeu
     * @param string $nomJeu
     * @param float $prix
     * @param string $dateParution
     * @param int $idGenre
     * @param int $idMarque
     * @param int $idPlateforme
     * @param int $idPegi
     * @return string la référence du jeu créé
     */
    public function ajouterJeu(string $refJeu, string $nomJeu, float $prix, string $dateParution, int $idGenre, int $idMarque, int $idPlateforme, int $idPegi): string {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare(
                "INSERT INTO jeu_video (refJeu, nom, prix, dateParution, idGenre, idMarque, idPlateforme, idPegi) 
                 VALUES (:ref, :nom, :prix, :datep, :idg, :idm, :idp, :idpegi)"
            );
            $requete_prepare->bindParam(':ref', $refJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':nom', $nomJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':prix', $prix, PDO::PARAM_STR);
            $requete_prepare->bindParam(':datep', $dateParution, PDO::PARAM_STR);
            $requete_prepare->bindParam(':idg', $idGenre, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idm', $idMarque, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idp', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idpegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->execute();
            return $refJeu;
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getMessage() . '</p></div>');
        }
    }

    /**
     * Modifie les informations d'un jeu.
     *
     * @param string $refJeu
     * @param string $nomJeu
     * @param float $prix
     * @param string $dateParution
     * @param int $idGenre
     * @param int $idMarque
     * @param int $idPlateforme
     * @param int $idPegi
     */
    public function modifierJeu(string $refJeu, string $nomJeu, float $prix, string $dateParution, int $idGenre, int $idMarque, int $idPlateforme, int $idPegi): void {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare(
                "UPDATE jeu_video 
                 SET nom = :nom, prix = :prix, dateParution = :datep, idGenre = :idg, idMarque = :idm, idPlateforme = :idp, idPegi = :idpegi
                 WHERE refJeu = :ref"
            );
            $requete_prepare->bindParam(':ref', $refJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':nom', $nomJeu, PDO::PARAM_STR);
            $requete_prepare->bindParam(':prix', $prix, PDO::PARAM_STR);
            $requete_prepare->bindParam(':datep', $dateParution, PDO::PARAM_STR);
            $requete_prepare->bindParam(':idg', $idGenre, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idm', $idMarque, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idp', $idPlateforme, PDO::PARAM_INT);
            $requete_prepare->bindParam(':idpegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getMessage() . '</p></div>');
        }
    }

    /**
     * Supprime un jeu de la base de données.
     * 
     * @param string $refJeu : la référence du jeu à supprimer
     */
    public function supprimerJeu(string $refJeu): void {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM jeu_video WHERE refJeu = :ref");
            $requete_prepare->bindParam(':ref', $refJeu, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                . $e->getMessage() . '</p></div>');
        }
    }


    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES Marques
    //
    //==============================================================================
	
    /**
     * Retourne tous les genres sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Genre)
     */

    public function getLesMarques(): array {
  		$requete =  'SELECT idMarque as identifiant, nomMarque as libelle 
                        FROM marque 
                        ORDER BY nomMarque';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbMarques  = $resultat->fetchAll();    
            return $tbMarques;
        }
        catch (PDOException $e)	{  
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }

    }
    /**
     * Ajoute une nouvelle marque avec le nom donné en paramètre
     * 
     * @param string $nomMarque : le nom de la marque à ajouter
     * @return int l'identifiant de la marque crée
     */

    public function ajouterMarque(string $nomMarque): int {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO marque "
                    . "(idMarque, nomMarque) "
                    . "VALUES (0, :unNomMarque) ");
            $requete_prepare->bindParam(':unNomMarque', $nomMarque, PDO::PARAM_STR);
            $requete_prepare->execute();
            // récupérer l'identifiant crée
            return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Modifie le nom de la marque donnée en paramètre
     * 
     * @param int $idMarque : l'identifiant de la marque à modifier  
     * @param string $nomMarque : le nom modifié
     */

    public function modifierMarque(int $idMarque, string $nomMarque): void { // Correction de "funtion" en "function"
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE marque "
                    . "SET nomMarque = :unNomMarque "
                    . "WHERE marque.idMarque = :unIdMarque");
            $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unNomMarque', $nomMarque, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Supprime la marque donnée en paramètre
     * 
     * @param int $idMarque :l'identifiant de la marque à supprimer 
     */

    public function supprimerMarque(int $idMarque): void {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM marque "
                    . "WHERE marque.idMarque = :unIdMarque");
            $requete_prepare->bindParam(':unIdMarque', $idMarque, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    //==============================================================================
    //
    //	METHODES POUR LA GESTION DES PEGIS
    //
    //==============================================================================
    /**
     * Retourne tous les pegi sous forme d'un tableau d'objets 
     * 
     * @return array le tableau d'objets  (Pegi)
     */

    public function getLesPegis(): array {
  		$requete =  'SELECT idPegi as identifiant, ageLimite as age, descPegi as description
                        FROM pegi
                        ORDER BY ageLimite';
        try	{
            $resultat = PdoJeux::$monPdo->query($requete);
            $tbPegis  = $resultat->fetchAll();
            return $tbPegis;
        }
        catch (PDOException $e)	{
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Ajoute un nouveau pegi avec les informations données en paramètre
     * 
     * @param string $age : l'age du pegi à ajouter
     * @param string $description : la description du pegi à ajouter
     * @return int l'identifiant du pegi crée
     */

    public function ajouterPegi(string $age, string $description): int {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO pegi (idPegi, ageLimite, descPegi) VALUES (0, :unAge, :uneDescription)");
            $requete_prepare->bindParam(':unAge', $age, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneDescription', $description, PDO::PARAM_STR);
            $requete_prepare->execute();
            return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) { // Ajout du catch et de l'accolade fermante
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    /**
     * Supprime le genre donné en paramètre
     * 
     * @param int $idGenre :l'identifiant du genre à supprimer 
     */
    public function supprimerPegis(int $idPegi): void {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM pegi "
                    . "WHERE pegi.idPegi = :unIdPegi");
            $requete_prepare->bindParam(':unIdPegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    public function modifierPegi(int $idPegi, string $age, string $description): void { // Correction de "funtion" en "function"
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE pegi "
                    . "SET ageLimite = :unAge, descPegi = :uneDescription "
                    . "WHERE pegi.idPegi = :unIdPegi");
            $requete_prepare->bindParam(':unIdPegi', $idPegi, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unAge', $age, PDO::PARAM_STR);
            $requete_prepare->bindParam(':uneDescription', $description, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
                .$e->getmessage().'</p></div>');
        }
    }

    public function getLesGenres(): array {
  		$requete =  'SELECT idGenre as identifiant, libGenre as libelle 
						FROM genre 
						ORDER BY libGenre';
		try	{	 
			$resultat = PdoJeux::$monPdo->query($requete);
			$tbGenres  = $resultat->fetchAll();	
			return $tbGenres;
		}
		catch (PDOException $e)	{  
			die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
		}
    }

	
	/**
	 * Ajoute un nouveau genre avec le libellé donné en paramètre
	 * 
	 * @param string $libGenre : le libelle du genre à ajouter
	 * @return int l'identifiant du genre crée
	 */
    public function ajouterGenre(string $libGenre): int {
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("INSERT INTO genre "
                    . "(idGenre, libGenre) "
                    . "VALUES (0, :unLibGenre) ");
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
            $requete_prepare->execute();
			// récupérer l'identifiant crée
			return PdoJeux::$monPdo->lastInsertId(); 
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
        }
    }
	
	
	 /**
     * Modifie le libellé du genre donné en paramètre
     * 
     * @param int $idGenre : l'identifiant du genre à modifier  
     * @param string $libGenre : le libellé modifié
     */
    public function modifierGenre(int $idGenre, string $libGenre): void { // C'est la bonne version de la fonction
        try {
            $requete_prepare = PdoJeux::$monPdo->prepare("UPDATE genre "
                    . "SET libGenre = :unLibGenre "
                    . "WHERE genre.idGenre = :unIdGenre");
            $requete_prepare->bindParam(':unIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->bindParam(':unLibGenre', $libGenre, PDO::PARAM_STR);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
        }
    }
	
	
	/**
     * Supprime le genre donné en paramètre
     * 
     * @param int $idGenre :l'identifiant du genre à supprimer 
     */
    public function supprimerGenre(int $idGenre): void {
       try {
            $requete_prepare = PdoJeux::$monPdo->prepare("DELETE FROM genre "
                    . "WHERE genre.idGenre = :unIdGenre");
            $requete_prepare->bindParam(':unIdGenre', $idGenre, PDO::PARAM_INT);
            $requete_prepare->execute();
        } catch (Exception $e) {
            die('<div class = "erreur">Erreur dans la requête !<p>'
				.$e->getmessage().'</p></div>');
        }
    }
	


}
?>