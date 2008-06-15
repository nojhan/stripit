<?php

// source : http://devzone.zend.com/article/3208-Using-Zend_Feed-to-Merge-Multiple-RSS-Feeds

require_once 'Zend/Feed.php';

function loadFeed ($url) {
	try {
			$feed = Zend_Feed::import($url);
		} catch (Zend_Feed_Exception $e) {
			// feed import failed
			return null;
	}
	return $feed;    
}

// Feed → array
function getEntriesAsArray ($feed) {
		$entries = array();
		foreach ($feed as $entry) {    
			$entries[] = array (
				'title' => $entry->title(),
				'link' => $entry->link(),
				'guid' => $entry->guid(),
				'lastUpdate' =>strtotime($entry->pubDate()),
				'description' => $entry->description(),
				'pubDate' => $entry->pubDate(),
			);
			// TODO ajouter les champs qui manquent
			// TODO vérifier que les deux RSS n'aient pas de champs différents
		}
	return $entries;
}

// sorting operator
function cmpEntries ($a , $b) {
	$a_time = $a['lastUpdate'];
	$b_time = $b['lastUpdate'];
	if ($a_time == $b_time) {
		return 0;
	}
	return ($a_time > $b_time) ? -1 : 1;
}





// Feed for merge
$merged_feed = array (
	'title'     => 'Test merge feed',
	'link'         => 'http://localhost/~nojhan/feed_merge.php',
	'charset'   => 'UTF-8', 
	'entries'     => array (),
);


$feed1 = loadFeed( "http://www.nojhan.net/geekscottes/rss.php?limit=10" );
$feed2 = loadFeed( "http://www.nojhan.net/geekscottes/forum/extern.php?action=new&fid=5&type=rss" );

$merged_feed['entries'] = array_merge (
	getEntriesAsArray ($feed1), 
	getEntriesAsArray ($feed2)
);

usort ($merged_feed['entries'], 'cmpEntries');

// 	create an object
$rssFeedFromArray = Zend_Feed::importArray($merged_feed, 'rss');

// outut
$rssFeedFromArray->send();

?>
