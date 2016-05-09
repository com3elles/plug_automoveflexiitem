<?php
defined('_JEXEC') or die;

class PlgSystemAutomoveflexiitem extends JPlugin

    protected $autoloadLanguage = true;
    public function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
 
    }
    public function onAfterInitialise ()
    {
        //if (function_exists('dump')) dump($this->_name, 'name');
        // recuperation des options
        $datemode = $this->params->get('datemode','0');
        $fielddateid = $this->params->get('fielddateid','');
        $methode = $this->params->get('catmethode', '1');
        $movecat = $this->params->get('movecat','0');//si on déplace ou non
        $moved_cat = $this->params->get('moved_category', '');//catégorie a traiter
        $target_cat = $this->params->get('target_category', '');
        $state = $this->params->get('changestate', 'nothing');
        $delay = $this->params->get('actiodelay', 'now');
        $cleardate = $this->params->get('cleardate', '1');
        $limit = 'LIMIT '.$this->params->get('limit', '20');
        
        if ($context != 'com_flexicontent.item'){//context
            return true;
        }
        $serveurdate = date('Y-m-d H:i:s');
       if (function_exists('dump')) dump($serveurdate, 'date serveur');
    }
    private function getItem () {

        $categoriesID = implode(',', $moved_cat);
        if (function_exists('dump')) dump($categoriesID, 'catid');
        if ($methode == 1){
                $whereCateg = 'catid IN ('.$categoriesID.')';
            }else{
                $whereCateg = 'catid NOT ('.$categoriesID.')';
            }
        if (function_exists('dump')) dump($whereCateg, 'catid');
        if ($datemode ==0){
                $datsource = 'publish_down';
            }else{
                $datsource = 'publish_down';//TODO requete pour le champ flexicontent
            }
            $db = JFactory::getDBO();
            $query = "SELECT * FROM #__content WHERE ' .$whereCateg.' AND '.$datsource.' < '.$serveurdate.' '.$limit.'";
            $db->setQuery($query);
            if (function_exists('dump')) dump($query, 'requette');
            $selectarticle = $db->loadObjectList();
            return $selectarticle;
            if (function_exists('dump')) dump($selectarticle, 'export de donnée');
           }
    private function moveItem () {
        // on deplace et on traite (déplacement catégorie, changement statu, reinitialisation date)
        //construction de la requette
        foreach ($selectarticle as $article){
            if ($cleardate == 1){
                //UPDATE
            }else{
                //UPDATE
            }
        }
    }
}
