<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Inscription extends Chocolat {

	/**
	 * constucteur de notre classe de base.
	 */
	public function __construct() {
		parent::__construct();

		// Charge la librairie de validation de formulaire
		$this->load->library('form_validation');

		// Charge la librairie mail
		$this->load->library('email');

		// Chargement du css.

		// Chargement du modele.
		$this->load->model('modelaccreditation');
		$this->load->model('modelevenement');
		$this->load->model('modelcategorie');
		$this->load->model('modellambda');
		$this->load->model('modelclient');
		$this->load->model('modelzone');
		$this->load->model('modelutilisateur');

		// Chargement du fichier de langue
		$this->load->helper('language');
		$this->chargerLangue();
	}

	public function index() {
		$this->entry();

	}

	public function entry() {
		$this->layout->ajouter_js('lambda/script');

		$data['events'] 	= $this->modelevenement->getEvenementEnCours();
		$data['idcategorie'] = $this->modelcategorie->getIdPresse();
		$data['listeSurCategorie'] 	= $this->modelcategorie->getCategoriesMeresSaufpresse();
		$data['lang'] = $this->session->userdata('lang');
		$data['baseUrl'] = base_url();

		$this->layout->view('presse/LPageEntre',$data);
	}


	/**
	 * Méthode pour le changement de langue
	 * @param $langage
	 * @param $url
	 */
	public function changerLangage($langage, $url) {

		if($langage === 'fra') {
			$this->session->set_userdata('lang', 'fra');
		}
		else if($langage === 'gbr') {
			$this->session->set_userdata('lang', 'gbr');
		}
		$urlOk = str_replace(':', '/', $url);
		$this->chargerLangue();
		redirect($urlOk);

	}

	public function chargerLangue() {

		if($this->session->userdata('lang')) {

			if($this->session->userdata('lang') == 'fra') {
				$this->config->set_item('language', 'french');
				$this->lang->load('fr');
			}
			else if($this->session->userdata('lang') == 'gbr') {
				$this->config->set_item('language', 'english');
				$this->lang->load('en');
			}
		}
		else {
			$this->config->set_item('language', 'french');
			$this->lang->load('fr');
		}
	}


	/**
	 * Méthode pour afficher le formulaire pour l'ajout individuelle.
	 * Comprend également le traitement complet.
	 * @param string $event
	 */
	public function ajouter($event='') {

		// Chargement du js.
		$this->layout->ajouter_js('lambda/script');
		$this->layout->ajouter_js('jpegcam/webcam');

		// variable pour transmettre des données à la vue.
		$data = Array();
		$data['lang'] = $this->session->userdata('lang');

		// On regle les paramètres du formulaire.

		$this->form_validation->set_message('required', $this->lang->line('champRequis'));
		$this->form_validation->set_message('valid_email', $this->lang->line('emailValide'));
		$this->form_validation->set_error_delimiters('<p class="error_message" > *', '</p>');

		// On définie les règles de validation du formulaire.
		$config = array(
			array(
				'field'   => 'nom',
				'label'   => $this->lang->line('nom'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'prenom',
				'label'   => $this->lang->line('prenom'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'pays',
				'label'   => $this->lang->line('pays'),
				'rules'   => ''
			),
			array(
				'field'   => 'tel',
				'label'   => $this->lang->line('tel'),
				'rules'   => ''
			),
			array(
				'field'   => 'fonction',
				'label'   => $this->lang->line('fonction'),
				'rules'   => ''
			),
			array(
				'field'   => 'civilite',
				'label'   => $this->lang->line('civilite'),
				'rules'   => ''
			),
			array(
				'field'   => 'categorie',
				'label'   => $this->lang->line('categorie'),
				'rules'   => ''
			),
			array(
				'field'   => 'titre',
				'label'   => $this->lang->line('societe'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'mail',
				'label'   => $this->lang->line('mail'),
				'rules'   => 'required|valid_email'
			)
		);

		$this->form_validation->set_rules($config);


		if ($this->form_validation->run() == false) {

			$data['event_id'] = $event;

			$data['event_info'] = $this->modelevenement->getEvenementParId( $event );

			$data['listePays'] = $this->modellambda->listePays();

			$data['listeCategorie'] = $this->listeCategorieToDisplay( $event );

			$this->layout->view('lambda/LIndividuelle', $data);

		}
		else {

			$verification = $this->modelaccreditation->verificationAccred(
					$event,
					strtoupper($this->input->post('nom')),
					$this->input->post('prenom'),
					$this->input->post('pays')
			);

			if(!$verification) {
				$values = Array (
					'nom' 		=> strtoupper($this->input->post('nom')),
					'prenom' 	=> $this->input->post('prenom'),
					'pays' 		=> $this->input->post('pays'),
					'mail' 		=> $this->input->post('mail')
				);

				// On gère les champs facultatif.
				$tel = $this->input->post('tel');
				if(isset($tel) && !empty($tel)) {
					$values['tel'] = $tel;
				}

				$organisme = $this->input->post('titre');
				if(isset($organisme) && !empty($organisme)) {
					$values['organisme'] = $organisme;
				}

				$categories = $this->input->post('categorie');
				$categorie = null;
				foreach($categories as $cat) {
					if($cat != "-1")
						$categorie = $cat;
					else
						$categorie = -1;
				}

				// On vérifie si le client existe dans la base.
				$idClient = $this->modelclient->verifierClient($values['nom'], $values['prenom'], $values['mail']);

				if(!$idClient) {
					//Insertion dans la base.
					$this->modelclient->ajouter($values);
					$idClient = $this->modelclient->lastId();
				}

				$accredData = Array(
					'idcategorie'		=> $categorie,
					'idevenement'		=> $event,
					'idclient'			=> $idClient,
					'etataccreditation'	=> ACCREDITATION_A_VALIDE,
					'dateaccreditation' => time()
				);

				$fonction = $this->input->post('fonction');
				if(isset($fonction) && !empty($fonction)) {
					$accredData['fonction'] = $fonction;
				}

				// On ajoute une nouvelle accréditation dans la base et on récupère son ID.
				$idNewAccred = $this->modelaccreditation->ajouter($accredData);

				// Si une catégorie a été sélectionnée
				if($categorie != -1) {
					// On crée l'association entre cette accréditation et les zones.
					$this->AssociationZoneAccred( $idNewAccred, $categorie, $event );
				}


				//upload ou webcam
				$webcam = $this->input->post('photo_webcam');
				if($webcam != null)
					rename('./assets/images/' . $webcam, UPLOAD_DIR . $idClient . '.jpg');


				if($_FILES['photo_file']['size'] != 0)
					$this->upload($idClient);

				$cat = $this->modelcategorie->getCategorieMereid($accredData['idcategorie']);

				$evenement = $this->modelevenement->getEvenementParId($event);

				// Préparation et envoi du mail de confirmation
				$this->email->from($this->modelutilisateur->getConfig('mail_expediteur'), $this->modelutilisateur->getConfig('nom_expediteur')); // L'adresse qui enverra le mail
				$this->email->to($values['mail']); // Le destinataire du mail
				$this->email->bcc($this->modelutilisateur->getConfig('mail_copie')); // Placer ici l'adresse de Courchevel qui recevra une copie du mail
				
				// Le sujet du mail
				$this->email->subject($this->modelutilisateur->getConfig('objet_mail'));

				// Le contenu du mail
				$contenuMail = 	'<html>' .
									'<head></head>' .
									'<body>' .
										'<p>' . $this->modelutilisateur->getConfig('cher') . $values['prenom'] . ' ' . $values['nom'] . ' / ' . $this->modelutilisateur->getConfig('dear') . $values['prenom'] . ' ' . $values['nom'] . ', </p>' .
										$this->modelutilisateur->getConfig('intro_mail') .
										'<table>' .
											'<tr>' .
												'<td>NOM / LASTNAME</td><td>' . $values['nom'] . '</td>' .
											'</tr>' .
											'<tr>' .
												'<td>PRENOM / FIRSTNAME</td><td>' . $values['prenom'] . '</td>' .
											'</tr>' .
											'<tr>' .
												'<td>PAYS / COUNTRY</td><td>' . $values['pays'] . '</td>' .
											'</tr>' ;
											if(isset($values['tel']) && !empty($values['tel'])) {
				$contenuMail .=					'<tr>' .
													'<td>TELEPHONE / PHONE NUMBER</td><td>' . $values['tel'] . '</td>' .
												'</tr>' ;
											}
											else {
				$contenuMail .=					'<tr>' .
													'<td>TELEPHONE / PHONE NUMBER</td><td>Pas de numéro de téléphone / No phone number</td>' .
												'</tr>' ;
											}

				$contenuMail .=				'<tr>' .
												'<td>MAIL</td><td>' . $values['mail'] . '</td>' .
											'</tr>' ;
											if(isset($cat) && !empty($cat)) {
				$contenuMail .=					'<tr>' .
													'<td>CATEGORIE / CATEGORY</td><td>' . $cat[0]->libellecategorie . '</td>' .
												'</tr>' ;
											}
											else {
				$contenuMail .=					'<tr>' .
													'<td>CATEGORIE / CATEGORY</td><td>Pas de catégorie définie / No defined category</td>' .
												'</tr>' ;
											}
				$contenuMail .=				'<tr>' .
												'<td>ORGANISME / COMPANY</td><td>' . $values['organisme'] . '</td>' .
											'</tr>' ;
											if(isset($accredData['fonction']) && !empty($accredData['fonction'])) {
				$contenuMail .=					'<tr>' .
													'<td>FONCTION / FUNCTION</td><td>' . $accredData['fonction'] . '</td>' .
												'</tr>' ;
											}
											else {
				$contenuMail .=					'<tr>' .
													'<td>FONCTION / FUNCTION</td><td>Pas de fonction définie / No defined function</td>' .
												'</tr>' ;
											}
				$contenuMail .=				'<tr>' .
												'<td>DATE D\'ENREGISTREMENT / REGISTRATION DATE</td><td>' . display_date($accredData['dateaccreditation']) . '</td>' .
											'</tr>' .
										'</table>' .
										$this->modelutilisateur->getConfig('traitement_mail');

				if($evenement[0]->textmail != '')
					$contenuMail .=		'<p>' . nl2br($evenement[0]->textmail) . '</p>';

				$contenuMail .=		$this->modelutilisateur->getConfig('signature_mail') .
									'</body>' .
								'</html>';

				// Inclusion du contenu dans le mail
				$this->email->message($contenuMail);

				// Envoi du mail
				$this->email->send();

				$data['titre']		= $this->lang->line('titreConfirmeDemande');
				$data['message']	= $this->lang->line('confirmeDemande');

			}
			else {
				$data['titre']		= $this->lang->line('titreDemandeNon');
				$data['message']	= $this->lang->line('demandeNon');

			}

			$this->layout->view('lambda/LMessage', $data);
		}
	}

	/**
	 * Fonction qui sert a créer les entrée entre des zones accéssible et une accréditation.
	 * @param $idAccred
	 * @param $idCategorie
	 * @param $idEvenement
	 */
	private function AssociationZoneAccred( $idAccred, $idCategorie, $idEvenement ) {

		// On récupère les zones de la catégorie pour cette évènement.
		$zones = $this->modelzone->getZoneParCategorieEtEvenement( $idCategorie, $idEvenement);

		// On construit notre array de couple zone/idAccreditation
		$zonesAccred = array();
		foreach($zones as $zone) {
			$zonesAccred[] = array(
				'idaccreditation' 	=> $idAccred,
				'idzone'			=> $zone->idzone
			);
		}

		// On lie ces zones à cette accréditation.
		$this->modelzone->ajouterZonesAccreditation( $zonesAccred );
	}


	private function listeCategorieToDisplay( $event ) {
		// Gestion pour les catégorie.
		$listeAllCategorie = $this->modelcategorie->getCategorieDansEvenementToutBien();
		$listeCategorieEvent = $this->modelcategorie->getCategoriesSaufPresse($event);
		$listeCategories = array();
		foreach($listeCategorieEvent as $categorie) {
			$listeCategories[] = $categorie->idcategorie;
		}
		$categories = array();
		foreach($listeAllCategorie as $cate) {
			if(in_array($cate['db']->idcategorie, $listeCategories)) {
				$categories[] = $cate;
			}
		}

		return $categories;
	}


	/**
	 * Méthode pour le formulaire pour la saisie du responsable d'une équipe.
	 * @param      $evenement
	 * @param bool $info
	 */
	public function groupe($evenement, $info=false) {
		// Chargement du js.
		$this->layout->ajouter_js('lambda/script');
		$this->layout->ajouter_js('jpegcam/webcam');

		$data['idEvenement']	= $evenement;
		$data['infoEvenement'] 	= $this->modelevenement->getEvenementParId($evenement);
		$data['listePays'] 		= $this->modellambda->listePays();
		$data['categorie'] = $this->listeCategorieToDisplay( $evenement );
		$data['values'] = $info;
		$data['lang'] = $this->session->userdata('lang');


		$this->layout->view('lambda/LGroupe', $data);

	}


	/**
	 * Méthode de traitement pour la saisie du responsable.
	 */
	public function exeGroupe() {

		$idEvenement = $this->input->post('evenement');

		// On regle les paramètres du formulaire.
		//$this->form_validation->set_message('required','Le groupe existe déjà.');
		$this->form_validation->set_message('required', 'Le champ %s est obligatoire.');
		$this->form_validation->set_message('valid_email', 'Veuillez rentrer un mail valide.');
		$this->form_validation->set_error_delimiters('<p class="error_message" > *', '</p>');



		// On définie les règles de validation du formulaire.
		$config = array(
			array(
				'field'   => 'groupe',
				'label'   => $this->lang->line('nomGroupe'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'nom',
				'label'   => $this->lang->line('nom'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'prenom',
				'label'   => $this->lang->line('prenom'),
				'rules'   => 'required'
			),
			array(
				'field'   => 'fonction',
				'label'   => $this->lang->line('fonction'),
				'rules'   => ''
			),
			array(
				'field'   => 'tel',
				'label'   => $this->lang->line('tel'),
				'rules'   => ''
			),
			array(
				'field'   => 'mail',
				'label'   => $this->lang->line('mail'),
				'rules'   => 'required|valid_email'
			),
			array(
				'field'	  => 'organisme',
				'label'   => $this->lang->line('organisme'),
				'rules'   => 'required'
			)
		);

		$this->form_validation->set_rules($config);

		// On vérifie si le nom de groupe existe déja.
		$groupe = $this->modelaccreditation->getGroupeExist( $this->input->post('groupe') );


		if ($this->form_validation->run() == false or $groupe) {

			$values             = '';

			$values->groupe 	= preg_replace('/[^a-zA-Z0-9-_ ]/i', '', $this->input->post('groupe'));
			$values->pays 		= $this->input->post('pays');
			$values->nom 		= strtoupper($this->input->post('nom'));
			$values->prenom 	= $this->input->post('prenom');
			$values->fonction 	= $this->input->post('fonction');
			$values->mail 		= $this->input->post('mail');
			$values->tel 		= $this->input->post('tel');
			$values->organisme	= $this->input->post('organisme');
			$values->categorie 	= $this->input->post('categorie');

			if($groupe)

			$values->error_groupe='Le groupe existe déjà';

			$this->groupe($idEvenement, $values);

		}
		else {

                    $data = array();
                    $data['groupe'] 		= preg_replace('/[^a-zA-Z0-9-_ ]/i', '', $this->input->post('groupe'));
                    $data['pays'] 		= $this->input->post('pays');
                    $data['nom'] 		= strtoupper($this->input->post('nom'));
                    $data['prenom'] 		= $this->input->post('prenom');
                    $data['fonction'] 		= $this->input->post('fonction');
                    $data['tel'] 		= $this->input->post('tel');
                    $data['mail'] 		= $this->input->post('mail');
		    $data['organisme']		= $this->input->post('organisme');
                    $data['evenement'] 		= $this->input->post('evenement');
                    $data['listeCategorie']     = $this->listeCategorieToDisplay( $idEvenement );
                    $data['webcam_ref']         = $this->input->post('photo_webcam');

                    // Gestion pour les catégorie.
                    $tab = $this->input->post('categorie');
                    $data['categorie'] = $tab;

                    // upload tmp du fichier
                    if(isset($_FILES['photo_file']) && $_FILES['photo_file']['name'] != '') {
                        $file = $_FILES['photo_file'];
                        $unik = time();
                        if(!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . 'tmp/' . $unik . '.jpg'))
                                die('Echec de l\'upload temporaire (inscription.php:exeGroupe) : ' . $_FILES['photo_file']['error']);
                        $data['photo_file'] = UPLOAD_DIR . 'tmp/' . $unik . '.jpg';
                    }

		    $webcam = $this->input->post('photo_webcam');
		    if($webcam != null)
			    $data['photo_webcam'] = './assets/images/' . $webcam;

                    $this->ajouterGroupe($data);
		}

	}


	/**
	 * Méthode pour l'ajout de tous les membres d"une équipe.
	 */
	public function ajouterGroupe($data) {

		$data['lang'] = $this->session->userdata('lang');

		$this->layout->ajouter_js('lambda/scriptGroupe');
		$this->layout->ajouter_js('jpegcam/webcam');

		$this->layout->ajouter_css('lambda/webcam-overlay');


		$this->layout->view('lambda/LGroupeDetails', $data);
	}


	/**
	 * Méthode pour le traitement de l'ajout des membres d'une équipe.
	 */
	public function exeAjouterGroupe() {
		// Ajout du référent.
		$ref = $data = $this->input->post('ref');
		$ref['nom'] = strtoupper($ref['nom']);
		unset($ref['categorie']);
		unset($ref['fonction']);
		unset($ref['groupe']);
		unset($ref['index']);

		// webcam
		$webcam = $ref['photo_webcam'];
		unset($ref['photo_webcam']);

		// file
		$file = $ref['photo_file'];
		unset($ref['photo_file']);

		// ajout ref
		$id = $this->modelclient->ajouter($ref);

		// ajout photo webcam
		if(!empty($webcam))
			rename($webcam, UPLOAD_DIR . $id . '.jpg');

		// upload fichier
		if(!empty($file)) {
			rename($file, UPLOAD_DIR . $id . '.jpg');
			$this->load->helper('image');
			resizeWidthRatio(UPLOAD_DIR . $id.".jpg", 160, 240);
		}

		// Création de l'accreditation pour le referent.
		$accred = null;
		$accred['idcategorie'] = $data['categorie'];
		$accred['idevenement'] = $this->input->post('evenement');
		$accred['idclient'] = $id;
		$accred['etataccreditation'] = ACCREDITATION_A_VALIDE;
		$accred['fonction'] = $data['fonction'];
		$accred['groupe'] = $data['groupe'];
		$accred['dateaccreditation'] = time();
		$idNewAccred = $this->modelaccreditation->ajouter($accred);

		// Création des zones accéssible pour cette accrédiation.
		if($data['categorie'] != -1)
			$this->AssociationZoneAccred($idNewAccred, $data['categorie'], $this->input->post('evenement') );

		$evenement = $this->modelevenement->getEvenementParId($accred['idevenement']);

		$contenuMail = 	'<html>' .
					'<head></head>' .
					'<body>' .
						'<p>' . $this->modelutilisateur->getConfig('cher') . $ref['prenom'] . ' ' . $ref['nom'] . ' / ' . $this->modelutilisateur->getConfig('dear') . $ref['prenom'] . ' ' . $ref['nom'] . ', </p>' .
						$this->modelutilisateur->getConfig('intro_mail') .
						'<p>Membres du groupe "' . $accred['groupe'] . '" : <br />' .
						'"' . $accred['groupe'] . '" group members : </p>' .
						'<ul title="Referent" >' .
						'<li>' . $ref['prenom'] . ' ' . $ref['nom'];

		$cat = $this->modelcategorie->getCategorieMereid($accred['idcategorie']);

		if(isset($cat) && !empty($cat) && $cat[0]->libellecategorie != '') {
			$contenuMail .= ' - ' . $cat[0]->libellecategorie;
		}
		else {
			$contenuMail .= ' - Pas de catégorie définie / No category defined';
		}

		if(isset($accred['fonction']) && !empty($accred['fonction']) && $accred['fonction'] != '') {
			$contenuMail .= ' (' . $accred['fonction'] . ') - <strong>Référent(e) du groupe / Group referent</strong></li>';
		}
		else {
			$contenuMail .= ' (Pas de fonction définie / No function defined) - <strong>Référent(e) du groupe / Group referent</strong></li>';
		}


		// Ajout des membres

		$gr = $this->input->post('groupe');
		$this->load->library('upload');

		//*
		if($gr) {
			foreach($gr as $k => $ligne) {
				// création du client
				$membre = null;
				$membre['nom'] = strtoupper($ligne['nom']);
				$membre['prenom'] = $ligne['prenom'];
				$membre['pays'] = $data['pays'];
				$membre['tel'] = $data['tel'];
				$membre['mail'] = $data['mail'];
				$idNewClient = $this->modelclient->ajouter($membre);

				// ajout photo webcam
				if($ligne['webcam'] != null)
					rename('./assets/images/' . $ligne['webcam'], UPLOAD_DIR . $idNewClient . '.jpg');

				$index = $ligne['index'];

				// création de l'accreditation
				$accred = null;
				$accred['groupe'] = $data['groupe'];
				$accred['idevenement'] = $this->input->post('evenement');
				$accred['idclient'] = $idNewClient;
				$accred['fonction'] = $ligne['fonction'];
				$accred['referent'] = $id;
				$accred['etataccreditation'] = ACCREDITATION_A_VALIDE;
				$accred['dateaccreditation'] = time();


				$tab = $ligne['categorie'];

				$accred['idcategorie'] = $tab;

				$idNewAccred = $this->modelaccreditation->ajouter($accred);

				$cat = null;

				//$cat = $this->modelcategorie->getCategorieMereid($accred['idcategorie']);

				// Création des zones accéssible pour cette accrédiation.
				if($accred['idcategorie'] != -1)
					$this->AssociationZoneAccred($idNewAccred, $accred['idcategorie'],$this->input->post('evenement') );

				// Gestion du mail.
				$contenuMail .= '<li>' . $membre['prenom'] . ' ' . $membre['nom'];


				if(isset($cat) && !empty($cat) && $cat[0]->libellecategorie != '') {
					$contenuMail .= ' - ' . $cat[0]->libellecategorie;
				}
				else {
					$contenuMail .= ' - Pas de catégorie définie / No category defined';
				}

				if(isset($accred['fonction']) && !empty($accred['fonction']) && $accred['fonction'] != '') {
					$contenuMail .= ' (' . $accred['fonction'] . ')</li>';
				}
				else {
					$contenuMail .= ' (Pas de fonction définie / No function defined)</li>';
				}

			}

		}
		/* */

		// Préparation et envoi du mail de confirmation
		$this->email->from($this->modelutilisateur->getConfig('mail_expediteur'), $this->modelutilisateur->getConfig('nom_expediteur')); // L'adresse qui enverra le mail
		$this->email->to($ref['mail']); // Le destinataire du mail
		$this->email->bcc($this->modelutilisateur->getConfig('mail_copie')); // L'adresse de Courchevel qui recevra une copie du mail

		// Le sujet du mail
		$this->email->subject($this->modelutilisateur->getConfig('objet_mail'));

		// Le contenu du mail
		$contenuMail .= 	'</ul>' .
							$this->modelutilisateur->getConfig('traitement_mail');

		if($evenement[0]->textmail != '')
			$contenuMail .=		'<p>' . nl2br($evenement[0]->textmail) . '</p>';

		$contenuMail .= 		$this->modelutilisateur->getConfig('signature_mail') .
							'</body>' .
						'</html>';

		// Inclusion du contenu dans le mail
		$this->email->message($contenuMail);

		// Envoi du mail
		$this->email->send();

		$msg['titre']	= $this->lang->line('titreConfirmeDemandeGroupe');
		$msg['message']	= $this->lang->line('confirmeDemandeGroupe');
		$this->layout->view('lambda/LMessage', $msg);
	}

	public function upload($id)
	{
		$client = $this->modelclient->getClientParId($id);

		$config['upload_path'] = UPLOAD_DIR;
		$config['allowed_types'] = 'jpg';
		$config['max_size']	= '4000';
		//$config['max_width']  = '1024';
		//$config['max_height']  = '768';
		//$config['file_name'] = urlencode($client->nom . '_' . $client->prenom . '_' . $id.".jpg");
		$config['file_name'] = $id.".jpg";
		$config['overwrite'] = true;

		$this->load->library('upload', $config);
		$this->upload->do_upload('photo_file');
		echo $this->upload->display_errors();
		$data = $this->upload->data();

		$data['lang'] = $this->session->userdata('lang');

		$img = imagecreatefrom(UPLOAD_DIR . $data['file_name']);

		$this->load->helper('image');

		if($data['image_width'] == IMG_WIDTH && $data['image_height'] == IMG_HEIGHT) {
			$data['titre']		= $this->lang->line('titreConfirmeDemande');
			$data['message']	= $this->lang->line('confirmeDemande');
			$this->layout->view('lambda/LMessage', $data);
		} elseif($data['image_width'] > IMG_WIDTH && $data['image_height'] > IMG_HEIGHT) {
			if($data['image_width'] > 940)
				resizeWidthRatio($data['full_path'], 940);
			redirect('inscription/crop/' . $id);
		} else
			die('Image trop petite.');
	}

	/*
	 * Crop : coupe une image trop grande
	 */
	public function crop($id) {

		$data['client'] = $this->modelclient->getClientParId($id);

		$data['lang'] = $this->session->userdata('lang');

		$this->layout->ajouter_css('jquery.Jcrop');
		$this->layout->ajouter_js('jquery.Jcrop.min');

		$this->layout->view('lambda/LCrop', $data);

	}


	/*
	 * ExeCrop : redimensionne l'image avec les paramères passés
	 */
	public function exeCrop() {

		$id = $this->input->post('id');
		$x = $this->input->post('x');
		$y = $this->input->post('y');
		$w = $this->input->post('w');
		$h = $this->input->post('h');

		$client = $this->modelclient->getClientParId($id);

		$this->load->helper('image');
		crop(UPLOAD_DIR . $client->idclient . '.jpg', $x, $y, $w, $h);

		$data['titre']		= $this->lang->line('titreConfirmeDemande');
		$data['message']	= $this->lang->line('confirmeDemande');
		$this->layout->view('lambda/LMessage', $data);
	}

}

/* End of file inscription.php */
/* Location: ./application/controllers/inscription.php */