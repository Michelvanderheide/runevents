<?php
/*
event.event_pk
event.event_id
event.event_date
event.event_display_date
event.name
event.title
event.description
event.city
event.region
event.country
event.event_url
event.website_url
event.subscription_url
event.results_url
event.distances
event.participants_count

host: mysqldb1
database: avgoor
user: avgoor
password: Asei4cei

webinterface: http://mysql.perrit.nl/


*/

	require_once("logger.php");
	
	//var_dump(preg_match('/[^0-9 km.-]/', '3 km - 6 km - 9 km - 12 km - 15 km - 18 km - 21.1 km'));exit;

	$config['postcodes'] = "";
	$config['usecache'] = false;
	$config['cols_listview'] = array("eventid", "displaydate", "displaydate", "city");
	$config['cols_detailview'] = array("eventid","displaydate", "displaydate", "city", "title", "link", "description");
	/*
	$config['months'] = array('January',
					'February',
					'March',
					'April',
					'May',
					'June',
					'July',
					'August',
					'September',
					'October',
					'November',
					'December');
	*/
	$config['use_db'] = true;
	
	$config['dbtype']     = "mysql";
	$prod = false;
	if ($prod) {
		$config['dbhost']     = "mysqldb1"; //"mysql.perrit.nl";
		$config['dbname']     = "avgoor"; //"avgoor";
		$config['dbuser']     = "avgoor";
		$config['dbpass']     = "Asei4cei"; //"Asei4cei";
	} else {
		$config['dbhost']     = "localhost"; //"mysql.perrit.nl";
		$config['dbname']     = "avgoorwp"; //"avgoor";
		$config['dbuser']     = "avgoor";
		$config['dbpass']     = "dehorst75"; //"Asei4cei";
	}
	$config['months']['January'] 	= '01';
	/*
	$config['months']['February'] 	= '02';
	$config['months']['March'] 		= '03';
	$config['months']['April'] 		= '04';
	$config['months']['May'] 		= '05';
	$config['months']['June'] 		= '06';
	$config['months']['July'] 		= '07';
	$config['months']['August'] 	= '08';
	$config['months']['September'] 	= '09';
	$config['months']['October'] 	= '10';
	$config['months']['November']	= '11';
	$config['months']['December'] 	= '12';
	*/
	
	//$config['states'] = array('OV');
	$config['states'] = array('FR', 'GR', 'DR', 'FL', 'OV', 'GE', 'LI', 'NB', 'ZE', 'UT', 'ZH', 'NH');
	
	class hk {
		function hk (){
			global $config, $db;
			setlocale(LC_ALL, 'nl');
			$db = new PDO("mysql:host=".$config['dbhost'].";dbname=".$config['dbname'], $config['dbuser'], $config['dbpass']);
			//$conn = new PDO("mysql:host=$dbhost;dbname=$dbname",$dbuser,$dbpass);
		}
		
		function getEventDetails($event_pk, $eventid, $naam) {
			$url = str_replace(" ", "-", "http://www.hardloopkalender.nl/loopevenement/$eventid/$naam");
		
			$html = $this -> getEventPage($url);

			//$html = file_get_contents($url);
			$DOM = new DOMDocument;
			@$DOM->loadHTML($html);

			$arrResult = array( 'datum' => "", 'plaats' => "", 'naam' => "", 'website' => "", 'overig' => "", 'url_inschrijven' => "");
			// Parse TD's to get event details: datum, naam, plaats, afstanden, website, overig
			
			//class="naamevenement"
			$xpath = new DOMXpath($DOM);
			foreach ($xpath->query('//table[@class="loopevenementt"]') as $rowNode) {
				//echo $rowNode->nodeValue; // will be 'this item'
				//exit("naamevenement");
				$tds = $rowNode -> ownerDocument -> getElementsByTagName('td');
//log_debug("getEventDetails loopevenement:".  $tds -> length);
			}

			$items = $DOM->getElementsByTagName('a');
//log_debug("getEventDetails A items:".$items->length);
			for ($i = 0; $i < $items->length; $i++) {
log_debug("getEventDetails item:".$items->item($i)-> nodeValue);
				if (trim($items->item($i)-> nodeValue) == "op de website") {
log_debug("Website:". $items->item($i) -> getAttribute("href"));
					$arrResult['website_url'] = $items->item($i) -> getAttribute("href");
				} else if (stristr($items->item($i)-> nodeValue,'google maps')) {
log_debug("google_maps:". $items->item($i) -> getAttribute("href"));
					$arr = explode('&', $items->item($i) -> getAttribute("href"));
					if (is_array($arr)) {
						$addressLong = str_replace("+", " ", $arr[count($arr)-1]);
log_debug("google_maps addressLong:".$addressLong);						
						$latLng = $this -> getLatLng($addressLong);
log_debug("google_maps latLng:".print_r($latLng, true));						
						$arr2 = explode(',', $addressLong);
log_debug("google_maps address:".print_r($arr2, true));
						$arrResult['address'] = str_replace("q=","",$arr2[0]);
						if (is_array($latLng) && count($latLng) == 2) {
							$arrResult['lat'] = $latLng['lat'];
							$arrResult['lng'] = $latLng['lng'];
						}
					}
				}
			}

			
			$items = $DOM->getElementsByTagName('td');
//log_debug("getEventDetails items:".$items->length);
			for ($i = 0; $i < $items->length; $i++) {
				$v = trim(strtolower($items->item($i)-> nodeValue));
				$v = str_replace('halve marathon', "21.1 km", $v);
				$v = str_replace('marathon', "42.2 km", $v);
				
				// todo: improve pattern matching
				//if((strstr($v, ' km') || strstr($v, ' engelse mijl') || strstr($v, ' marathon') ) && !strstr($v, 'uitslag') && !strstr($v, 'wijzig') && !strstr($v, 'eten') && !strstr($v, 'agenda') && !strstr($v, 'start') && !strstr($v, 'prijs') && !strstr($v, 'uur')) {
				if (!preg_match('/[^0-9 km.-]/', $v)) {
					
					//$arrResult['afstanden'] = $v;
					$arrAfstanden = explode(' - ', $v);
//log_debug("getEventDetails FOUND:".print_r($arrAfstanden,true));
					$arrResult['afstanden'] = $v;
					$arrResult['event_distances'] = $arrAfstanden;
					
				}
	

			}

			// Parse IFRAME's to get inschrijven.nl url, if any
			
			$items = $DOM->getElementsByTagName('iframe');
			if ($items) {
				for ($i = 0; $i < $items->length; $i++) {
					$attr = $items->item($i) -> attributes;
					$arrResult['url_inschrijven'] = $attr -> getNamedItem("src") -> nodeValue;
				}
			}
			return $arrResult;
		}
		
		
		function getEventPage($url) {
			$tuCurl = curl_init();
			curl_setopt($tuCurl, CURLOPT_URL, $url);
			curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($tuCurl, CURLOPT_HEADER, 0);
			$tuData = curl_exec($tuCurl); 
			if(curl_errno($tuCurl)){
				echo 'Curl error: ' . curl_error($tuCurl);
			}
			curl_close($tuCurl);
			return $tuData;
		}

		function getDbMarkers($filter=array()) {
			global $db;
			$sql = 'SELECT id, city, lat, lng FROM markers WHERE 1=1 ';
			
			foreach($filter as $col => $val) {
				$sql .= " AND $col=:$col";
			}
			$q = $db->prepare($sql);
			$q -> execute($filter);
			
			$queryResult = $q->fetchAll(PDO::FETCH_ASSOC);
			$q->closeCursor();	
			return $queryResult;
		}

		
		function getDbRunningEvents($filter=array(), $extrafilter='', $orderby=' ORDER BY event_date ', $limit='') {
			global $db;
			/*
			[event_pk] => 9
            [event_id] => 1416479819
            [event_date] => 2014-12-14 00:00:00
            [event_display_date] => zo 14 dec 2014
            [name] => Kerstmannenrun-het-Hulsbeek
            [title] => zo 14 dec 2014, Kerstmannenrun het Hulsbeek, Oldenzaal (OV)
            [description] => zo 14 dec 2014, Kerstmannenrun het Hulsbeek, Oldenzaal (OV)
            [city] => Oldenzaal
            [region] => OV
            [country] => 
            [event_url] => http://www.hardloopkalender.nl/loopevenement/1416479819/Kerstmannenrun-het-Hulsbeek
            [website_url] => 
            [subscription_url] => 
            [results_url] => 
            [distances] => 
            [participants_count] =>
			
"link":"http:\/\/www.hardloopkalender.nl\/loopevenement\/1418234688\/Scharenborg-Crossloop","name":"Scharenborg-Crossloop","eventid":"1418234688","id":0,"title":"zo 14 dec 2014, Scharenborg Crossloop, Lichtenvoorde, gemeente Oost Gelre (GE)","displaydate":"zo 14 dec 2014","date":"14\/12\/2014","event_date":"2014-12-14","displayname":"Scharenborg Crossloop","city":"Lichtenvoorde","description":"zo 14 dec 2014, Scharenborg Crossloop, Lichtenvoorde, gemeente Oost Gelre (GE)","filterMonthOfYear":null,"filterYear":null,"period":false,"datum":"","plaats":"","naam":"","website":"","overig":"","url_inschrijven":""}			
			*/
			$sql = "SELECT event_pk as id,
					event_id as eventid,
					uitslagen_nl_event_id,
					concat_ws('/',SUBSTRING(event_date,9, 2),SUBSTRING(event_date,6,2),SUBSTRING(event_date,1,4)) as date,
					event_date,
					event_display_date as displaydate,
					name,
					title,
					description,
					address,
					lat as latitude,
					lng as longitude,
					running_event.city as city,
					region,
					country,
					website_url,
					event_url,
					subscription_url,
					distances as afstanden,
					program
					FROM running_event WHERE 1=1
					";
			$values = false;
			if (is_array($filter)) {
				foreach($filter as $col => $val) {
					if (is_array($val)) {
						$sql .= " AND $col ".$val['oper']." :".$col;
						$values [] = $val['val'];
					} else {
						$sql .= " AND $col=:$col";
						$values [":$col"] = $val;
					}
					
				}
			}
			$sql .= $extrafilter . $orderby. $limit;
			$q = $db->prepare($sql);
log_debug("getDbRunningEvents:$sql,".print_r($values,true));
			if ($values)
				@$q -> execute($values);
			else
				@$q -> execute();
			
			$queryResult = $q->fetchAll(PDO::FETCH_ASSOC);
log_debug("queryResult:".print_r($queryResult,true));
			$q->closeCursor();	
			return $queryResult;
		}
		
		function getDbRunningEventsByRadius($pc, $radius=40, $period=false, $distances=false, $count=false) {
			global $config;
			$cities = "";
			$filterDistance = '';
			
			//$radius=40;
			if ($pc !== "all") {
				$latLng = $this -> getLatLng(false, $pc);
log_debug("getDbRunningEventsByRadius:$distance,".print_r($latLng,true));
				// Get all cities;
				$markers = $this -> getDbMarkers();
log_debug("markers:".print_r($markers,true));
				// Select cities within radius
				foreach($markers as $marker) {
					$distance = intval(round($this -> getDistance($latLng['lat'], $latLng['lng'], $marker['lat'], $marker['lng'])));
log_debug($latLng['lat'] .",". $latLng['lng'] .",". $marker['lat'] .",". $marker['lng'].":".$this -> getDistance($latLng['lat'], $latLng['lng'], $marker['lat'], $marker['lng']));
log_debug("distance:".$distance. "<=".  $radius);				
					if ($distance <= $radius) {
						if ($cities == '')
							$cities = " AND city IN ('".$marker['city']."'";
						else
							$cities .= ",'".$marker['city']."'";
					}
				}
				if ($cities) {
					$cities .= ")"; 
					//$filter['city'] =  array('val' => $cities, 'oper' => 'IN');
log_debug("distance:$cities  :".print_r($markers,true));
				}
			}
			
			// set period filter if any
			if ($period) {
				$arrPeriod = explode("-", $period);
				if (count($arrPeriod)) {
					$month = $arrPeriod[0];
					$year =  $arrPeriod[1];
					
					if (isset($config['months'][$month])) {
						//$filter['event_date'] = array('val' => $year . '-' . $config['months'][$month].'%', 'oper' => 'like');
						$filterPeriod = " AND event_date like '".$year . '-' . $config['months'][$month]."%'";
					}
				}
			}
			
			if (is_array($distances)) {
				$filterDistance = " AND exists (select * from event_distance where running_event_fk=running_event.event_pk and distance in (".implode(",", $distances)."))";
			}
			
			
			// set City filter
			
			// get db events
log_debug("filter:$cities . $filterPeriod . $filterDistance,".print_r($filter,true));
			$rows = $this -> getDbRunningEvents($filter, $cities . $filterPeriod . $filterDistance);
			//log_debug("result,".print_r($rows,true));
			return $rows;
		}		
		
		function saveMarker($city) {
			global $db;
			$markers = $this -> getDbMarkers(array('city' => $city));
			if (count($markers) == 0) {
				$latLng = $this -> getLatLng($city.", Nederland");
				
				
				try {
				
					$sql = "INSERT INTO markers (city, lat, lng) VALUES (:city, :lat, :lng)";

					$q = $db->prepare($sql);

					$q -> execute(array(':city' => $city,  
										':lat' => $latLng['lat'],
										':lng' => $latLng['lng']));

				} catch (Exception $e) {
				  log_error( "SQL insert Failed: " . $e->getMessage());
				}				
log_debug("saveMarker($city):".print_r($latLng,true));		
			}
		}
		
		function saveEvent($eventid=false, $event_pk=false, $details) {
			global $config, $db;
			
			if ($config['use_db']) {
			
				if ($details['city'])
					$this -> saveMarker($details['city']);
				$events = array();			
				if ($eventid)
					$events = $this -> getDbRunningEvents(array('event_id' => $eventid));
					
				if ($event_pk)
					$events = $this -> getDbRunningEvents(array('event_pk' => $event_pk));

				if (count($events) == 0) {
					
		
log_debug("saveEvent($event_pk,$eventid):".print_r($events, true));

					try {
					
						$sql = "INSERT INTO running_event (event_id, uitslagen_nl_event_id, event_date, event_display_date, name, title, description, address, lat, lng, city, region, country, subscription_url, website_url, event_url, distances, program)
								VALUES (:event_id, :uitslagen_nl_event_id, :event_date, :event_display_date, :name, :title, :description, :address, :lat, :lng, :city, :region, :country, :subscription_url, :website_url, :event_url, :distances, :program)";

						$q = $db->prepare($sql);

						if ($eventid)
							$eventDetails = $this -> getEventDetails($event_pk, $details['eventid'], $details['name']);
							
						if (!$eventDetails['lat']) {
							$latLng = $this -> getLatLng($details['city']. ", Nederland");
							$eventDetails['lat'] = $latLng['lat'];
							$eventDetails['lng'] = $latLng['lng'];
						}
							
						$q -> execute(array(':event_id' => $this -> nullVal($details['eventid']),  
											 ':uitslagen_nl_event_id' => $this -> nullVal($details['uitslagen_nl_event_id']),
											 ':event_date' => $this -> nullVal($details['event_date']),
											 ':event_display_date' => $this -> nullVal($details['displaydate']),
											 ':name' => $this -> nullVal($details['name']),
											 ':title' => $this -> nullVal($details['title']),
											 ':description' => $this -> nullVal($details['description']),
											 ':address' => $this -> nullVal($eventDetails['address']),
											 ':lat' => $this -> nullVal($eventDetails['lat']),
											 ':lng' => $this -> nullVal($eventDetails['lng']),
											 ':city' => $this -> nullVal($details['city']),
											 ':region' => $this -> nullVal($details['region']),
											 ':country' => $this -> nullVal($details['country']),
											 ':subscription_url' => $this -> nullVal($details['subscription_url']),
											 ':website_url' => $this -> nullVal($eventDetails['website_url']),
											 ':event_url' => $this -> nullVal($details['link']),
											 ':distances' => $this -> nullVal($eventDetails['afstanden']),
											 ':program' => $this -> nullVal($eventDetails['program'])
											 ));
						
						$event_pk = $db -> lastInsertId();
log_debug("3b:".$event_pk);						
						if (is_array($eventDetails['event_distances'])) {
							$this -> saveEventDistances($event_pk, $eventDetails['event_distances']);
						}

log_debug("3c");						

					} catch (Exception $e) {
					  log_error( "SQL insert Failed: " . $e->getMessage());
					}
				} else {
					log_debug("Event $event_pk found in DB");
					try {
					
						$sql = "UPDATE running_event SET ";
						
						foreach($details as $k => $v) {
							$updatesets[] = $k.'=:'.$this -> nullVal($k);
							$updatevals[':'.$k] = $v;
						}
						$updatevals[':event_pk'] = $details['event_pk'];
						$sql .= implode(',', $updatesets);
						$sql .= " WHERE event_pk=:event_pk";

						$q = $db->prepare($sql);
log_debug("Update event sql:".$sql);
log_debug("updatevals:".print_r($updatevals,true));
						$q -> execute($updatevals);

					} catch (Exception $e) {
					  log_error( "SQL update Failed: " . $e->getMessage());
					}
				}
			}
			
		}		
		
		function saveEventDistances($event_pk, $distances) {
			global $db;
			
//log_debug("saveEventDistances:".print_r($distances,true));
			if (is_array($distances)) {
				try {
				
					$sql = "INSERT INTO event_distance (running_event_fk, distance) VALUES (:running_event_fk, :distance)";

					$q = $db->prepare($sql);
					foreach ($distances as $distance) {
						if (strstr($distance, 'km')){
							$floatval = floatval(trim(str_replace('km', '', $distance)));
							$meters = $floatval * 1000;
							$q -> execute(array(':running_event_fk' => $event_pk, ':distance' => $meters));							
//log_debug("meters:".$meters);
						}
					}
				



				} catch (Exception $e) {
				  log_error( "SQL insert Failed: " . $e->getMessage());
				}				
log_debug("saveEventDistance($event_pk, $distance):");
			}
		}
		
		function getCachedEvent($eventid) {
			$filename = __DIR__."/activities/".$eventid.".json";
			
			if (is_file($filename)) {
				return file_get_contents($filename);
			}
			return false;
		}
		
		function getCachedEventList($pc, $radius) {
			$rs = array();
			$filename = __DIR__."/activities/".$pc."-".$radius.".json";
			if (is_file($filename)) {
				$rs = file_get_contents($filename);
			}
			return$rs;
		}
		
		function saveEventList($pc, $radius, $jsonStr) {
			if (!is_dir(__DIR__."/activities")) {
				mkdir(__DIR__."/activities/");
			}
			$filename = __DIR__."/activities/".$pc."-".$radius.".json";
			file_put_contents($filename, $jsonStr);
		}
		
		function readEventsUitslagenPuntNL($year=false, $month = false, $state = false) {
			$url = 'http://uitslagen.nl/evenementen.html?';
			
			if ($year) {
				$params[] = 'jr='.$year;
			}
			if ($month) {
				$params[] = 'ma='.$month;
			}
			if ($state) {
				$params[] = 'pr='.$state;
			}
			$url .= implode("&", $params);
log_debug("readEventsUitslagenPuntNL URL:".$url);

			$tuCurl = curl_init();
			curl_setopt($tuCurl, CURLOPT_URL, $url);
			curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($tuCurl, CURLOPT_HEADER, 0);
			$tuData = curl_exec($tuCurl); 
			if(curl_errno($tuCurl)){
				echo 'Curl error: ' . curl_error($tuCurl);
			}
			curl_close($tuCurl);
//log_debug("readEventsUitslagenPuntNL data:".print_r($tuData, true));

			$DOM = new DOMDocument;
			@$DOM->loadHTML($tuData);

			$arrResult = array();
		
			$idx=0;
			$items = $DOM->getElementsByTagName('td');
			for ($i = 0; $i < $items->length; $i++) {
				// date: za 03-01-2015
				// name: Snertloop, Nieuwleusen
			
				$val = trim($items->item($i)-> nodeValue);
				
				//log_debug("readEventsUitslagenPuntNL item(".strlen($val)."):".$val);
				
				// Handle date
				if (strlen($val) == 16) {
					$arr = explode("-", substr($val,3,10));
					if (is_array($arr) && count($arr) == 3) {
						$event_date = $arr[2] . '-' . $arr[1] . '-' . $arr[0];
					}

				}
				if (strstr($val, ', ')) {
					$arr = explode(", ", $val);
					if (is_array($arr) && count($arr) == 2) {
						$name = $arr[0];
						$arrResult[$name]['name'] = $arrResult[$name]['title'] = $name;
						$arrResult[$name]['event_date'] = $event_date;
						$arrResult[$name]['city'] = trim($arr[1]);
					}
					
				}				
				
			}
			$items = $DOM->getElementsByTagName('a');
log_debug("items A:".$items->length);			
			for ($i = 0; $i < $items->length; $i++) {
				$href = trim($items->item($i) -> getAttribute("href"));
log_debug("evenement?id==>".$href);
				$val = $items->item($i)-> nodeValue;
				if (strstr($href, 'evenement?id=')) {
				
					$arr = explode(", ", $val);
					if (is_array($arr) && count($arr) == 2) {
						$name = $arr[0];
						$arrResult[$name]['uitslagen_nl_event_id'] = str_replace('evenement?id=', '', $href);
						$arrResult[$name]['event_url'] = "http://www.uitslagen.nl/".$href;
						$arrResult[$name]['subscription_url'] = "https://inschrijven.nl/formulier?id=".$arrResult[$name]['uitslagen_nl_event_id'];
log_debug("readEventsUitslagenPuntNL($href) val:".$val);
						$html = $this -> getEventPage('http://uitslagen.nl/'.$href); 
log_debug($html);
						$DOMDoc = new DOMDocument;
						@$DOMDoc->loadHTML($html);
						$tds = $DOMDoc->getElementsByTagName('td');
log_debug("tds:".$tds->length);
						for ($j = 0; $j < $tds->length; $j++) {
							$v = trim(strtolower($tds->item($j)-> nodeValue));
log_debug("v:".$v);
							if ($v == 'locatie') {
								$address = $tds->item($j+1)-> nodeValue;
						
								$arrLoc = explode(',', $address);
								if (count($arrLoc) > 1) {
									unset($arrLoc[count($arrLoc)-1]);
									$arrResult[$name]['address'] = implode(",", $arrLoc);
								}
							} else if ($v == 'programma') {
								$arrResult[$name]['program'] = $tds->item($j+1)-> nodeValue;
							}
						}
						$anchors = $DOMDoc->getElementsByTagName('a');
log_debug("anchors:".$tds->length);						
						for ($j = 0; $j < $anchors->length; $j++) {
							$href = trim($anchors->item($j) -> getAttribute("href"));
log_debug("href:".$href);
							if (strstr($href, '?daddr=')) {
								$latLng = explode(',', preg_replace('/.*daddr=/','',$href));
								
								if (count($latLng) == 2) {
									$arrResult[$name]['lat'] = $latLng[0];
									$arrResult[$name]['lng'] = $latLng[1];
								}
							}
						}
					}
				}
			}
log_debug("readEventsUitslagenPuntNL Result:".print_r($arrResult, true));
//exit;
			return $arrResult;

		}
		
		function readRSSFeed($pc, $radius, $period=false, $count=100) {
			global $config;
			$rs = array();
			if ($period) {
				$arrPeriod = explode('-', $period);
			
				$filterMonthOfYear = array_search($arrPeriod[0], $config['months'])+1;
				$filterYear = $arrPeriod[1];
			}
			if ($radius > 0) {
				$url = "http://www.hardloopkalender.nl/rss-loopagenda-postcode.xml?postcode=$pc&straal=$radius&aantal=$count";
			} else {
				$url = "http://www.hardloopkalender.nl/rss_maand_jaar.xml?maand=".$filterMonthOfYear."&jaar=".$filterYear;
			}
			
			$tuCurl = curl_init();
			curl_setopt($tuCurl, CURLOPT_URL, $url);
			curl_setopt($tuCurl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($tuCurl, CURLOPT_HEADER, 0);
			$tuData = curl_exec($tuCurl); 
			if(curl_errno($tuCurl)){
				echo 'Curl error: ' . curl_error($tuCurl);
			}
			curl_close($tuCurl);
		
			//$tuData = preg_replace('/&[^; ]{0,6}.?/e', "((substr('\\0',-1) == ';') ? '\\0' : '&amp;'.substr('\\0',1))", $tuData);
			$tuData = html_entity_decode($tuData, ENT_QUOTES, "utf-8");
			$rss = simplexml_load_string($tuData);
			if ($rss) {
				$i=0;
				$items = $rss->channel->item;
				foreach($items as $item) {
					if ($i >= $count)
						break;
						

					$arrLink = explode("/", (string)$item -> link);
					$eventid = $arrLink[count($arrLink)-2];
					if (is_numeric($eventid)) {
					
						$rs[$i]['link'] = (string)$item -> link;
						$rs[$i]['name'] = $arrLink[count($arrLink)-1];
						$rs[$i]['eventid'] = $eventid;
						$rs[$i]['id'] = $i;
						$rs[$i]['title'] = (string)$item -> title;
						$arrTitle = explode(",", $rs[$i]['title']);
						if (count($arrTitle) > 2) {
							$rs[$i]['displaydate']	= preg_replace('/^\s+|\s+$/', '', $arrTitle[0]);
							$tmpDate 	= str_ireplace("okt", "oct", $rs[$i]['displaydate']);
							if (($arrDate = date_parse ( $tmpDate)) !== false) {
								$rs[$i]['date'] = $arrDate['day'] .'/'. $arrDate['month'] .'/'. $arrDate['year'];
								$rs[$i]['event_date'] = $arrDate['year'] .'-'. $arrDate['month'] .'-'. $arrDate['day'];
							}
							$rs[$i]['name']	= preg_replace('/^\s+|\s+$/', '', $arrTitle[1]);				
							
							$cityRegion = explode(" ", preg_replace('/^\s+|\s+$/', '', $arrTitle[2]));
							
							if (strstr($cityRegion[count($cityRegion)-1], '(')) {
								$rs[$i]['region']	= preg_replace('/[()]/', "", $cityRegion[count($cityRegion)-1]);
								unset($cityRegion[count($cityRegion)-1]);
							}
							
							$rs[$i]['city']	= implode(" ", $cityRegion);
							
							
							if ((isset($filterMonthOfYear) && ($arrDate['month'] != $filterMonthOfYear)) ||
								(isset($filterYear) && ($arrDate['year'] != $filterYear))) {
								unset($rs[$i]);
								//$rs[$i]['displayname'] .= "-->".$filterYear."(".$arrDate['year']."):".$filterMonthOfYear."(".$arrDate['month'].")";
								continue;
							}
							
						}
						$rs[$i]['description'] = (string)$item -> description;
						$rs[$i]['filterMonthOfYear'] = $filterMonthOfYear;
						$rs[$i]['filterYear'] = $filterYear;
						$rs[$i]['period'] = $period;
						
						$i++;
					}
				}
			}
			//print_r($rs);exit;
			return $rs;
		}
		
		function nullVal($val) {
			if (!isset($val) || $val == "") {
				return null;
			}
			return $val;
		}
		
		function outputJSON($str){
			ob_get_clean();
			header('Content-type: application/json');
			echo $str;
		}
		
		function handleListRequest($pc, $radius, $period=false, $distances, $aantal, $outputResult=true, $useDb=true) {
			global $config;
			
			//if ($useDb)
			$rs = $this -> getDbRunningEventsByRadius($pc, $radius, $period, $distances, $aantal);

			if ($outputResult)
				$this -> outputJSON(json_encode($rs));

		}
		
		function handleDetailsRequest($eventid, $outputResult=true) {
			global $config;

			if ($config['usecache'] && ($details = $this -> getCachedEvent($eventid)) !== false) {
				$details = json_decode($details);
				if (is_object($details)){
					$rs[0] = $details;
				}
			} else {
				$details = $this -> getEventDetails($eventid, $row['name']);
				if (is_array($details)) {
					$this -> saveEvent($eventid, false, $details);
				}
			}
			if ($outputResult)
				$this -> outputJSON(json_encode(array($details)));

		}
		
		function getLatLng($address, $pc=false) {

			if ($pc) {
				$url = "http://maps.googleapis.com/maps/api/geocode/json?address=Nederland&components=postal_code:$pc";
			} else {
				$url = "http://maps.googleapis.com/maps/api/geocode/json?address=".urlencode($address)."&sensor=false";
			}
			
			
			// Make the HTTP request
			$data = file_get_contents($url);
			// Parse the json response
			$jsondata = json_decode($data,true);
			// If the json data is invalid, return empty array
			if ($jsondata["status"] != "OK") return array();

			$LatLng = array(
				'lat' => $jsondata["results"][0]["geometry"]["location"]["lat"],
				'lng' => $jsondata["results"][0]["geometry"]["location"]["lng"],
			);

			return $LatLng;
		}
		
		function getDistance($lat1, $lon1, $lat2, $lon2, $unit="K") {
			$theta = $lon1 - $lon2;
			$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
			$dist = acos($dist);
			$dist = rad2deg($dist);
			$miles = $dist * 60 * 1.1515;
			$unit = strtoupper($unit);
			if ($unit == "K") {
				return ($miles * 1.609344);
			} else if ($unit == "N") {
				return ($miles * 0.8684);
			} else {
				return $miles;
			}
		}
		
		function collectEvents() {
			global $config;
			set_time_limit(0);
			$arrPC = array(
							'9746', // Groningen
							'8608', // Sneek
							'7941', // Meppel
							'7828', // Emmen
							'3841', // Harderwijk
							'7336', // Apeldoorn
							'7471', // Goor
							'7009', // Doetinchemn
							'6546', // Nijmegen
							'5658', // Eindhoven
							'6045', // Roermond
							'6229', // Maastricht
							'4839', // Breda
							'4465', // Goes
							'3112', // Vlaardingen
							'3585', // Utrecht
							'1094', // Amsterdam
							'1742' // Schagen
							);
			$radius =30;
			$period = false;
			$aantal = 100;
			$i=0;
			foreach($arrPC as $pc) {
				if ($i > 1000) {
				
					$rs = $this -> readRSSFeed($pc, $radius, $period, $aantal);
				//if ($outputResult)
				//	$this -> outputJSON(json_encode($rs));
				//return;
				
					$counts[$pc] = "-";
					if (is_array($rs)) {
						$counts[$pc] = count($rs);
						foreach($rs as $row) {
						
							$this -> saveEvent($row['eventid'], false, $row);
							/*
							$details = $this -> getEventDetails($row['event_pk'], $row['eventid'], $row['name']);
							if (is_array($details)) {
								$rs[$idx] = array_merge($rs[$idx], $details);
								$this -> saveEvent($row['eventid'], $rs[$idx]);
							}					
							if ($config['usecache'] && ($details = $this -> getCachedEvent($row['eventid'])) !== false) {
								$details = json_decode($details);
								if (is_object($details)){
									$rs[$idx] = $details;
								}
							} else {
								$details = $this -> getEventDetails($row['event_pk'], $row['eventid'], $row['name']);
								if (is_array($details)) {
									$rs[$idx] = array_merge($rs[$idx], $details);
									$this -> saveEvent($row['eventid'], $rs[$idx]);
								}
							}
							*/
							
						}
					}
//log_debug("handleListRequest($pc, $radius, $period, $aantal):".print_r($rs,true));	
					//$this -> saveEventList($pc, $radius, json_encode($rs));				
					//$this -> handleListRequest($pc, $radius, $period, $aantal, false);
				}
log_debug("collectEvents:".print_r($counts, true));	
				$i++;
				
			}
			
			$years = array(date("Y"), date("Y")+1);
			foreach($years as $year) {
				foreach ($config['months'] as $month) {
					if (intval($year.$month) >= date("Ym")) {
						foreach($config['states'] as $state) {
							$events = $this -> readEventsUitslagenPuntNL($year, $month, $state);
log_debug("readEventsUitslagenPuntNL events $year, $month, $state:".print_r($events,true));
							if (is_array($events)) {
								foreach($events as $name => $details) {
									$filter['name'] = $name;
									//$event = $this -> getDbRunningEvents(false, " AND event_date='".$details['event_date']."' AND levenshtein_ratio('".$name."', name)>75", " order by levenshtein_ratio('".$name."', name) desc ", ' limit 1');
									$event = $this -> getDbRunningEvents(false, " AND event_date='".$details['event_date']."' AND city like '".$details['city']."'");
									log_debug("FOUND:".count($event));
									$eventid = false;
									if (count($event)>0) {
										$details['event_pk'] = $event[0]['id'];
										$eventid = $event[0]['eventid'];
									} else {
										$detail['title'] = $name;
									}
									
									$this -> saveEvent(false, $details['event_pk'], $details);
									log_debug("saveEvent:". print_r($details,true));
									
								} 
							}
						}
					}
				}
			}
		}
	}
/*		
select levenshtein_ratio('Winterloop', name) pnts, event_id, name 
from running_event
where levenshtein_ratio('Winterloop', name)>75
order by pnts desc
limit 1		
*/	
	
	$hk = new hk();
log_debug("Request:".print_r($_REQUEST,true));
	if ($_REQUEST["pc"]) {
		$pc = $_REQUEST["pc"];
		$period = $_REQUEST["period"];
		
		if ($_REQUEST["radius"]) {
			$radius = $_REQUEST["radius"];
		} else {
			$radius = 0;
		}
		if ($_REQUEST["aantal"]) {
			$aantal = $_REQUEST["aantal"];
		} else {
			$aantal = 5;
		}
		$distances = false;
		if ($_REQUEST["distances"] && $_REQUEST["distances"] != 'all') {
			$distances = explode(',', $_REQUEST["distances"]);
		}
		//$distances = array(11000, 15000);
		$hk -> handleListRequest($pc, $radius, $period, $distances, $aantal);
		
	} else if ($_REQUEST["eventid"]) {
		$hk -> handleDetailsRequest($_REQUEST["eventid"]);
	} else if ($_REQUEST["cacheevents"]) {
		// todo: cache 
	} else if ($_REQUEST["test"]) {
		$hk -> collectEvents();
		//$hk -> getDbRunningEvents(array('event_id' => '1388148722'));

		//$hk -> getEventDetails(1418120585 , 'Marathon-Rotterdam');
		//print_r($hk -> getEventDetails(1391770010 , 'snertloop'));
		/*
		$markers = $hk -> getDbMarkers(array('city' => 'Haarlem'));
		$lat1 = $markers[0]['lat'];
		$lon1 = $markers[0]['lng'];
		$markers = $hk -> getDbMarkers(array('city' => 'Amsterdam'));
		$lat2 = $markers[0]['lat'];
		$lon2 = $markers[0]['lng'];
		//print_r($markers);
		log_debug("Distance($lat1,$lon1)-($lat2,$lon2):".$hk -> distance($lat1, $lon1, $lat2, $lon2));
		*/
		
		exit("done");
	} else {
		echo "Nothing to do";
	}
	
	/*
	- db aanleggen voor opslag
	- service: sync rss met db
		- evenementen ophalen uit rss feed hardloopkalender
			
		- evenementen opslaan in db
		- datum laatste sync
		
		
		
		
		tabellen
		- sync
			- datum
		- evenementen
			- id
			- postcode (overnemen vanuit rss overzicht)
			- plaats (overnemen vanuit rss overzicht)
			- titel (overnemen vanuit rss overzicht)
			- datum
			- afstanden
			- tijden
			
			<img src="http://maps.googleapis.com/maps/api/staticmap?center=51.356882,6.162128&amp;
			zoom=13&amp;size=300x200&amp;maptype=roadmap&amp;sensor=false&amp;markers=icon:http://www.uitslagen.nl/css/marker.png%7C51.356882,6.162128" style="border:solid 1px #999999">
	*/	
	
		
		


