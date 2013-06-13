<?php
/**
 * Rapummies
 *
 * Rap lines for dummies. PHP plugin for Rapgenius.
 * Get rap lines and annotation/interpretation
 *
 * Copyright (c) 2013 - 92 Bond Street, Yassine Azzout
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 *	The above copyright notice and this permission notice shall be included in
 *	all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package Rapummies
 * @version 1.0
 * @copyright 2013 - 92 Bond Street, Yassine Azzout
 * @author Yassine Azzout
 * @link http://www.92bondstreet.com Rapummies
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

 
// token to database
require_once('token.php');	
// SwissCode plugin
// Download on https://github.com/92bondstreet/swisscode
require_once('swisscode.php');	

// RAPGENIUS  urls
define("RAPGENIUS","http://rapgenius.com");
define("RAPGENIUS_SEARCH","http://rapgenius.com/search?q=");
define("RAPGENIUS_ACCEPTED","accepted");	//reviewed by the Rap Genius editors
define("RAPGENIUS_ROUGH","rough");
 
 //Report all PHP errors
error_reporting(E_ALL);
set_time_limit(0);


class RapLine { 
	// Song name
	public $song = "";
	// Artist/producer... name
	public $artist = "";
	// Song url on rapgenius
	public $rapgenius_url = "";
	// rap line
	public $line = "";
	// rap line intepretation url
	public $interpretation = "";
}


class Rapummies {
	
	// Database to save
	private  $pdodb;
		
	// file dump to log
	private  $enable_log;
	private  $log_file_name = "rapummies.log";
	private  $log_file;
	
	
	/**
	 * Constructor, used to input the data
	 *
	 * @param bool $log
	 */
	public function __construct($log=false){
	
		if(defined('DB_NAME') && defined('DB_USER') && defined('DB_PWD') ){
			try{
				$pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION;		
				$this->pdodb = new PDO(DB_NAME, DB_USER, DB_PWD,$pdo_options);
				$this->pdodb->exec("SET CHARACTER SET utf8");
			}
			catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
				$this->pdodb = null;
			}
			
		}
		else
			$this->pdodb = null;
				
		$this->enable_log = $log;
		if($this->enable_log)
			$this->log_file = fopen($this->log_file_name, "w");
		else
			$this->log_file = null;
			
	}
	
	/**
	 * Destructor, free datas
	 *
	 */
	public function __destruct(){
	
		// and now we're done; close it
		$this->pdodb = null;
		if(isset($this->log_file))
			fclose($this->log_file);
	}
	
	/**
	 * Write to log file
	 *
	 * @param $value string to write 
	 *
	 */
	function dump_to_log($value){
		fwrite($this->log_file, $value."\n");
	}
	
	
	
	/**
	 * Get all lines and annotation/interpretation from song url
	 *
	 * @param 	$song_url 		on rapgenius 	 
	 *
	 * @return array|null
	 */
	
	function raplines_song($song_url){
			
		$results = array();

		//Step 0: get lyrics
		$html = MacG_connect_to($song_url);
		$html = str_get_html($html);		
		$lines = $html->find('a[data-editorial-state='.RAPGENIUS_ACCEPTED.'],a[data-editorial-state='.RAPGENIUS_ROUGH.']');		// only accepted lines
		
		// Get song name and artist
		$rapgenius_url = $song_url;  
		$artist = $html->find('h1.song_title',0);
		if(isset($artist))
			$artist = $artist->find('a',0)->plaintext;
		else
			$artist = "";
		$song = $html->find('div.edit_song_description',0);
		if(isset($song))
			$song = $song->find('i',0)->plaintext;
		else
			$song = "";
		
		//Step 0: get lyrics
		foreach($lines as $line){
	
			$rapline = new RapLine;
			
			$rapline->song = $song;
			$rapline->artist = $artist;
			$rapline->rapgenius_url = $rapgenius_url;
			$rapline->interpretation = 'http://rapgenius.com'.$line->href;
			$rapline->line = strip_tags($line->plaintext);
			$rapline->line = trim(preg_replace('/\s\s+/', ' ', $rapline->line));
			$rapline->line = mb_convert_encoding($rapline->line, 'HTML-ENTITIES', 'UTF-8');		
			$rapline->line = addslashes($rapline->line);
				
			// delete lines with [*****]
			if(strstr($rapline->line,'[')!=FALSE)
				continue;
			
			// delete last comma ,
			if (substr($rapline->line, -1) == ',')
				$rapline->line = substr($rapline->line, 0, -1);
			
			$results[] = $rapline;
		}
		
		unset($html);
		
		return $results;
	}
	
	/**
	 * Get all lines and annotation/interpretation of artist/singer/producer
	 *
	 * @param 	$artist 		name or producer...
	 * @param 	$popular 		songs or not
	 *
	 * @return array|null
	 */
	
	function raplines_artist($artist,$popular=true){
		
		$results = array();

		//Step 0: artist url
		$artist_url = $this->search_artist($artist);
		if(isset($artist_url)){
			
			$html = MacG_connect_to($artist_url);
			$html = str_get_html($html);

	
			//Step 1: popular or all songs
			$artist_songs = null;
			if($popular)			
				$artist_songs = $html->find('.song_list',0)->find('li');	// populatr songs of rapgenius
			else
				$artist_songs = $html->find('li[data-id]');		// all songs
			
			//Step 2: get kines
			foreach($artist_songs as $song){
				$song_url = 'http://rapgenius.com'.$song->find('a',0)->href;
				$lines_results = $this->raplines_song($song_url);
				$results = array_merge($results,$lines_results);
			}
						
			unset($html);
		}
		return $results;
		
	}
	
	/**
	 * Get rapgenius url of artist/singer/producer
	 *
	 * @param 	$artist 		name or producer...
	 *
	 * @return string|null
	 */
	 
	function search_artist($artist){
	
		//Step 0: custom url
		$search_url = RAPGENIUS_SEARCH.urlencode($artist);
		$html = MacG_connect_to($search_url);
		$html = str_get_html($html);
		
		//Step 1: get artist url
		$artist_result = $html->find('div.results_container',0);
		$artist_url = NULL;
		if(isset($artist_result))
			$artist_url = RAPGENIUS.$artist_result->find('a',0)->href;		
		
		unset($html);
		
		return $artist_url;
	}
	
	/**
	 * Save results in Database
	 *
	 * @param 	$results 				of raplines
	 * @param 	$results_table_name 	where to insert
	 *
	 * @return bool
	 */
	
	function save($results, $results_table_name){
		
		if(!isset($this->pdodb))
			return false;
		
			
		// Step 0 : save to database
		if(count($results)>0){
		
			// insert query prepared statement
			$query = 'INSERT INTO '.$results_table_name.' (song, artist, rapgenius_url, line, interpretation) VALUES (?, ?, ?, ?, ?);';			
			$pdodb_stmt = $this->pdodb->prepare($query);
		
			
			foreach ($results as $current_result){
							
				// step 1 : insert in db	
				$pdodb_stmt->bindValue(1, $current_result->song);
				$pdodb_stmt->bindValue(2, $current_result->artist);
				$pdodb_stmt->bindValue(3, $current_result->rapgenius_url);
				$pdodb_stmt->bindValue(4, $current_result->line);		
				$pdodb_stmt->bindValue(5, $current_result->interpretation);	
				$pdodb_stmt->execute();					
			}
		}
		else
			return false;
		
		return true;
	}
	
}