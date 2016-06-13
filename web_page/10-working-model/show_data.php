<?php
	/*****************************************************
	 * Classes to more easily store and edit show details.
	 *****************************************************/
	// Episode, contains details about a tv episode, belongs to a Show.
	class Episode {
		public $name;			// String, used as an ID.
		public $date_created;	// Timestamp.
		public $link;			// Magnet link.
		public $downloaded;		// Bool.
		public $show;			// String, name of the Show this belongs to.

		public function __construct($name, $date_created, $link, $downloaded, $show){
			$this->name = $name;
			$this->date_created = $date_created;
			$this->link = $link;
			$this->downloaded = $downloaded;
			$this->show = $show;
		}
		public function __toString(){
			return 'ep : ' . $this->name . '<br>';
		}
	}
	// Show, contains an array of Episodes. Can be marked for auto-download and has a unique name. 
	// All access to Episode should be done through this.
	class Show {
		public $name;		// String, used as an ID.
		public $episodes;	// Array of episodes.
		public $marked;		// Bool, marked for download.
		
		public function __construct($name){
			$this->name = $name;
			$this->episodes = array();
			$this->marked = false;
		}
		public function __toString(){
			$str = 'sh : ' . $this->name . ' : ' . count($this->episodes) . '<br>';
			foreach ($this->episodes as $episode){
				$str .= $episode;
			}
			return $str;
		}
		// Add an episode. If the episode already exists then nothing happens.
		public function addEpisode($name, $date_created, $link, $downloaded){
			foreach ($this->episodes as $episode){
				if ($episode->name == $name) return;
			}
			array_push($this->episodes, new Episode($name, $date_created, $link, $downloaded, $this->name));
		}
		// Comparator for sorting Episodes.
		public static function compareEpisodesByDate($a, $b){
			return -1*strcmp($a->date_created, $b->date_created);
		}
		
	}
	// MyShows, contains an array of shows. All access to Show and Episode should be done through 
	// this.
	class MyShows {
		private $shows;
		
		public function __construct(){
			$this->shows = array();
		}
		// Adds the shows and episodes stored in a JSON file.
		public function parseJSON($json){
			$data = json_decode($json, true);
			foreach($data['shows'] as $show){
				$this->addShow($show['name']);
				$this->markShow($show['name'], $show['marked']);
				foreach ($show['episodes'] as $episode){
					$this->addEpisode($show['name'], $episode['name'], $episode['date_created'], 
								      $episode['link'], $episode['downloaded']);
				}
			}
		}
		// Returns a string containing show data that is stored.
		public function __toString(){
			$str = 'myshows : ' . count($this->shows) . '<br>';
			foreach ($this->shows as $show){
				$str .= $show;
			}
			return $str;
		}
		// Pushes a show to the end of the shows list. If show already exists then nothing happens.
		public function addShow($show_name){
			if ($this->getShow($show_name) == false )
				array_push($this->shows, new Show($show_name));
		}
		// Marks or un-marks a show for auto-download.
		public function markShow($show_name, $marked){
			$this->getShow($show_name)->marked=$marked;
		}
		// Adds an episode to the specified show. If the episode already exists then nothing 
		// happens. Show must exist.
		public function addEpisode($show_name, $name, $date_created, $link, $downloaded){
			$this->getShow($show_name)->addEpisode($name, $date_created, $link, $downloaded);
		}
		// Adds an episode from RSS format, if the episode already exists then nothing happens.
		public function parseEpisode($title, $desc, $link, $date){
			//echo "Checking: " . $title . "<br>";
			// Split title into Show name and Episode name
			$str_parts = explode(' ', $title);
			$show_name = '';
			$ep_name = '';
			$part = 0;
			foreach ($str_parts as $str){
				if (preg_match('/[0-9]/', $str)) $part = 1;
				if ($part == 0){
					$show_name .= $str . ' ';
				} else {
					$ep_name .= $str . ' ';
				}
			}
			$show_name = trim($show_name);
			$ep_name = trim($ep_name);
			//print($show_name . ", " . $ep_name . "<br>");
			// Check if show already exists or create a new one
			foreach ($this->shows as $show){
				if ($show_name == $show->name){
					$this->addEpisode($show_name, $ep_name, $date, $link, false);
					return;
				}
			}
			$this->addShow($show_name);
			$this->addEpisode($show_name, $ep_name, $date, $link, false);
			
		}
		// Returns the show variable that matches the given name.
		public function getShow($show_name){
			foreach ($this->shows as $show) {
				if ( $show->name == $show_name ) return $show;
			}	
			return false;
		}
		// Returns the JSON encoded version.
		public function toJSON(){
			return json_encode(get_object_vars($this));
		}
		// Returns a string that creates the Episode list table in HTML.
		public function getEpisodeTable(){
			// Get a sorted list of all episodes.
			$all_lists = $this->getAllEpisodes();
			// Put them all into the table.
			$table = "<table style='width:100%'>";
			$row_num = 0;
			foreach ($all_lists as $ep){
				$colour = '#E6E6E6';
				if ($ep->downloaded == true){
					$colour = '#A9F5BC';
				}
				// Set 
				$table .= "<tr bgcolor=$colour>";
				$table .= "<td align='center'>" . $ep->show . "</td>";
				$table .= "<td align='center'>" . $ep->name . "</td>";
				$table .= "<td align='center'>" . date('d-m-Y H:i:s',$ep->date_created) . "</td>";
				$table .= "<td align='center'>" . 
					"<button type='button' onclick='downloadShow($row_num)'";
				if ($ep->downloaded) $table .= "disabled";
				$table .= ">Download</button>" . "</td>";
				$table .= "<td align='center'>" . 
					"<button type='button' onclick='deleteShow($row_num)'";
				if (! $ep->downloaded) $table .= "disabled";
				$table .= ">Delete</button>" . "</td>";
				$table .= "</tr>";
				
				$row_num+=1;
			}
			$table .= "</table>";
			return $table;
		}
		// Returns all Episodes sorted by date.
		public function getAllEpisodes(){
			$all_lists = array();
			foreach ($this->shows as $show){
				$all_lists = array_merge($all_lists, $show->episodes);
			}
			usort($all_lists, array('Show','compareEpisodesByDate'));
			return $all_lists;
		}
		// Returns the torrent ID from the episode and transmission list output. Note that when
		// transmission gets a new torrent, for a while it has the name thats a subsection of the
		// 'xt' section of the episode link.
		public function getTorrentID($ep, $torrent_info){
			// PHASE 1 - Check if torrent name is the subsection of magnet torrent 'xt'.
			// Get the magnet 'xt' bit.
			//print("<br><br>");
			$exp = explode('&', $ep->link);
			//var_dump($exp);
			$magnet_name;
			foreach ($exp as $str){
				if (strpos($str, 'xt=urn:btih:') !== false){
					$magnet_name = strtolower(substr($str, 20));
					break;
				}
			}
			//print("<br>Magnet name: " . $magnet_name);
			// Find the torrent row that has the magnet name, and get the ID.
			foreach ($torrent_info as $item){
				if (strpos($item, $magnet_name) !== false){
					//print("<br>Row: " . $item);
					$row = explode(" ", $item);
					$id = $row[2];
					return $id;
				}
			}
			// PHASE 2 - Check if torrent name is similar to the magnet torrent 'dn'
			// Get the magnet name.
			//print("<br><br>");
			$exp = explode('&', $ep->link);
			//var_dump($exp);
			$magnet_name;
			foreach ($exp as $str){
				if (strpos($str, 'dn=') === 0){
					$magnet_name = substr($str, 3);
					break;
				}
			}
			//print("<br>Magnet name: " . $magnet_name);
			// Explode the magnet name into an array, as tokens in between words are different.
			$magnet_names = explode('+', $magnet_name);
			// Find the torrent row that has the magnet name, and get the ID.
			foreach ($torrent_info as $item){
				$match = true;
				foreach ($magnet_names as $part){
					if (strpos($item, $part) === false){
						$match = false;
						break;
					}
				}
				if ($match == true){
					//print("<br>Row: " . $item);
					$row = explode(" ", $item);
					$id = $row[2];
					return $id;
				}
			}
			return -1;
		}
	}
	/*****************************************************
	 * Helpful Functions
	 *****************************************************/
	// Converts from RSS date format to a timestamp.
	function rssToTime($rss_time) {
        $day = substr($rss_time, 5, 2);
        $month = substr($rss_time, 8, 3);
        $month = date('m', strtotime("$month 1 2011"));
        $year = substr($rss_time, 12, 4);
        $hour = substr($rss_time, 17, 2);
        $min = substr($rss_time, 20, 2);
        $second = substr($rss_time, 23, 2);
        $timezone = substr($rss_time, 26);
        $timestamp = mktime($hour, $min, $second, $month, $day, $year);
        date_default_timezone_set('UTC');
        if(is_numeric($timezone)) {
            $hours_mod = $mins_mod = 0;
            $modifier = substr($timezone, 0, 1);
            $hours_mod = (int) substr($timezone, 1, 2);
            $mins_mod = (int) substr($timezone, 3, 2);
            $hour_label = $hours_mod>1 ? 'hours' : 'hour';
            $strtotimearg = $modifier.$hours_mod.' '.$hour_label;
            if($mins_mod) {
                $mins_label = $mins_mod>1 ? 'minutes' : 'minute';
                $strtotimearg .= ' '.$mins_mod.' '.$mins_label;
            }
            $timestamp = strtotime($strtotimearg, $timestamp);
        }
        return $timestamp;
	}
	/*****************************************************
	 * Start Script
	 *****************************************************/
	// Set environment variables
	date_default_timezone_set('Australia/Adelaide');
	// Get the mode of the request.
	// ModeToken	Mode 				OtherArgsRequired	Description
	// df			Display Feed		-					Returns a table with most recent 
	//														episodes.
	// de 			Download Episode	tr					Downloads the episode referring to the 
	//														table row, returns the updated table.
	// re			Remove Episode		tr					Deletes the episode torrent and data.
	$mode=$_REQUEST["mode"]; 
	// Get additional arguments
	// ArgumentToken	Argument		Description
	// tr 				Table Row		Describes the table row index starting at 0.
	$tr=$_REQUEST["tr"];	// Table Row
	
	// Load the current show data.
	$myshows = new MyShows();
	$file_contents = file_get_contents('showdata.json'); 
	$decoded_file_contents = json_decode($file_contents);//, true);
	$myshows->parseJSON($file_contents);
	
	//print("Mode: " . $mode . "<br>Table Row: " . $tr );
	
	// Display Feed
	if ($mode == 'df'){
		print($myshows->getEpisodeTable());
	} 
	// Update Feed
	elseif ($mode == 'uf'){
		// Get the feed
		$rss = new DOMDocument();
		//$rss->load('http://showrss.info/rss.php?user_id=257329&hd=0&proper=0');
		$rss->load('https://showrss.info/user/67971.rss?magnets=false&namespaces=true&name=clean&quality=sd&re=null');
		$feed = array();
		foreach ($rss->getElementsByTagName('item') as $node) {
			$item = array ( 
				'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
				'desc' => $node->getElementsByTagName('description')->item(0)->nodeValue,
				'link' => $node->getElementsByTagName('link')->item(0)->nodeValue,
				'date' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
				);
			array_push($feed, $item);
			$myshows->parseEpisode($item['title'], $item['desc'], $item['link'], rssToTime($item['date']));
		}
		
		print($myshows->getEpisodeTable());
	}
	// Download Episode
	elseif ($mode == 'de'){
		// Get the episode
		$episodes = $myshows->getAllEpisodes();
		$episode = $episodes[intval($tr)];
		// Mark as downloaded
		$episode->downloaded = true;
		// Download
		exec('bash download_torrent.sh ' . $episode->link, $output);
		// Return the table
		print($myshows->getEpisodeTable());
		// Print logs
		print('bash download_torrent.sh ' . $episode->link);
		print ("<br><br>");
		var_dump($output);
	} 
	// Remove Episode
	elseif ($mode == 're'){
		// Get the episode
		$episodes = $myshows->getAllEpisodes();
		$episode = $episodes[intval($tr)];
		// Mark as not downloaded
		$episode->downloaded = false;
		// Get the list of torrents
		exec('bash list_torrents.sh', $output);
		// Find the torrent ID
		$id = $myshows->getTorrentID($episode, $output);
		// Remove
		$output = array();
		exec('bash remove_torrent.sh ' . $id, $output);
		// Return the table
		print($myshows->getEpisodeTable());
		// Print the logs
		print("<br>ID: " . $id . "<br>");
		print('bash remove_torrent.sh ' . $id);
		print ("<br><br>");
		var_dump($output);

	} else {
		echo "No input found.<br>";
	}
	
	// Store the show data again
	$encoded_file_contents = $myshows->toJSON();
	file_put_contents("showdata.json", $encoded_file_contents);
	
	print("<br><br>");
	print($myshows);
	print("<br><br>");

	


?>