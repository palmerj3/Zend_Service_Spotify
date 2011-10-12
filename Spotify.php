<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Spotify
 * @copyright  Copyright (c) 2011 Jason Palmer (http://www.jason-palmer.com/)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Spotify.php 1 2011-10-10 12:10:00Z palmerj3 $
 */

class Zend_Service_Spotify
{
    /**
     * Base URI for the REST client
     */
    const URI_BASE = 'http://ws.spotify.com';

	const PATH_ARTIST_SEARCH = '/search/1/artist';
	const PATH_ALBUM_SEARCH = '/search/1/album';
	const PATH_TRACK_SEARCH = '/search/1/track';
	
	const LOOKUP_BASE = '/lookup/1/';
	const LOOKUP_URI_PARAM = 'uri';
	const PATH_LOOKUP_ALBUM = 'spotify:album';
	const PATH_LOOKUP_ARTIST = 'spotify:artist';
	const PATH_LOOKUP_TRACK = 'spotify:track';
	
	const JSON_ACCEPT_HEADER = 'application/json';
	const XML_ACCEPT_HEADER = 'application/xml, text/xml';
	
	/**
	 * Reference to current API Response Format (JSON,XML)
	 *
	 * @var String
	 */
	protected $_responseFormat = null;
	
    /**
     * Reference to REST client object
     *
     * @var Zend_Rest_Client
     */
    protected $_restClient = null;

    /**
     * Stores current URL requested
     *
     * @var String
     */
	protected $_currentURL = null;
	
    /**
     * Performs object initializations
     *
     *
     * @param  string	$responseFormat specify the response format to get back from Spotify API
     * @return void
     */
    public function __construct($responseFormat='XML') {
		$this->setResponseFormat($responseFormat);
    }

	/**
     * Sets response format for Spotify API calls
     *
     *
     * @param  string $responseFormat   Response format for API calls
     * @param  array        $options Additional parameters to refine your query.
     * @return Boolean true|false
     * @throws Zend_Service_Exception
     */
    public function setResponseFormat($responseFormat) {
		$reqFormat = strtoupper($responseFormat);
		switch($reqFormat) {
			case 'JSON':
			case 'XML':
				$this->_responseFormat = $reqFormat;
				return true;
				break;
			default:
				/**
	             * @see Zend_Service_Exception
	             */
	            require_once 'Zend/Service/Exception.php';
	            throw new Zend_Service_Exception('Invalid Response Format.  Supported Formats: JSON, XML.');
				break;
		}
	}
	
	/**
     * Returns a reference to the REST client, instantiating it if necessary
     *
     * @return Zend_Rest_Client
     */

	/**
     * Queries the Artist Search API
     *
     *
     * @param  string $artist   Artist you're searching for
     * @param  int $page        Page to request (defaults to 1)
     * @return Array
     * @throws Zend_Service_Exception
     */
    public function searchByArtist($artist,$page=1) {
		//Ensure page is 1 or higher
		if($page > 0) {
			return $this->querySpotify(self::PATH_ARTIST_SEARCH, 
								array(
									'q' => $artist,
									'page' => $page));
		} else {
			//Invalid page
			/**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('Invalid page request.  Request must be an integer of 1 or higher.');
		}
	}

	/**
     * Queries the Track Search API
     *
     *
     * @param  string $track   	Track you're searching for
     * @param  int $page        Page to request (defaults to 1)
     * @return Array
     * @throws Zend_Service_Exception
     */
    public function searchByTrack($track,$page=1) {
		//Ensure page is 1 or higher
		if($page > 0) {
			return $this->querySpotify(self::PATH_TRACK_SEARCH, 
								array(
									'q' => $track,
									'page' => $page));
		} else {
			//Invalid page
			/**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('Invalid page request.  Request must be an integer of 1 or higher.');
		}
	}
	
	/**
     * Queries the Album Search API
     *
     *
     * @param  string $album   	Album you're searching for
     * @param  int $page        Page to request (defaults to 1)
     * @return Array
     * @throws Zend_Service_Exception
     */
    public function searchByAlbum($album,$page=1) {
		//Ensure page is 1 or higher
		if($page > 0) {
			return $this->querySpotify(self::PATH_ALBUM_SEARCH, 
								array(
									'q' => $album,
									'page' => $page));
		} else {
			//Invalid page
			/**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('Invalid page request.  Request must be an integer of 1 or higher.');
		}
	}
	
	/**
     * Queries the Artist Lookup API
     *
     *
     * @param  string $artistURI   		Spotify Artist URI you're searching for
     * @param  string $detail        	Amount of detail to show - defaults to 'basic'.  Options: 'basic', 'album', 'albumdetail'
     * @return Array
     * @throws Zend_Service_Exception
     */
    public function lookupArtist($artistURI,$detail='basic') {
		//Ensure detail level is proper		
		$extrasParam = strtolower($detail);
		
		switch($extrasParam) {
			case 'basic':
				$extrasParam='';
				break;
			case 'album':
			case 'albumdetail':
				break;
			default:
				//Invalid detail
				/**
	             * @see Zend_Service_Exception
	             */
	            require_once 'Zend/Service/Exception.php';
	            throw new Zend_Service_Exception('Invalid detail level specified.  "Basic", "Album", and "AlbumDetail" supported.');
				break;
		}
		
		return $this->querySpotify(self::LOOKUP_BASE, 
								array(
									self::LOOKUP_URI_PARAM => self::PATH_LOOKUP_ARTIST . ':' . $artistURI,
									'extras' => $extrasParam));

	}
	
	/**
     * Queries the Album Lookup API
     *
     *
     * @param  string $albumURI   		Spotify Album URI you're searching for
     * @param  string $detail        	Amount of detail to show - defaults to 'basic'.  Options: 'basic', 'track', 'trackdetail'
     * @return Array
     * @throws Zend_Service_Exception
     */
    public function lookupAlbum($albumURI,$detail='basic') {
		//Ensure detail level is proper		
		$extrasParam = strtolower($detail);
		
		switch($extrasParam) {
			case 'basic':
				$extrasParam='';
				break;
			case 'track':
			case 'trackdetail':
				break;
			default:
				//Invalid detail
				/**
	             * @see Zend_Service_Exception
	             */
	            require_once 'Zend/Service/Exception.php';
	            throw new Zend_Service_Exception('Invalid detail level specified.  "Basic", "Track", and "TrackDetail" supported.');
				break;
		}
		
		return $this->querySpotify(self::LOOKUP_BASE, 
								array(
									self::LOOKUP_URI_PARAM => self::PATH_LOOKUP_ALBUM . ':' . $albumURI,
									'extras' => $extrasParam));

	}
	
	/**
     * Parses raw API result given response format and returns array
     *
     *
     * @param  string $responseBody   Body of Zend_Rest response
     * @return stdObj|SimpleXMLElement
     * @throws Zend_Service_Exception
     */
	protected function parseResponse($responseBody) {
		switch($this->_responseFormat) {
			case 'JSON':
				return json_decode($responseBody);
				break;
			case 'XML':
				return new SimpleXMLElement($responseBody);
				break;
		}
	}
	
	/**
     * Queries Spotify API and returns results
     *
     *
     * @param  string $url   Artist you're searching for
     * @return Array
     * @throws Zend_Service_Exception
     */
    protected function querySpotify($url,$options) {
		//Ensure Zend_Rest_Client is instantiated for the current URL
		if($this->_restClient == null || $this->_currentURL != $url) {
			require_once 'Zend/Rest/Client.php';

			$this->_restClient = new Zend_Rest_Client(self::URI_BASE . $url);
		}
		
		//Set 'accept' headers so proper response format is set
		if($this->_responseFormat != null) {
			switch($this->_responseFormat) {
				case 'JSON':
					$this->_restClient->getHttpClient()->setHeaders(array(
						'Accept' => self::JSON_ACCEPT_HEADER
					));
					break;
				case 'XML':
					$this->_restClient->getHttpClient()->setHeaders(array(
						'Accept' => self::XML_ACCEPT_HEADER
					));
					break;
			}						
		} else {
			/**
             * @see Zend_Service_Exception
             */
            require_once 'Zend/Service/Exception.php';
            throw new Zend_Service_Exception('Response format is not set.  Unable to query Spotify API.');
		}
		
		//Reset Zend_Rest_Client
		$this->_restClient->getHttpClient()->resetParameters();
		$response = $this->_restClient->restGet($url,$options);
		
		//Ensure code is acceptable
		switch($response->getStatus()) {
			case 200: //OK
			case 304: //Not Modified
				return $this->parseResponse($response->getBody());
				break;
			case 403: //Forbidden - rate limiting
				/**
	             * @see Zend_Service_Exception
	             */
	            require_once 'Zend/Service/Exception.php';
	            throw new Zend_Service_Exception('Spotify rate limiting has kicked in');
				break;
			case 404: //Unable to locate - or you changed the URI.. in which case.. NO DATA FOR YOU!
				return false;
				break;
			default:
				/**
	             * @see Zend_Service_Exception
	             */
	            require_once 'Zend/Service/Exception.php';
	            throw new Zend_Service_Exception('Invalid request.  Response code: ' . $response->getStatus());
				break;
		}
	}
}

