<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Evenement extends Cafe {
	
	
	public function __construct() {
		parent::__construct();
		
		// Chargemnent des module de codeigniter.
		$this->load->library('form_validation');
		
		// Chargement des modeles.
		$this->load->model('modelevenement');
		$this->load->model('modelzone');
		$this->load->model('modelcategorie');
		
		// Chargement des fichier javascript.
		$this->layout->ajouter_js('jquery-ui-1.8.17.min');
		$this->layout->ajouter_js('utilisateur/scriptDate');
		$this->layout->ajouter_js('utilisateur/CRUDEvenement');
		
		// Chargement d'un css particulier.
		$this->layout->ajouter_css('utilisateur/evenement');
		$this->layout->ajouter_css('jquery-ui-1.8.17.custom');
		
		// Mise en place de la sécurisation.
		$this->securiseAll();
	}


	public function index() {
		
		$this->liste();
		
	}
	
	/**
	 * Méthode Listing du CRUD. 
	 */
	public function liste() {
		// Chargement du css.
		$this->layout->ajouter_css('utilisateur/liste');
		
		// Récupération des données dans la base.
		$data['resultats']=$this->modelevenement->getEvenements();
		
		// Appelle de la vue.
		$this->layout->view('utilisateur/evenement/UEIndex', $data);
	}
	
	/**
	 * Méthode Read du CRUD.
	 * @param  $id : Id de la données à  afficher.
	 */
	public function voir($id) {
		// Chargement du css.
		$this->layout->ajouter_css('utilisateur/details');
		
		// Récupération des données sur la données corréspondant a l'id.
		$data['resultats']=$this->modelevenement->getEvenementParId( $id );
		
		if($data['resultats']) {
			$data['id'] = $id;
		
			// Récupération de la liste des zones.
			$data['listeZones'] = $this->modelzone->getZonesAvecCode($id);



			$categories = $this->modelcategorie->getCategorieDansEvenementToutBien($id);
			$data['listeCategorie'] = $categories;

			$data['listeCatgorieZone'] = Array();
			if($categories) {
				// on construit un tableau avec les id des catégorie pour récupérer tous les couples zone/catégorie.
				$idCategorie = Array();
				foreach($categories as $cate) {
					$idcategorie[] = $cate['db']->idcategorie;
				}
				$categoriesZones = $this->modelzone->getZoneParIdMultipleParEvenement ( $idcategorie, $id );

				// On construit le tableau qui va organiser les zones et les catégories.
				$listeCatgorieZone = Array();
				foreach($categoriesZones as $categorie) {
					$listeCatgorieZone[$categorie->idcategorie][$categorie->idzone] = 1;
				}

				$data['listeCatgorieZone'] 	= $listeCatgorieZone;
			}
		}
		else {
			
			
		}
		
		
		// Appelle de la vue.
		$this->layout->view('utilisateur/evenement/UEVoir', $data);
	}
	
	/**
	 * Méthode Create du CRUD.
	 */
	public function ajouter($values='') {
		
		// Traitement.
		$data['resultats']=$this->modelevenement->getEvenements();
		$data['info'] = $values;
		
		// Appel de la vue.
		$this->layout->view('utilisateur/evenement/UEAjout', $data);
		
	}
	
	
	/**
	 * Méthode de traitement pour l'ajout.
	 */
	public function exeAjouter() {
		$config = array(
			array(
				'field'   => 'nom',
				'label'   => 'Nom', 
				'rules'   => 'required'
			),
			array(
				'field'   => 'datedebut',
				'label'   => 'Date de début', 
				'rules'   => 'required'
			),
			array(
				'field'   => 'datefin',
				'label'   => 'Date de fin', 
				'rules'   => 'required'
			)
		);
		$this->form_validation->set_rules($config);
		
		
		$nom 		= $this->input->post('nom');
		$datedebut 	= $this->input->post('datedebut');
		$datefin 	= $this->input->post('datefin');
		$textmail	= $this->input->post('textmail');
		
		if(!empty ($datedebut)) {
			$datedebutTstmp= date_to_timestamp($datedebut);
		}
		if(!empty ($datefin)) {
			$datefinTstmp  = date_to_timestamp($datefin);
		}
		 
		 
		
		if ($this->form_validation->run() == true && !empty ($datedebut) && !empty ($datedebut) && $datedebutTstmp <= $datefinTstmp) {
			
			if($this->input->post('choix') == "oui") {
				$idEvenement = $this->input->post('evenements');
			}
			else {
				$idEvenement = 0;
			}
				
			
			$this->modelevenement->ajouterAvecTextMail( $nom, $datedebutTstmp, $datefinTstmp, $textmail);
			
			$id = $this->modelevenement->lastId();
			
			if(!$this->session->userdata('idEvenementEnCours')) { 
				$this->session->set_userdata('idEvenementEnCours', $id);
				$this->session->set_userdata('libelleEvenementEnCours', $nom);
			}
			
			$this->donnees( $id, $idEvenement );
		}
		else {
			
			$values->nom		= $nom;
			$values->dateDebut	= $datedebut;
			$values->dateFin	= $datefin;
			$values->textmail	= $textmail;
			
			if(!empty ($datedebut) && !empty ($datefin)) {
				if($datedebutTstmp >= $datefinTstmp)
					$values->erreurDate = "La date de début doit être antérieure à la date de fin.";
			}
			else {
				$values->erreurDate = "Veuillez spécifier une date de début et une date de fin.";
			}
			
			if(empty($nom))
				$values->erreurNom = "Veuillez spécifier un nom.";
			
			$this->ajouter($values);
		}
	}
	

	/**
	 * Fonction pour mettre les zones et les catégories liées à  notre évènement.
	 */
	public function donnees( $id, $idEvenement=0 ) {
		
		$data['id'] = $id;
		
		if($idEvenement == 0) {
			$data['modeleEvenement'] = false;
		}
		else {
			$data['modeleEvenement'] = true;
		}
		
		// On récupère la liste des zones avec les codes zone de l'évènement.
		$data['listeZones'] = $this->modelzone->getZonesAvecCode($idEvenement);

		// On traite la récupération des catégorie de l'évènement modèle.
		$categories = $this->modelcategorie->getCategorieDansEvenementToutBien();
		$data['listeCategorie'] = $categories;

		$data['listeCatgorieZone'] = Array();
		
		if($data['modeleEvenement'] == true) {
			$idCategorie = Array();
			foreach($categories as $cate) {
				$idcategorie[] = $cate['db']->idcategorie;
			}
			$categoriesZones = $this->modelzone->getZoneParIdMultipleParEvenement ( $idcategorie, $idEvenement );

			// On construit le tableau qui va organiser les zones et les catégories.
			$listeCatgorieZone = Array();
			foreach($categoriesZones as $categorie) {
				$listeCatgorieZone[$categorie->idcategorie][$categorie->idzone] = 1;
			}
			$data['listeCatgorieZone'] 	= $listeCatgorieZone;
		}
		
		$this->layout->view('utilisateur/evenement/UEAjout2', $data);
		
	}
	
	
	public function exeDonnees( $id ) {
		// Récupération de la liste des zones.
		$zones = $this->modelzone->getZones();
		
		// recup liste des categorie.
		$categories = $this->input->post('name');
		
		$newentry = false;
		$newDonneesEvenement = Array();
		if(isset($categories) && !empty($categories)) {
			foreach( $categories as $categorie) {
				foreach($zones as $zone) {
					if($this->input->post($categorie . '_' . $zone->idzone) == 'on') {
						$entry = Array(
							'idzone'		=> $zone->idzone,
							'idcategorie'	=> $categorie,
							'idevenement'	=> $id,
							'codezone'		=> $this->input->post('code_' . $zone->idzone)
						);
						$newDonneesEvenement[] = $entry;
						$newentry = true;
					}
				}
			}
		}
				
		// ajout dans la base des données.
		if( $newentry ) {
			$this->modelevenement->ajouterDonnees( $newDonneesEvenement );
		}
		// Appel de la vue.
			$data['titre']		= 'Ajout';
			$data['message']	= 'Votre évènement a bien été ajouté.';
			$data['redirect'] = 'evenement/liste';
			$this->layout->view('utilisateur/UMessage', $data);	
		
		
		// on affiche le message de reussite de l'ajout de l'evenement.
		//display_tab($newDonneesEvenement);
	}
	
	
	/**
	 * Méthode Update du CRUD.
	 * @param $id : Id de la données à  modifiée.
	 */
	public function modifier($id, $re=false) {
		
		// Traitement
		$data['id'] = $id;
		$data['modeleEvenement'] = true;
		
		// On récupère la liste des zones avec les codes zone de l'évènement.
		$data['listeZones'] = $this->modelzone->getZonesAvecCode($id);

		// On traite la récupération des catégorie de l'évènement modèle.
		$categories = $this->modelcategorie->getCategorieDansEvenementToutBien();
		$data['listeCategorie'] = $categories;
		
		
		if($re) {
			$data['nom'] 		= $re['nom'];
			$data['datedebut'] 	= date_to_timestamp($re['datedebut']);
			$data['datefin'] 	= date_to_timestamp($re['datefin']);
			$data['textmail']	= $re['textmail'];
			
			$data['listeCategorieZone'] = $re['coupleZoneCategorie'];
			
			$data['info'] = $re['info'];
			
		}
		else {
			$reponse = $this->modelevenement->getEvenementParId($id);
			if($reponse) {
				$data['nom'] 		= $reponse[0]->libelleevenement;
				$data['datedebut'] 	= $reponse[0]->datedebut;
				$data['datefin'] 	= $reponse[0]->datefin;
				$data['textmail']	= $reponse[0]->textmail;

				$data['listeCategorieZone'] = Array();
				if($categories) {
					// Construction du tableau de couple categorie / zone pour remplir les case a cocher.
					$data['listeCatgorieZone'] = Array();
					$idCategorie = Array();
					foreach($categories as $cate) {
						$idcategorie[] = $cate['db']->idcategorie;
					}
					$categoriesZones = $this->modelzone->getZoneParIdMultipleParEvenement ( $idcategorie, $id );

					// On construit le tableau qui va organiser les zones et les catégories.
					$listeCatgorieZone = Array();
					foreach($categoriesZones as $categorie) {
						$listeCatgorieZone[$categorie->idcategorie][$categorie->idzone] = 1;
					}
					$data['listeCategorieZone'] = $listeCatgorieZone;
				}
			}
			
		}
		
		// Appelle de la vue.
		$this->layout->view('utilisateur/evenement/UEModification',$data);
	}
	
	/**
	 * Méthode traitement de l'Update du CRUD.
	 */
	public function exeModifier($id) {
		$data['id'] = $id;
		
		
		$config = array(
			array(
				'field'   => 'nom',
				'label'   => 'Nom', 
				'rules'   => 'required'
			),
			array(
				'field'   => 'datedebut',
				'label'   => 'Date de début', 
				'rules'   => ''
			),
			array(
				'field'   => 'datefin',
				'label'   => 'Date de fin', 
				'rules'   => ''
			)
		);
		$this->form_validation->set_rules($config);
		
		$nom 		= $this->input->post('nom');
		$datedebut 	= $this->input->post('datedebut');
		$datefin 	= $this->input->post('datefin');
		$textmail	= $this->input->post('textmail');
		
		$datedebutTstmp= date_to_timestamp($datedebut);
		$datefinTstmp  = date_to_timestamp($datefin);
		
		// Récupération de la liste des zones.
		$zones = $this->modelzone->getZones();
		
		if ($this->form_validation->run() == true && $datedebutTstmp < $datefinTstmp ) {
			// on vire les anciens paramètres.
			$this->modelevenement->supprimerParametreParEvenement( $id );
			
			// recup liste des categorie.
			$categories = $this->input->post('name');
			
			$newentry = false;
			$newDonneesEvenement = Array();
			foreach( $categories as $categorie) {
				foreach($zones as $zone) {
					if($this->input->post($categorie . '_' . $zone->idzone) == 'on') {
						$entry = Array(
							'idzone'		=> $zone->idzone,
							'idcategorie'	=> $categorie,
							'idevenement'	=> $id,
							'codezone'		=> $this->input->post('code_' . $zone->idzone)
						);
						$newDonneesEvenement[] = $entry;
						$newentry = true;
					}
				}
			}
				
			// ajout dans la base des données.
			if( $newentry ) {
				$this->modelevenement->ajouterDonnees( $newDonneesEvenement );
			}
			
			// Si la verification est ok.
			$resultat = $this->modelevenement->modifierAvecTextMail($nom, $datedebutTstmp, $datefinTstmp, $id, $textmail);
			
			if($this->session->userdata('idEvenementEnCours') == $id) {
				$evenementsEnCours = $this->modelevenement->getEvenementEnCours();
				if(!empty($evenementsEnCours)) {
					$this->session->set_userdata('idEvenementEnCours', $evenementsEnCours[0]->idevenement);
					$this->session->set_userdata('libelleEvenementEnCours', $evenementsEnCours[0]->libelleevenement);
				}
				else {
					$this->session->unset_userdata('idEvenementEnCours');
					$this->session->unset_userdata('libelleEvenementEnCours');
				}
			}
			
			$data['titre']		= 'Modification';
			$data['message']	= 'Votre évènement a bien été modifié.';
			$data['redirect'] 	= 'evenement/liste';
			$this->layout->view('utilisateur/UMessage', $data);
			
		}
		else {
			// Si la vérification a échouée.
			$donnees['nom'] 		= $nom;
			$donnees['datedebut'] 	= $datedebut;
			$donnees['datefin'] 	= $datefin;
			$donnees['textmail']	= $textmail;
			
			$donnees['coupleZoneCategorie'] = Array();
			//======================================
			// Construction du tableau de couple categorie / zone pour remplir les case a cocher.
			$categories = $this->input->post('name');
			$newDonneesEvenement = Array();
			foreach( $categories as $categorie) {
				foreach($zones as $zone) {
					if($this->input->post($categorie . '_' . $zone->idzone) == 'on') {
						$donnees['coupleZoneCategorie'][$categorie][$zone->idzone] = 1;
					}
				}
			}
			//var_dump($donnees['coupleZoneCategorie']);
			//==========================================
			$values = '';
			if(!empty ($datedebut) && !empty ($datefin)) {
				if($datedebutTstmp >= $datefinTstmp)
					$values->erreurDate = "La date de début doit être antérieure à la date de fin.";
			}
			else {
				$values->erreurDate = "Veuillez spécifier une date de début et une date de fin.";
			}
			
			if(empty($nom))
				$values->erreurNom = "Veuillez spécifier un nom.";
			
			$donnees['info'] = $values;
			
			$this->modifier($id, $donnees);
		}
	}
	
	
	/**
	 * Methode Delete du CRUD.
	 * @param $id : Id de la données a supprimer.
	 */
	public function supprimer($id) {
	   
		$this->modelevenement->supprimer($id);
		
		if($this->session->userdata('idEvenementEnCours') == $id) {
			$evenementsEnCours = $this->modelevenement->getEvenementEnCours();
			if(!empty($evenementsEnCours)) {
				$this->session->set_userdata('idEvenementEnCours', $evenementsEnCours[0]->idevenement);
				$this->session->set_userdata('libelleEvenementEnCours', $evenementsEnCours[0]->libelleevenement);
			}
			else {
				$this->session->unset_userdata('idEvenementEnCours');
				$this->session->unset_userdata('libelleEvenementEnCours');
			}
		}
		
		$data['titre']		= 'Suppression';
		$data['message']	= 'Votre évènement a bien été supprimé.';
		$data['redirect'] 	= 'evenement/liste';
		$this->layout->view('utilisateur/UMessage', $data);

	}
	
	
	 public function valide() {
		// TODO
		$this->layout->view('utilisateur/evenement/UEVoir');
		
	}
	
	public function Avalide() {
		// TODO
		$this->layout->view('utilisateur/evenement/UEVoir');
		
	}

}