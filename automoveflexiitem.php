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


        // récuperer les articles
        if 
        ($methode == 1 || $datemode == 0){// si on exlus des catégorie depuis l'admin et que la date utilisé est la dépublication joomla
            // Get a database object
            $dbinclude = JFactory::getDBO();
            $query = "SELECT * FROM #__content WHERE catid = ' .$moved_cat.' AND publish_down > '.$serveurdate.'";
            $dbinclude->setQuery($query);
            $selectarticle = $dbinclude->loadObjectList();
            if (function_exists('dump')) dump($dbinclude, 'donnée sql include + date joomla');
        }
        elseif ($methode == 0 || $datemode == 0){// si on inlus les catégories depuis l'admin et que la date utilisé est la dépublication joomla
            // Get a database object
            $dbexclude = JFactory::getDBO();
            $query = "SELECT * FROM #__content WHERE NOT catid = ' .$moved_cat.' AND publish_down > '.$serveurdate.'";
            $dbexclude->setQuery($query);
            $selectarticle = $dbexclude->loadObjectList();
             if (function_exists('dump')) dump($dbexclude, 'donnée sql exclude + date joomla');
        }
        elseif ($methode == 1 || $datemode == 1){// si on exlus des catégorie depuis l'admin et que la date utilisé est un champs flexicontent
            // Get a database object
            $dbexclude = JFactory::getDBO();
            $query = "SELECT * FROM #__content WHERE NOT catid = ' .$moved_cat.' AND publish_down > '.$serveurdate.'";
            $dbexclude->setQuery($query);
            $selectarticle = $dbexclude->loadObjectList();
             if (function_exists('dump')) dump($dbexclude, 'donnée sql exclude + date flexi');
        }
        else ($methode == 0 || $datemode == 1){// si on inclus des catégorie depuis l'admin et que la date utilisé est un champs flexicontent
            // Get a database object
            $dbexclude = JFactory::getDBO();
            $query = "SELECT * FROM #__content WHERE NOT catid = ' .$moved_cat.' AND publish_down > '.$serveurdate.'";
            $dbexclude->setQuery($query);
            $selectarticle = $dbexclude->loadObjectList();
             if (function_exists('dump')) dump($dbexclude, 'donnée sql exclude + date flexi');
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
