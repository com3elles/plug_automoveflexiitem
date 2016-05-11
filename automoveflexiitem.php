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
        // recuperation des options
        $datemode = $this->params->get('datemode','0');
        $fielddateid = $this->params->get('fielddateid','');
        $methode = $this->params->get('catmethode', '1');
        $movecat = $this->params->get('movecat','0');//si on déplace ou non
        $moved_cat = $this->params->get('moved_category', '');//catégorie a traiter
        $target_cat = $this->params->get('target_category', '');
        $state = $this->params->get('changestate', 'nothing');
        $delay = $this->params->get('actiondelay', 'now');
        $cleardate = $this->params->get('cleardate', '1');
        $limit = 'LIMIT '.$this->params->get('limit', '20').'';
        $fielddateid= $this->params->get('fielddateid','');
        
       // if ($context != 'com_flexicontent'){//context indisponible avec onafterinitialise
         //   return true;
        //}
        
        // private function getDateaction () {
        $serveurdateinit = date('Y-m-d H:i:s');
        if ($delay !=0){
        $serveurdate = 'ADDDATE('.$serveurdateinit.', INTERVAL '.$delay.')';
        }else{
        $serveurdate = $serveurdateinit;
            }
        if (function_exists('dump')) dump($serveurdateinit, 'date serveur');
       if (function_exists('dump')) dump($serveurdate, 'date serveur + delay');
        return $serveurdate;
    }
   // }
   // private function getItem () {

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
                $datsource = 'a.id, a.title, a.publish_down, b.field_id, b.value , a.catid FROM #__content AS a LEFT JOIN #__flexicontent_fields_item_relations AS b ON a.id = b.item_id WHERE b.field_id = '.$fielddateid.''; //TODO => que faire quand il n'y a pas de champ date associé ??
            }
            $db = JFactory::getDBO();
            $query = "SELECT  $datsource > '$serveurdate' AND $whereCateg $limit";
            $db->setQuery($query);
            if (function_exists('dump')) dump($query, 'requette');
            $selectarticle = $db->loadObjectList();
            if (function_exists('dump')) dump($selectarticle, 'export de donnée');
            return $selectarticle;
            
           //}
    //private function moveItem () {
        // on deplace et on traite (déplacement catégorie, changement statu, reinitialisation date)
        //construction de la requette
      //  foreach ($selectarticle as $article){
        //    if ($cleardate == 1){
                //UPDATE
          //  }else{
                //UPDATE
            //}
        //}
    }
}
