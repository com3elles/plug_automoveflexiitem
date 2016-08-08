<?php
defined('_JEXEC') or die;

class PlgSystemAutomoveflexiitem extends JPlugin
{
    protected $autoloadLanguage = true;
    public function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
 
    }
    public function onAfterInitialise ()
    {
        $datemode = $this->params->get('datemode','');//0 joomla unplishing date or 1 for flexicontent date
        $fielddateid = $this->params->get('fielddateid','');//id of flexicontent date field
        
        $srvdate = $this->_getDateAction();

        $listContents = $this->_getItemsToMove ($srvdate, $datemode, $fielddateid);
        if (function_exists('dump')) dump($listContents, 'Des données sont à archivées');
        if($listContents)
            $this->_moveItems($listContents, $datemode, $fielddateid);
        
   }
    /**
	* Get date and delay
	*/
    private function _getDateAction () {
        $delay = $this->params->get('actiondelay', ''); // add delay to sql for get item
        $serveurdateinit = date('Y-m-d H:i:s');
        if ($delay !=0){
        $serveurdate = 'ADDDATE("'.$serveurdateinit.'", INTERVAL '.$delay.')';
        }else{
        $serveurdate = $serveurdateinit;
            }
       // if (function_exists('dump')) dump($serveurdateinit, 'date serveur');
       if (function_exists('dump')) dump($serveurdate, 'date serveur + delay');
        return $serveurdate;
    }
   
	/**
	* Get item to move
	*/
	private function _getItemsToMove ($serveurdate, $datemode, $fielddateid) {
		$methode = $this->params->get('catmethode', '1');       // 1 include or 0 exclude categories
		$moved_cat = $this->params->get('moved_category', '');  // categories to get item
		$limit = 'LIMIT '.$this->params->get('limit', '20').''; // number of item to get

//TODO il faudra refaire l'objet query en utilisant les methodes d'abstraction SQL ... on en parle dans la semaine
		// selection des champs
		$datsource = 'a.id, a.title, a.publish_down, a.catid FROM #__content AS a';
//TODO tu es sur que la liste des champs du SELECT change selon le datmode ??? pas facile a maintenir ca !
		if ($datemode != 0) {
			$datsource = 'a.id, a.title, a.publish_down, b.field_id, b.value, a.catid FROM #__content AS a ' .
						'LEFT JOIN #__flexicontent_fields_item_relations AS b ON b.item_id = a.id';
			}

		// construction des clauses WHERE
		$tWheres = array();

		// clause sur la date de publication
		if ($datemode == 0){
            $tWheres[] = "a.publish_down < '$serveurdate'";
        } else {
            $tWheres[] = "b.value < '$serveurdate'";
        }
		 
		// clause sur les categories
		$categoriesID = implode(',', $moved_cat);
		if (function_exists("dump")) dump($categoriesID, 'catid');
		if ($methode == 0) {
			$tWheres[] = "a.catid IN (".$categoriesID.")";
		} else {
			$tWheres[] = "a.catid NOT IN (".$categoriesID.")";
		}
		if (function_exists("dump")) dump($datemode, "datemode");

		// clause sur l'utilisation d'un champ date flexi
		if ($datemode == 1)
			$tWheres[] = "b.field_id = ".$fielddateid;

		$db = JFactory::getDBO();
		// construction de la requete SQL
		$sWheres = implode(" AND ", $tWheres);
		$query = "SELECT $datsource WHERE $sWheres $limit";
		$db->setQuery($query);
		if (function_exists("dump")) dump($query, 'requete');
		//if (function_exists("dump")) dump($query->__toString(), 'requete toString');
		$selectarticle = $db->loadObjectList();
		//if (function_exists("dump")) dump($selectarticle, 'export de données');
		return $selectarticle;
	}

    /**
	* Move item
	*/
    private function _moveItems ($listContents, $datemode, $fielddateid) {
        $movecat = $this->params->get('movecat','');//0 not move article or 1 for move
        $target_cat = $this->params->get('target_category', '');//id of target move categorie
        $movesubcat = $this->params->get('movesubcat','');//0 not move article in subcator 1 for move
        $target_subcat = $this->params->get('target_subcategory', '');//id of target move subcategorie
        $state = $this->params->get('changestate', '');//changing state of article
        $cleardate = $this->params->get('cleardate', '');//clear date nothing, 0 unpblished, 1 published, -1 archived, -2 trashed
        
        if ($cleardate == 1 && $datemode == 0){ //clear joomla unpublished date
                $changeDate="a.publish_down = '0000-00-00 00:00:00'";
            }elseif ($cleardate == 1 && $datemode == 1){ //clear flexicontent date field
                $changeDate="value ='0000-00-00 00:00:00'"; 
            }else{
                $changeDate="";
         }
        
        switch ($state){//changing state
            case '0': 
                $changeState="a.state =0";
            break;
                case '1': 
                $changeState="a.state =1";
            break;
            case '-1': 
                $changeState="a.state =-1";
            break;
            case '-2': 
                $changeState="a.state =-2";
            break;
            case 'nothing':
                $changeState=" ";
            break;
        }
        if ($movecat == 1 && $movesubcat == 0){//move article
            $changeCat="a.catid =$target_cat";
        }elseif ($movecat == 1 && $movesubcat == 1){
            $changeCat="a.catid =$target_cat ".
                        "LEFT JOIN ";//TODO adding FLEXIContent subcat
        }else {
            $changeCat="";
        }
        
      foreach ($listContents as $article){
          $db = JFactory::getDBO();
          $query = "UPDATE #__content SET $changeDate $changeState $changeCat WHERE id =$article->id";
          // $db->setQuery($query);
          if ($cleardate == 1 && $datemode == 1){//clear flexicontent date field
                $querydateflexi = "UPDATE #__flexicontent_fields_item_relations SET $changeDate  WHERE field_id= $fielddateid AND id =$article->id";
              if (function_exists('dump')) dump($querydateflexi, 'requette update date flexi');
              // $db->setQuery($querydateflexi);
          }
          if (function_exists('dump')) dump($query, 'requette update');
          //$result = $db->execute();  
        }
    }
}
