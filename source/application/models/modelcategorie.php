<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class modelCategorie extends MY_Model {
	
	public function getCategorieMere() {
		return $this->db->select('*')
						->from(DB_CATEGORIE )
						->where('surcategorie',null)
						->get()
						->result();
	}
	
	public function getCategories() {
		return $this->db->select('*')
						->from(DB_CATEGORIE )
						->get()
						->result();
	}
	
	public function getCategoriesSaufPresse( $event ) {
		return $this->db->select('*')
						->from(DB_CATEGORIE.' c')
						->join (DB_PARAMETRES_EVENEMENTS.' p', 'p.idcategorie = c.idcategorie')
						->where('idevenement', $event)
						->not_like('libellecategorie','Presse')
						->get()
						->result();
	}
	
	

    public function getCategoriesMeresSaufpresse() {
		return $this->db->select('*')
						->from(DB_CATEGORIE )
				        ->not_like('libellecategorie','Presse')
			        	->where('surcategorie',null)
						->get()
						->result();
	}

	/**
	 * Méthode pour récuperer l'id de la catégorie Presse.
	 * @return mixed
	 */
	public function getIdPresse() {
		$result = $this->db->select('*')
							->from(DB_CATEGORIE )
							->where('libellecategorie', 'Presse')
							->or_where('libellecategorie', 'presse')
							->get()
							->result();

		return $result[0]->idcategorie;
	}

	
	public function getCategoriesParIdaccred($id){
		return $this->db->select('*')
						->from(DB_CATEGORIE .' c')
						->join(DB_ACCREDITATION .' a', 'c.idcategorie = a.idcategorie', 'left')
						->where('a.idaccreditation', $id)
						->get()
						->result();
	}
	
	public function getCategorieMereid($id) {
		return $this->db->select('*')
						->from(DB_CATEGORIE )
						->where('idcategorie',$id)
						->get()
						->result();
	}
	
	public function getSousCategorieid($id) {
		return $this->db->select('idcategorie')
						->from(DB_CATEGORIE )
						->where('surcategorie',$id)
						->get()
						->result();
	}
	
	public function getSousCategorie( $id ) {
		return $this->db->select('*')
						->from(DB_CATEGORIE)
						->where('surcategorie',$id)
						->get()
						->result();
	}
	
	public function getCategorieDansEvenement( $idEvenement ) {
		return $this->db->select('c.idcategorie, c.libellecategorie')
						->distinct()
						->from(DB_CATEGORIE . ' c')
						->join(DB_PARAMETRES_EVENEMENTS . ' pe', 'pe.idcategorie = c.idcategorie', 'left')
						->where('idevenement',$idEvenement)
						->get()
						->result();
	}
	
	public function getCategorieDansEvenementToutBien() {
		$result =  $this->db->select('*')
						->from(DB_CATEGORIE . ' c')
						->order_by('c.surcategorie')
						->get()
						->result();
		
		// tableau de sortie
		$sorted = array();
		
		// on boucle sur tout les elements de result (tableau trié par surcategorie)
		foreach($result as $cat){
			
			// variable qui check si une sous catégorie a été insérée
			$inserted = false;
			
			// on boucle sur le tabeau de sortie
			for($j=0; $j<=count($sorted); $j++) {
				
				// si la cat du tableau de sortie est mère de l'actuelle
				if(isset($sorted[$j]) && $sorted[$j]['db']->idcategorie == $cat->surcategorie) {
					
					// insertion de la cat après sa cat mère
					$before = array_slice($sorted, 0, $j+1);
					$before[] = array('db' => $cat, 'depth' => $depth = ++$sorted[$j]['depth']);
					$after = array_slice($sorted, $j+1);
					$sorted = array_merge($before, $after);
					
					// une cat a été insérer après sa mère
					$inserted = true;
					break;
				}
			}
			
			// si la cat courante n'a pas trouver de cat mère, on l'insère à la fin
			if(!$inserted)
				$sorted[] = array('db' => $cat, 'depth' => $depth = 0);
		}
		
		return $sorted;
	}
	
	public function getCategorieDansEvenementToutBienAvecId($idEvenement) {
		$result =  $this->db->select('*')
						->from(DB_CATEGORIE . ' c')
						->join(DB_PARAMETRES_EVENEMENTS . ' pe', 'pe.idcategorie = c.idcategorie', 'left')
						->where('pe.idevenement',$idEvenement)
						->order_by('c.surcategorie')
						->get()
						->result();
		
		// tableau de sortie
		$sorted = array();
		
		// on boucle sur tout les elements de result (tableau trié par surcategorie)
		foreach($result as $cat){
			
			// variable qui check si une sous catégorie a été insérée
			$inserted = false;
			
			// on boucle sur le tabeau de sortie
			for($j=0; $j<=count($sorted); $j++) {
				
				// si la cat du tableau de sortie est mère de l'actuelle
				if(isset($sorted[$j]) && $sorted[$j]['db']->idcategorie == $cat->surcategorie) {
					
					// insertion de la cat après sa cat mère
					$before = array_slice($sorted, 0, $j+1);
					$before[] = array('db' => $cat, 'depth' => $depth = ++$sorted[$j]['depth']);
					$after = array_slice($sorted, $j+1);
					$sorted = array_merge($before, $after);
					
					// une cat a été insérer après sa mère
					$inserted = true;
					break;
				}
			}
			
			// si la cat courante n'a pas trouver de cat mère, on l'insère à la fin
			if(!$inserted)
				$sorted[] = array('db' => $cat, 'depth' => $depth = 0);
		}
		
		return $sorted;
	}
	
	public function ajouter($data) {
		$this->db->insert(DB_CATEGORIE, $data);
	}
	
	
	public function supprimerCategorie($id) {
		$this->db->where('idcategorie', $id);
        $this->db->delete(DB_CATEGORIE);
	}
	
	public function supprimersousCategorie($id) {
		$this->db->where('surcategorie', $id);
        $this->db->delete(DB_CATEGORIE);
	}
	
	public function supprimerSousCategories($id) {
		
		$this->db->where('idcategorie', $id);
        $this->db->delete(DB_CATEGORIE);
		
		$cats = $this->getSousCategorie($id);
		foreach($cats as $cat)
			$this->supprimerSousCategories($cat->idcategorie);
		
	}
	
	public function modifier($id, $data) {
		return $this->db->update(DB_CATEGORIE, $data, 'idcategorie = '.$id);
	}
	
	public function majCouleur() {
		
		foreach($this->getCategories() as $cat) {
			if($cat->surcategorie != null) {
				$surcat = $this->getCategorieMereid($cat->surcategorie);
				$data['couleur'] = $surcat[0]->couleur;
				$this->modifier($cat->idcategorie, $data);
			}
		}
		
	}
	
	
	
}