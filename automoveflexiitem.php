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
        
    private function _getDateAction () {
        $delay = $this->params->get('actiondelay', '');//add delay to sql for get item
        $serveurdateinit = date('Y-m-d H:i:s');
        if ($delay !=0){
        $serveurdate = 'ADDDATE("'.$serveurdateinit.'", INTERVAL '.$delay.')';
        }else{
        $serveurdate = $serveurdateinit;
            }
        if (function_exists('dump')) dump($serveurdateinit, 'date serveur');
       if (function_exists('dump')) dump($serveurdate, 'date serveur + delay');
        return $serveurdate;
    }
   
   private function _getItemsToMove ($serveurdate, $datemode, $fielddateid) {
       $methode = $this->params->get('catmethode', '1');// 1 include or 0 exclude categories
       $moved_cat = $this->params->get('moved_category', '');//categories to get item
       $limit = 'LIMIT '.$this->params->get('limit', '20').'';//number of item to get

        $categoriesID = implode(',', $moved_cat);
        if (function_exists('dump')) dump($categoriesID, 'catid');
        if ($methode == 0){
                $whereCateg = 'catid IN ('.$categoriesID.')';
            }else{
                $whereCateg = 'catid NOT IN ('.$categoriesID.')';
            }
        if (function_exists('dump')) dump($whereCateg, 'catid');
        if ($datemode ==0){
                $datsource = 'a.id, a.title, a.publish_down, a.catid FROM #__content AS a WHERE a.publish_down';
            }else{
                $datsource = 'a.id, a.title, a.publish_down, b.field_id, b.value , a.catid FROM #__content AS a ' .
                            'LEFT JOIN #__flexicontent_fields_item_relations AS b ON a.id = b.item_id ' .
                            'WHERE b.field_id = '.$fielddateid.''; //TODO => que faire quand il n'y a pas de champ date associé ??
            }
            $db = JFactory::getDBO();
            $query = "SELECT  $datsource > $serveurdate AND $whereCateg $limit";
            $db->setQuery($query);
            if (function_exists('dump')) dump($query, 'requete');
            //if (function_exists('dump')) dump($query->__toString(), 'requete toString');
            $selectarticle = $db->loadObjectList();
            if (function_exists('dump')) dump($selectarticle, 'export de données');
            return $selectarticle;
    }
    
    private function _moveItems ($listContents, $datemode) {
        $movecat = $this->params->get('movecat','');//0 not move article or 1 for move
        $target_cat = $this->params->get('target_category', '');//id of target move categorie
        $movesubcat = $this->params->get('movesubcat','');//0 not move article in subcator 1 for move
        $target_subcat = $this->params->get('target_subcategory', '');//id of target move subcategorie
        $state = $this->params->get('changestate', '');//changing state of article
        $cleardate = $this->params->get('cleardate', '');//clear date nothing, 0 unpblished, 1 published, -1 archived, -2 trashed
        
        if ($cleardate == 1 && $datemode == 0){ //clear joomla unpublished date
                $changeDate="publish_down = 0000-00-00 00:00:00";
            }elseif ($cleardate == 1 && $datemode == 1){ //clear flexicontent dat field
                $changeDate="";//TODO 
            }else{
                $changeDate="";
         }
        
        switch ($state){//changing state
            case '0': 
                $changeState="state = 0";
            break;
                case '1': 
                $changeState="state = 1";
            break;
            case '-1': 
                $changeState="state = -1";
            break;
            case '-2': 
                $changeState="state = -2";
            break;
            case 'nothing':
                $changeState="";
            break;
        }
        if ($movecat == 1 && $movesubcat == 0){//move article
            $changeCat="catid = $target_cat";
        }elseif ($movecat == 1 && $movesubcat == 1){
            $changeCat="catid = $target_cat".
                        "LEFT JOIN ";//FLEXIContent subcat
        }else {
            $changeCat="";
        }
        
      foreach ($listContents as $article){
          $db = JFactory::getDBO();
          $query = "UPDATE #__content SET $changeDate $changeState $changeCat WHERE id ='$article->id'";
         // $db->setQuery($query);
          if (function_exists('dump')) dump($query, 'requette update');
          //$result = $db->execute();  
          //if (function_exists('dump')) dump($result, 'resultat requette update');
        }
    }
}
