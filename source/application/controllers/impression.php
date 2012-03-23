<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Impression extends The {
	
	
	public function __construct() {
		
		parent::__construct();
		
		// Chargement des modeles.
		$this->load->model('modelzone');
		$this->load->model('modelclient');
		$this->load->model('modelcategorie');
		$this->load->model('modelaccreditation');
		$this->load->library('form_validation');
		$this->layout->ajouter_css('utilisateur/impressionaccred');
		//$this->layout->ajouter_js('utilisateur/CRUDAccred');
		
		// Mise en place de la sécurisation.
		$this->securiseAll();
		
	}


	public function index($idclient, $idaccred, $idevenement, $facult='') {
		
		//$this->layout->view('utilisateur/accreditation/UAImprimer');
		
		include("phpToPDF.php");
		
		
		$client = $this->modelclient->getClientParId($idclient);
		$accred = $this->modelaccreditation->getAccreditationParId($idaccred);
		$zones = $this->modelzone->getZoneParAccredParEvenement ($idaccred, $idevenement);
		$facult = str_replace('%20', ' ', $facult);
		$indice = 0;
		
		$pdf = new phpToPDF();
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 12);
		if(img_url('photos/'.$client->nom.'.jpg') != NULL){
			$pdf->Image(img_url('photos/'.$client->nom.'.jpg'), 25, 22, 30, 38);
			
		}
		else{
			$pdf->Image(img_url('photos/ombre.jpg'), 25, 22, 30, 38);
		}
			
		$pdf->Text(25, 74, utf8_decode($accred->nom));
		$pdf->Text(55, 74, utf8_decode($accred->prenom));
		$pdf->Image(img_url('drapeaux/'.utf8_decode($client->pays).'.gif'), 25, 75);
		$pdf->Text(35, 78, utf8_decode($client->pays));
		if ($accred->libellecategorie != null){
			$couleur = hexaToRGB($accred->couleur);
			$pdf->SetFillColor($couleur->red,$couleur->green,$couleur->blue);
			$pdf->Rect(25, 79, 50, 5, 'DF');
			$pdf->Text(27, 83, utf8_decode($accred->libellecategorie));
			$indice = $indice + 5;
		}
		if($facult != ''){
			$pdf->Text(25, 83 + $indice, utf8_decode($facult));
			$indice = $indice + 4;
		}
		$pdf->Text(25, 83 + $indice, utf8_decode($client->organisme));
		
		
		$pdf->SetFont('helvetica', '', 15);
		$nb = count($zones);
		$nbligne = ($nb % 5 == 0)?$nb / 5 : round(($nb / 5), 0, PHP_ROUND_HALF_DOWN)+1;
		$zonetxt = "- ";
		$px = 5;
		for($i=0;$i<$nbligne;$i++){
			for($j=0;$j<5 && ($j+$i*5)<$nb;$j++){
				$zonetxt = $zonetxt.$zones[$j+$i*5]->codezone." - ";
			}
			$pdf->Text(25, 105 + $px*$i, $zonetxt);
			$zonetxt = '- ';
		}
		$pdf->Output();
		
	}
	
	public function impcarte($idclient, $idaccred, $idevenement, $facult=''){
		//$this->layout->view('utilisateur/accreditation/UAImprimer');
		
		include("phpToPDF.php");
		
		
		$client = $this->modelclient->getClientParId($idclient);
		$accred = $this->modelaccreditation->getAccreditationParId($idaccred);
		$zones = $this->modelzone->getZoneParAccredParEvenement ($idaccred, $idevenement);
		$facult = str_replace('%20', ' ', $facult);
		$indice = 0;
		
		
		$pdf = new phpToPDF();
		$pdf->AddPage();
		$pdf->SetFont('helvetica', '', 12);
		if(img_url('photos/'.$client->nom.'_bis.jpg') != NULL){
			$pdf->Image(img_url('photos/'.$client->nom.'.jpg'), 30, 22, 25, 32);
			
		}
		else{
			$pdf->Image(img_url('photos/ombre.jpg'), 30, 22, 25, 32);
		}
		$nomprenom = $accred->nom.' '.$accred->prenom;	
		$pdf->Text(58, 28, utf8_decode($nomprenom));
		$pdf->Image(img_url('drapeaux/'.utf8_decode($client->pays).'.gif'), 58, 30);
		$pdf->Text(65, 33, utf8_decode($client->pays));
		if ($accred->libellecategorie != null){
			$couleur = hexaToRGB($accred->couleur);
			$pdf->SetFillColor($couleur->red,$couleur->green,$couleur->blue);
			$pdf->Rect(58, 35, 44, 6, 'DF');
			$pdf->Text(60, 40, utf8_decode($accred->libellecategorie));
			$indice = $indice + 6;
		}
		if($facult != ''){
			$pdf->Text(58, 40 + $indice, $facult);
			$indice = $indice + 6;
		}
		$pdf->Text(58, 40 + $indice, utf8_decode($client->organisme));
		$zonetxt = '- ';
		foreach($zones as $z){
			$zonetxt = $zonetxt.$z->codezone.' - ';
		}
		$pdf->SetFont('helvetica', 'B', 12);
		$pdf->Text(30, 62, $zonetxt);
		$pdf->Output();
	}
	
}