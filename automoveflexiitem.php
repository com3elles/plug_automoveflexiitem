<?php
defined('_JEXEC') or die;

class PlgSystemAutomoveflexiitem extends JPlugin
{
    public function __construct( &$subject, $config )
    {
        parent::__construct( $subject, $config );
 
    }
    
    public function onAfterInitialise ()
    {
        //TODO Ajout du context
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
        
        $serveurdate = date('Y-m-d H:i:s');
        if (function_exists('dump')) dump($serveurdate, 'date serveur');

            private function getItemtomove {
                $categoriesID = implode(',', $moved_cat);
                if ($methode == 1){
                $whereCateg = 'catid IN ('.$categoriesID.')';
                    }else{
                $whereCateg = 'catid NOT ('.$categoriesID.')';
                }
                if ($datemode ==0){
                   $datsource = 'publish_down';
                }else{
                    $datsource = 'publish_down';//TODO requete pour le champ flexicontent
                }
                
                $db = JFactory::getDBO();
                $query = "SELECT * FROM #__content WHERE ' .$whereCateg.' AND '.$datsource.' < '.$serveurdate.'"; //TODO ajout de la limite
                $db->setQuery($query);
                $selectarticle = $db->loadObjectList();
                return $selectarticle;
                if (function_exists('dump')) dump($query, 'requette');
                if (function_exists('dump')) dump($selectarticle, 'export de donnée');
            }
        
        // on deplace et on traite
        foreach ($selectarticle as $article){
            if ($cleardate == 1){
                //on change l'article de place + on clean ca date
            }else{
                //on change l'article de place sans changer la date
            }
            
        }

    }
}
