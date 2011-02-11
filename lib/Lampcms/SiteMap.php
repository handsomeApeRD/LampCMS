<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website's Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */


namespace Lampcms;

/**
 * Generate sitemap about every 4-6 hours
 * Once it's generated, create a dir if necessary based on date
 * /sitemap/2010/08/02/
 *
 * Update SITEMAPS table to update latest item ID
 *
 * save it there as .tgz file,
 * update the master sitemap file, resave it
 * ping Google and other services like Bing, Yahoo
 * using that NEW file (new file only, not master file)
 *
 * Always use urlencode() for location part of XML
 * Always make sure it's in UTF-8, which is basically automatically done
 * in our case since our url_text is always utf-8
 *
 *
 * @author Dmitri Snytkine
 *
 */
class SiteMap extends LampcmsObject
{
	/**
	 * Object of type clsMongoDoc
	 * that holds array of latest IDs
	 *
	 * @var object clsMongoDoc
	 */
	protected $oLatest;

	/**
	 * This is used to create a new sitemap xml file
	 *
	 * @var string
	 */
	const XML_START = '<?xml version="1.0" encoding="UTF-8"?>
	<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>';


	/**
	 * This is used to create new sitemap index file
	 * with collection of sitemaps
	 *
	 * @var string
	 */
	const INDEX_START = '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"/>';


	/**
	 * Path to writable global site map file
	 * that holds list of daily sitemaps
	 * New maps will be appended to this file as needed!
	 *
	 * it should already exist and be in xml format
	 * this is relative to the writable directory 'w'
	 * whis is this constant: LAMPCMS_DATA_DIR
	 *
	 * @var string
	 */
	protected $rootMapFilePath = 'sitemap/index.xml';

	/**
	 * SimpleXMLElement class
	 *
	 * @var object of type SimpleXMLElement
	 */
	protected $oSXESitemap;

	/**
	 * Name of sitemap of this file
	 * It will be eithre supplied to the run() method
	 * or generated based on today date like sitemap_20100808.xml
	 *
	 * @var string
	 */
	protected $siteMapName;

	/**
	 *
	 * @var unknown_type
	 */
	protected $siteMapGz;

	/**
	 * SimpleXML representing urlindex sitemap file
	 * this is object created from the rootMapFilePath file
	 * it holds collection of xml.tgz files
	 * we append newly created sitemap to it
	 *
	 * @var object of type SimpleXMLElement
	 */
	protected $oSXEIndexMap;

	protected $aLatestIds = array();


	protected $aPingUrls = array('bing' => 'http://www.bing.com/webmaster/ping.aspx?siteMap=%s',
	'google' => 'http://www.google.com/webmasters/sitemaps/ping?sitemap=%s',
	'yahoo' => 'http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=YahooDemo&url=%s');


	public function run($fileName = null){

		$this->siteMapName = $fileName;

		$this->getLatestIds()
		->makeSXEObject()
		->addNewQuestions()
		->saveNewMapFile()
		->updateIndexFile()
		->pingSearchSites();
	}

	protected function getLatestIds()
	{
				
		$oMongo = $this->oRegistry->Mongo;
		$aLatest = $oMongo->getCollection('SITEMAP_LATEST')->findOne();
		$aLatest = (!$aLatest) ? array() : $aLatest;
		$this->oLatest = new MongoDoc($this->oRegistry, 'SITEMAP_LATEST', $aLatest);
		
		return $this;
	}

	
	/**
	 * Create root object for the new
	 * sitemap
	 *
	 *  @return object $this
	 */
	protected function makeSXEObject()
	{
		if(false === $this->oSXESitemap = simplexml_load_string(self::XML_START)){
			throw new DevException('Unable to load xml file '.self::XML_START);
		}

		return $this;
	}


	public function addNewQuestions()
	{

		$id = (int)$this->oLatest['i_qid'];
		d('latest QID: '.$id);
		$urlPrefix = $this->oRegistry->Ini->SITE_URL.'/';
		/**
		 * @todo change this to $this->oRegistry->Mongo
		 */
		$oMongo = $this->oRegistry->Mongo;
		$coll = $oMongo->getCollection('QUESTIONS');
		$cursor = $coll->find(array('_id' => array( '$gt' => $id)), array('_id', 'url', 'i_ts'))->limit(12000);

		d('cursor: '.get_class($cursor));
		if($cursor && ($cursor instanceof MongoCursor) && ($cursor->count() > 0)){
			d('cursor count: '.$cursor->count());
			foreach($cursor as $aMessage){
				if(!empty($aMessage)){
					$loc = $urlPrefix.'q'.$aMessage['_id'].'/'. $aMessage['url'];
					$lastmod = date('Y-m-d', $aMessage['i_ts']);

					$this->addUrl($loc, $lastmod, 'yearly');

					$this->oLatest['i_qid'] = $aMessage['_id'];
				}
			}
		}

		d('latest qid: '.$this->oLatest['i_qid']);

		return $this;
	}

	
	/**
	 * Save newly generated sitemap file
	 *
	 * @return object $this
	 */
	protected function saveNewMapFile()
	{

		$this->siteMapName = (null !== $this->siteMapName) ? $this->siteMapName : 'sitemap_'.date('Ymd').'.xml';
		$xmlFile = LAMPCMS_DATA_DIR.'sitemap/'.$this->siteMapName;

		/**
		 * If this sitemap file already exists then we
		 * can either reuse it and append new urls to IT
		 * OR create a new file with timestamp prefixed to it.
		 * Google recommends NOT to keep creating many new files
		 * but instead modify existing ones, but
		 * it's not mandatory and if we worry about files being
		 * close to site/file size limit then we should create new
		 * files
		 */
		if(file_exists($xmlFile)){
			$this->siteMapName = time().'_'.$this->siteMapName;
			$xmlFile = LAMPCMS_DATA_DIR.'sitemap/'.$this->siteMapName;
		}

		d('xmlFile: '.$xmlFile);

		if(false === $this->oSXESitemap->asXML($xmlFile)){
			$err = 'Unable to save new sitemap file: '.$xmlFile;
			d($err);

			throw new DevException($err);
		}

		return $this;
	}

	
	/**
	 * Append the main sitemap index file
	 * and add the latest just created sitemap to it
	 * and then resave it
	 *
	 * @return object $this
	 */
	protected function updateIndexFile()
	{
		$file = LAMPCMS_DATA_DIR.$this->rootMapFilePath;
		if(file_exists($file)){
			if(!is_writable($file)){
				throw new DevException('file: '.$file.' is not writable');
			}

			if(false === $this->oSXEIndexMap = simplexml_load_file($file)){
				throw new DevException('Unable to load xml file: '.$file);
			}
		} else {
			if(false === $this->oSXEIndexMap = simplexml_load_string(self::INDEX_START)){
				throw new DevException('Unable to load xml string: '.self::INDEX_START);
			}
		}

		$oMap = $this->oSXEIndexMap->addChild('sitemap');
		$oMap->addChild('loc', $this->oRegistry->Ini->SITE_URL.'/w/sitemap/'.$this->siteMapName);
		$oMap->addChild('lastmod', date('c'));

		if(false === $this->oSXEIndexMap->asXml($file)){
			throw new DevException('Unable to save sitemap index file: '.$file);
		}

		return $this;
	}

	
	/**
	 * Accepts array with keys like
	 * url, lastmod, howOften
	 * and appends them as DOM Elements to root
	 * @param string $url
	 * @param string $time must be in W3C Datetime format!
	 * @param string $changefreq change frequency: one of these values:
	 *
	 *  always
	 *  hourly
	 *  daily
	 *  weekly
	 *  monthly
	 *  yearly
	 *  never
	 *
	 *  @return object $this
	 */
	protected function addUrl($url, $time, $changefreq = 'yearly')
	{
		$oSXUrl = $this->oSXESitemap->addChild('url');
		
		$oSXUrl->addChild('loc', $url);
		$oSXUrl->addChild('lastmod', $time);
		$oSXUrl->addChild('changefreq', $changefreq);

		return $this;
	}


	/**
	 * Ping a bunch of search engines to tell
	 * them about our new sitemap file
	 *
	 * @return object $this
	 */
	protected function pingSearchSites()
	{

		$oHttp = new Curl();
		$url = $this->oRegistry->Ini->SITE_URL.'/w/sitemap/'.$this->siteMapName;

		foreach($this->aPingUrls as $key => $val){
			try{
				$pingUrl = sprintf($val, $url);
				d('going to ping '.$key.' url: '.$pingUrl);

				$oHttp->getDocument($url);
				$code = $oHttp->getHttpResponseCode();
				d('pinged '.$key.' response code: '.$code);
					
			} catch (\Exception $e){
				$err = 'Unable to ping '.$key.' got error: '.$e->getMessage();
				d('Error: '.$err);
			}
		}

		return $this;
	}
}


?>
