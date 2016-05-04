<?php
/**
 * @author     mediahof, Kiel-Germany
 * @link       http://www.mediahof.de
 * @copyright  Copyright (C) 2011 - 2013 mediahof. All rights reserved.
 * @license    GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die;

class plgSystemAutoArchive extends JPlugin {
	
	private $frontpageremove = array();

    public function OnAfterInitialise() {
        $kat2kat = $this->createKat2Kat($this->params->get('kat2kat', '0'));
        $kat2katextdate = $this->validParam($this->params->get('kat2katextenddate', '0'));
        $kat2archiv = $this->validParam($this->params->get('kat2archiv', '0'));
        $k2arcextdate = $this->validParam($this->params->get('kat2archivextenddate', '0'));
        $kat2trash = $this->validParam($this->params->get('kat2trash', '0'));
        $kat2nirvana = $this->validParam($this->params->get('kat2nirvana', '0'));
        $this->kat2kat($kat2kat, $kat2katextdate);
        $this->kat2other($kat2archiv, '2', $k2arcextdate);
        $this->kat2other($kat2trash, '-2');
        $this->kat2other($kat2nirvana, '-3');
        $this->unsetFrontpage();
    }

    private function kat2kat($arr, $ext) {
        if(is_array($arr) && is_array($ext)) {
        	$newdata = $this->dataupdate($ext[0]);
            foreach($arr as $kat => $toKat) {
                $artAll = $this->artRead($kat);
                foreach($artAll as $art) {
                    $this->execQuery(
                       'UPDATE #__content SET
                        catid = ' . $toKat . ',
                        state = 1,
                        publish_down = "' . $newdata . '"
                        WHERE id = '.$art->id);
                    
                    $this->frontpageremove[] = $art->id;
                }
            }
        }
    }

    private function unsetFrontpage() {
    	if($this->params->get('frontpageremove', '0') == 0 || empty($this->frontpageremove)) {
    		return;
    	}
    	
    	$this->frontpageremove = implode(',', $this->frontpageremove);
    	
    	$db = JFactory::getDbo();
    	 
    	$query = $db->getQuery(true)->delete('#__content_frontpage')->where('content_id IN(' . $this->frontpageremove . ')');
    	$db->setQuery($query);
    	$db->execute();
    	
    	$query = $db->getQuery(true)->update('#__content AS c')->set('c.featured = ' . $db->q(0))->where('c.id IN(' . $this->frontpageremove . ')');
    	$db->setQuery($query);
    	$db->execute();
    }
    
    private function dataupdate($addMonth){
        switch(intval($addMonth)) {
            case 0:
                $return = JFactory::getDbo()->getNullDate();
            break;
            default:
                $unix = mktime(date('H'), date('i'), date('s'), date('m') + intval($addMonth), date('d'), date('Y'));
                $return = JFactory::getDate($unix, 'UTC')->toSql();
            break;
        }
        return $return;
    }

    private function kat2other($arr, $method, $ext = array(0)) {
        $newdata = $this->dataupdate($ext[0]);
        foreach($arr as $kat) {
            $artAll = $this->artRead($kat);
            if(is_array($artAll) && count($artAll)) {
            	switch($method) {
                    case '-3':
                        $query = 'DELETE FROM #__content';
                    break;
                    default:
                        $query = 'UPDATE #__content SET state = "' . $method . '", publish_down = "' . $newdata . '"';
                    break;
                }
                
                foreach($artAll as $art) {
                    $this->execQuery($query . ' WHERE id = ' . $art->id);
                    $this->frontpageremove[] = $art->id;
                }
            }
        }
    }

    private function artRead($kat) {
    	$db = JFactory::getDbo();
    	
    	$query = $db->getQuery(true)
    			->select('c.id')
    			->from('#__content AS c')
    			->where('c.catid = ' . $db->q($kat))
    			->where('c.publish_down != ' . $db->q(JFactory::getDbo()->getNullDate()))
    			->where('c.publish_down <= ' . $db->q(JFactory::getDate('now', 'UTC')->toSql()));
    	
    	$db->setQuery($query);
    	
    	return $db->loadObjectList();
    }

    private function validParam($param) {
        switch($param) {
            default:
                $return = array();
                $param = str_replace(array(' ', "\t"), '', trim($param));
                if(strpos($param, ',') !== false) {
                    $params = explode(',', $param);
                } else {
                    $params = array(
                        strval($param)
                    );
                }
                foreach($params as $zahl) {
                    switch(ctype_digit($zahl)) {
                        case true:
                            $return[] = intval($zahl);
                            break;
                        default:
                            break;
                    }
                }
                return $return;
            break;
            
            case '0':
            case '':
                return array(0);
            break;
        }
    }

    private function createKat2Kat($kat2kat) {
        switch($kat2kat) {
            default:
                $return = array();
                $kat2kat = str_replace(array(' ', "\t"), '', trim($kat2kat));
                preg_match_all('/\((.*?)\)/', $kat2kat, $matches);
                foreach($matches[1] as $reg) {
                    if(strpos($reg, '=') !== false) {
                        list($regs, $toKat) = explode('=', $reg);
                        if(strpos($regs, ',') !== false) {
                            $kats = explode(',', $regs);
                        } else {
                            $kats = array(strval($regs));
                        }
                        foreach($kats as $kat) {
                            switch(ctype_digit($kat)) {
                                case true:
                                    $return[$kat] = $toKat;
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
                return $return;
            case '0':
            case '':
                return array(0);
                break;
        }
    }
    
    private function execQuery($query, $read=false) {
    	$db = JFactory::getDbo();
    	$db->setQuery($query);
    	return $read ? $db->loadObjectList() : $db->execute();
    }
}