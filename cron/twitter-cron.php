<?php
require_once realpath(dirname(__FILE__).'/..'). '/includes/redbean/rb.php';
require_once realpath(dirname(__FILE__).'/..'). '/includes/common/dbconnector.php';
require_once realpath(dirname(__FILE__).'/..'). '/includes/common/functions.php';

$site  = R::getCell('SELECT twitter FROM settings');

echo saveTweets($site, 'tweets');

$team = R::getAll('SELECT twitter FROM users WHERE twitter IS NOT NULL');

foreach ($team AS $team) {
    if ($team['twitter']) {
        echo saveTweets($team['twitter'], 'tweets');
    }
}

function saveTweets($screenname, $table) {
    global $link;

    $screenname = sanitize(strtolower(trim($screenname)));
    
    if (!$screenname) {
      echo '<p>Error: No screen name declared.</p>';
      return false;
    }

    $last_id = R::getCell('SELECT tid FROM '.$table.' WHERE screenname = "'. $screenname .'" ORDER BY tid DESC LIMIT 1');

    $url = 'http://api.twitter.com/1/statuses/user_timeline.xml?screenname=' . $screenname;
    if ($last_id) { $url .= '&since_id=' . $last_id; }
    $ch = curl_init($url);
    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $xml = curl_exec ($ch);
    curl_close ($ch);

    $affected = 0;
    $twelement = new SimpleXMLElement($xml);

    foreach ($twelement->status as $status) {
        $text = trim($status->text);
        $text = str_replace(array("\r\n", "\r", "\n\n", "\n", "\t"), ' ', $text);
        $time = strtotime($status->created_at);
        $tid =  strval($status->id);

        ## Create Tweet
        $tweet            = R::dispense($table); // Tweets

            $tweet->tid           = $tid;
            $tweet->setMeta("cast.tid", "string"); // Cast to BIGINT

            $tweet->screenname    = $screenname;
            $tweet->time          = $time;
            $tweet->text          = $text;
            $tweet->published     = 1;
            
        R::store($tweet);
        unset($tweet);

        $affected++;
    }

    return '<p>' . number_format($affected) . ' new tweets from <strong>'. $screenname .'</strong> saved.</p>';
}