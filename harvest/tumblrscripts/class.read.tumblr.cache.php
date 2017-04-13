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

class readTumblrCache extends readTumblr {
	protected $sCacheDir = null;
	protected $nCacheTime = null;
	protected $bLog = null;
	protected $oLogFile = null;
	
	public function __construct($sTumblrID = null,$sHTTPUserAgent = 'phpTumblr',$sCacheDir = null,$nCacheTime = 3600,$bLog = false) {
		$this->sCacheDir = dirname(__FILE__).'/../../tmp/tcxml';
		$this->sCacheDir = path::clean(path::real($sCacheDir));
		$this->nCacheTime = $nCacheTime;
		$this->bLog = $bLog;
		
		$this->__putLog('Constructing on '.$sTumblrID);
		parent::__construct($sTumblrID,$sHTTPUserAgent);
	}
	
	public function __destruct() {
		$this->__flushCache();
		$this->__putLog('Destructing');
		$this->__putLog();
	}
	protected function __apiRead($aParams = array()) {
		$sRequest = '';
		foreach ($aParams as $k => $v) { $sRequest .= $k.'-'.$v.' '; }
		$this->__putLog('Requesting '.$this->sTumblrID.'.'.$sRequest);
		
		if (!$this->__cacheRead($aParams)) {
			parent::__apiRead($aParams);
			$this->__cacheWrite($aParams);
		}
		return true;
	}
	
	protected function __putLog($sText = '') {
		if (!$this->bLog) { return true; }
		if (!$this->oLogFile) { $this->oLogFile = fopen($this->sCacheDir.'/log.txt','a'); }
		$oLogFile = &$this->oLogFile;
		if ($sText != '') {
			fputs($oLogFile,'['.date('c').'] '.$sText."\n");
		} else {
			fputs($oLogFile,"\n\n");
		}
		//fclose($oLogFile);
		return true;
	}
	
	protected function __cacheRead($aParams = array()) {
		$sCacheFile = $this->__cacheFile($aParams);
		if (file_exists($sCacheFile)) {
			if (time() - filemtime($sCacheFile) <= $this->nCacheTime) {
				$this->aTemp = unserialize(file_get_contents($sCacheFile));
				$this->__putLog('Reading from cache');
				
				if ($this->bDebug) {
					$sKey = '';
					foreach ($aParams as $k => $v) {
						$sKey .= $k.$v;
					}
					$this->aDebug[$sKey] = $this->aTemp;
				}
				
				return true;
			} else {
				$this->__putLog('Reading from API');
				return false;
			}
		} else {
			$this->__putLog('Reading from API');
			return false;
		}
	}
	
	protected function __cacheWrite($aParams = array()) {
		file_put_contents($this->__cacheFile($aParams),serialize($this->aTemp));
		$this->__putLog('Writing cache');
		return true;
	}
	
	protected function __cacheFile($aParams = array()) {
		$sCacheFile = $this->sTumblrID;
		foreach ($aParams as $k => $v) {
			if ($k == 'search') { $v = md5($v); }
			$sCacheFile .= '.'.$k.'-'.$v;
		}
		$sCacheFile .= '.inc';
		$sCacheFile = $this->sCacheDir.'/'.$sCacheFile;
		return $sCacheFile;
	}
	
	protected function __flushCache() {
		$this->__putLog('Flushing cache');
		$sCacheFiles = files::scanDir($this->sCacheDir);
		foreach ($sCacheFiles as $v) {
			$sCacheFile = $this->sCacheDir.'/'.$v;
			$sCacheFile = path::clean(path::real($sCacheFile));
			if (is_file($sCacheFile)) {
				if (time() - filemtime($sCacheFile) >= $this->nCacheTime) {
					unlink($sCacheFile);
					$this->__putLog('Deleting '.$v);
				}
			}
		}
		$sCacheFiles = files::scanDir($this->sCacheDir);
		return true;
	}
}
?>
