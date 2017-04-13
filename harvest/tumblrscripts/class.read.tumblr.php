<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of phpTumblr.
# Copyright (c) 2006 Simon Richard and contributors. All rights
# reserved.
#
# phpTumblr is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# phpTumblr is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with phpTumblr; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

class readTumblr {
	protected $sTumblrID = null;
	protected $oNetHttp = null;
	protected $aValidPostTypes = array('regular','quote','photo','link','conversation','video','audio');
	protected $aTumblelog = array();
	protected $aPosts = array();
	protected $aStats = array();
	protected $aTemp = array();
	protected $bFirstRequest = true;
	protected $bDebug = false;
	protected $aDebug = array();
	protected $bIgnorePosts = false;
	
	public function __construct($sTumblrID = null,$sHTTPUserAgent = 'phpTumblr') {
		if ($sTumblrID == null) { unset($this); return false; }

		$this->sTumblrID = $sTumblrID;
		$sTumblrURL = $sTumblrID.'.tumblr.com';
		
		$oNetHttp = &$this->oNetHttp;
		$oNetHttp = new netHttp($sTumblrURL);
		$oNetHttp->setUserAgent($sHTTPUserAgent);
		$oNetHttp->get('/');
		if ($oNetHttp->getStatus() != 200) { unset($this); return false; }
		
		$this->__getTumblelogInfos();
		
		return true;
	}
	
	public function getPosts($nStart = 0,$mNum = 20,$sType = null,$nID = null,$sTagged = null, $sSearch = null) {
		$aParams = array();
		
		if (is_numeric($nID)) {
			$aParams['id'] = $nID;
			$this->__apiRead($aParams);
			$this->__orderResults();
			return true;
		}
		
		if ($nStart != null && is_numeric($nStart)) { $aParams['start'] = $nStart; } else { $aParams['start'] = 0; }
		if (!is_numeric($mNum)) { $mNum = 'all'; }
		if ($sType != null && is_string($sType) && in_array($sType,$this->aValidPostTypes)) { $aParams['type'] = $sType; } else { $aParams['type'] = 'all'; }
		
		if (!is_null($sTagged)) {
			$aParams['tagged'] = $sTagged;
			if ($mNum == 'all' || (is_numeric($mNum) && $mNum > 50)) { $aParams['num'] = 50; } else { $aParams['num'] = $mNum; }
			
			$this->__apiRead($aParams);
			$this->__orderResults();
			return true;
		}
		
		if (!is_null($sSearch)) {
			$aParams['search'] = $sSearch;
			if ($mNum == 'all' || (is_numeric($mNum) && $mNum > 50)) { $aParams['num'] = 50; } else { $aParams['num'] = $mNum; }
			
			$this->__apiRead($aParams);
			$this->__orderResults();
			return true;
		}
		
		$nMax = $this->aStats['num-'.$aParams['type']] - $nStart;
		if ($mNum == 'all') { $mNum = $nMax; }
		if ($mNum >= $nMax) { $mNum = $nMax; }
		
		if ($mNum > 50) {
			$aParams['num'] = 50;
			while (true) {
				$this->__apiRead($aParams);
				$this->__orderResults();
				
				$mNum = $mNum - $aParams['num'];
				if ($mNum == 0) { break; }
				$aParams['start'] = $aParams['start'] + $aParams['num'];
				if ($mNum <= 50) { $aParams['num'] = $mNum; }
			}
			return true;
		}
		
		$aParams['num'] = $mNum;
		$this->__apiRead($aParams);
		$this->__orderResults();
		return true;
	}
	
	public function dumpArray($bChrono = false) {
		$this->__sortArray($bChrono);
		$this->aStats['num-inarray'] = count($this->aPosts);
		
		$aDump = array();
		$aDump['tumblelog'] = $this->__cleanArr($this->aTumblelog);
		$aDump['stats'] = $this->aStats;
		$aDump['posts'] = $this->__cleanArr($this->aPosts);
		if ($this->bDebug) { $aDump['debug'] = $this->aDebug; }
		return $aDump;
	}
	
	public function __sortArray($bChrono = false) {
		if ($bChrono) {
			ksort($this->aPosts,SORT_NUMERIC);
		} else {
			krsort($this->aPosts,SORT_NUMERIC);
		}
		return true;
	}
	
	protected function __getTumblelogInfos() {
		$this->bIgnorePosts = true;
		$aParams['start'] = 0;
		$aParams['num'] = 1;
		$aParams['type'] = 'all';
		$this->__apiRead($aParams);
		$this->__orderResults();
		foreach ($this->aValidPostTypes as $v) {
			$aParams['type'] = $v;
			$this->__apiRead($aParams);
			$this->__orderResults();
		}
		//$this->__cleanArr($this->aTumblelog);
		$this->bIgnorePosts = false;
	}
	
	protected function __apiRead($aParams = array()) {
		if (isset($aParams['type']) && $aParams['type'] == 'all') { unset($aParams['type']); }
		$aParams['json'] = 1;

		$oNetHttp = &$this->oNetHttp;
		$oNetHttp->get('/api/read',$aParams);
		if ($oNetHttp->getStatus() != 200) { return false; }
		
		$sJson = preg_replace('#var tumblr_api_read = (.*);#','$1',$oNetHttp->getContent());
		$this->aTemp = json_decode($sJson,true);
		if ($this->bDebug) {
			$sKey = '';
			foreach ($aParams as $k => $v) {
				$sKey .= $k.$v;
			}
			$this->aDebug[$sKey] = $this->aTemp;
		}
		return true;
	}
	
	protected function __orderResults() {
		$aTemp = &$this->aTemp;
		$aPosts = &$this->aPosts;
		$aStats = &$this->aStats;
		
		foreach ($aTemp as $k => $v) {
			if ($k == 'tumblelog') {
				if ($this->bFirstRequest) {
					$aTumblelog = &$this->aTumblelog;
					$aTumblelog['title'] = (string) $aTemp['tumblelog']['title'];
					$aTumblelog['description'] = (string) $aTemp['tumblelog']['description'];
					$aTumblelog['id'] = (string) $aTemp['tumblelog']['name'];
					$aTumblelog['timezone'] = (string) $aTemp['tumblelog']['timezone'];
					$aTumblelog['cname'] = (string) $aTemp['tumblelog']['cname'];
					
					if ($aTumblelog['cname'] == null) {
						$aTumblelog['url'] = (string) 'http://'.$aTumblelog['id'].'.tumblr.com/';
					} else {
						$aTumblelog['url'] = (string) 'http://'.$aTumblelog['cname'].'/';
					}
					
					$this->bFirstRequest = false;
				}
			}
			
			if ($k == 'posts-start') { }
			if ($k == 'posts-total') { if ($aTemp['posts-type'] != '') { $aStats['num-'.$aTemp['posts-type']] = (int) $v; } else { $aStats['num-all'] = (int) $v; }}
			if ($k == 'posts-type') { }
			
			if ($k == 'posts') {
				foreach ($v as $post) {
					if ($this->bIgnorePosts) { break; }
					$pid = $post['unix-timestamp'].'|'.$post['id'];
					$aPosts[$pid]['id'] = (int) $post['id'];
					$aPosts[$pid]['url'] = (string) $post['url'];
					$aPosts[$pid]['type'] = (string) $post['type'];
					$aPosts[$pid]['time'] = (int) $post['unix-timestamp'];
					$aPosts[$pid]['mobile'] = (bool) $post['mobile'];
					$aPosts[$pid]['bookmarklet'] = (bool) $post['bookmarklet'];
					$aPosts[$pid]['format'] = (string) $post['format'];
					
					$aPosts[$pid]['tags'] = array();
					if (isset($post['tags'])) {
						foreach ($post['tags'] as $tag) { $aPosts[$pid]['tags'][] = (string) $tag; }
					}
					
					switch ($post['type']) {
						case 'regular' :
							$aPosts[$pid]['content']['title'] = (string) $post['regular-title'];
							$aPosts[$pid]['content']['body'] = (string) $post['regular-body'];
							break;
						case 'quote' :
							$aPosts[$pid]['content']['quote'] = (string) $post['quote-text'];
							$aPosts[$pid]['content']['source'] = (string) $post['quote-source'];
							break;
						case 'photo' :
							$aPosts[$pid]['content']['caption'] = (string) $post['photo-caption'];
							if ($post['photos'] != array()) {
								$aPosts[$pid]['content']['photos'] = array();
								foreach ($post['photos'] as $picid => $photo) {
									$aPosts[$pid]['content']['photos'][$picid]['photo-caption'] = (string) $photo['caption'];
									$aPosts[$pid]['content']['photos'][$picid]['photo-width'] = (string) $photo['width'];
									$aPosts[$pid]['content']['photos'][$picid]['photo-height'] = (string) $photo['height'];
									$aPosts[$pid]['content']['photos'][$picid]['url-1280'] = (string) $photo['photo-url-1280'];
									$aPosts[$pid]['content']['photos'][$picid]['url-500'] = (string) $photo['photo-url-500'];
									$aPosts[$pid]['content']['photos'][$picid]['url-400'] = (string) $photo['photo-url-400'];
									$aPosts[$pid]['content']['photos'][$picid]['url-250'] = (string) $photo['photo-url-250'];
									$aPosts[$pid]['content']['photos'][$picid]['url-100'] = (string) $photo['photo-url-100'];
									$aPosts[$pid]['content']['photos'][$picid]['url-75sq'] = (string) $photo['photo-url-75'];
								}
							} else {
								$aPosts[$pid]['content']['url-1280'] = (string) $post['photo-url-1280'];
								$aPosts[$pid]['content']['url-500'] = (string) $post['photo-url-500'];
								$aPosts[$pid]['content']['url-400'] = (string) $post['photo-url-400'];
								$aPosts[$pid]['content']['url-250'] = (string) $post['photo-url-250'];
								$aPosts[$pid]['content']['url-100'] = (string) $post['photo-url-100'];
								$aPosts[$pid]['content']['url-75sq'] = (string) $post['photo-url-75'];
							}
							break;
						case 'link' :
							$aPosts[$pid]['content']['text'] = (string) $post['link-text'];
							$aPosts[$pid]['content']['url'] = (string) $post['link-url'];
							$aPosts[$pid]['content']['description'] = (string) $post['link-description'];
							break;
						case 'conversation' :
							$aPosts[$pid]['content']['title'] = (string) $post['conversation-title'];
							$aPosts[$pid]['content']['text'] = (string) $post['conversation-text'];
							foreach ($post['conversation'] as $x => $line) {
								$aPosts[$pid]['content']['lines'][$x]['name'] = (string) $line['name'];
								$aPosts[$pid]['content']['lines'][$x]['content'] = (string) $line['phrase'];
							}
							break;
						case 'video' :
							$aPosts[$pid]['content']['caption'] = (string) $post['video-caption'];
							$aPosts[$pid]['content']['source'] = (string) $post['video-source'];
							$aPosts[$pid]['content']['player'] = (string) $post['video-player'];
							break;
						case 'audio' :
							$aPosts[$pid]['content']['caption'] = (string) $post['audio-caption'];
							$aPosts[$pid]['content']['player'] = (string) $post['audio-player'];
							$aPosts[$pid]['content']['url'] = (string) preg_replace('#(?:.*)audio_file=(.*)&color=(?:.*)#','$1',$aPosts[$pid]['content']['player']);
							$aPosts[$pid]['content']['plays'] = (int) $post['audio-plays'];
							break;
					}
				}
			}
		}
	}
	
	protected function __cleanArr(&$aToClean = null) {
		if (!$aToClean) { return false; }
		foreach ($aToClean as $k => &$v) {
			if (is_string($aToClean[$k])) {
				$v = html_entity_decode($aToClean[$k],ENT_COMPAT,'UTF-8');
			    $v = trim($aToClean[$k]);
			}
			if (is_array($aToClean[$k])) { $aToClean[$k] = $this->__cleanArr($aToClean[$k]); }
		}
		return $aToClean;
	}
}
?>
