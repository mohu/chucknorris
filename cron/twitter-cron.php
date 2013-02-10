<?php
## Initialise core app
require_once realpath(dirname(__FILE__).'/..') . '/' . 'admin/core/app.php';
$app = new App();

$site  = ltrim(R::getCell('SELECT twitter FROM settings'), '@');

echo saveTweets($site, 'tweets');

// Update the below query if you want to get tweets from other admin members of your site
// and include them in the twitter database table if required
$team = R::getAll('SELECT twitter FROM users WHERE twitter IS NOT NULL');

foreach ($team AS $team) {
    if ($team['twitter']) {
        echo saveTweets(ltrim($team['twitter'], '@'), 'tweets');
    }
}

function saveTweets($screenname, $table) {

  $screenname = sanitize(strtolower(trim($screenname)));

  if (!$screenname) {
    echo '<p class="alert alert-error"><strong>Error</strong>: No screen name declared.<br>Add your site\'s Twitter username to the <a href="/admin/settings/">settings page</a>.</p>';
    return false;
  }

  $last_id = R::getCell('SELECT tid FROM '.$table.' WHERE screenname = "'. $screenname .'" ORDER BY tid DESC LIMIT 1');

  $url = 'http://api.twitter.com/1/statuses/user_timeline.xml?screen_name=' . $screenname;
  if ($last_id) { $url .= '&since_id=' . $last_id; }
  $ch = curl_init($url);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
  $xml = curl_exec ($ch);
  curl_close ($ch);

  $affected = 0;
  $twelement = new SimpleXMLElement($xml);

  ## Any errors?
  $error = ($twelement->xpath('error')) ? (string) (reset($twelement->xpath('error'))) : null;

  ## Reverse XML to save oldest to newest...
  $twelement = array_reverse($twelement->xpath('status'));

  foreach ($twelement as $status) {
    // Parse twitter xml
    $text = trim($status->text);
    $text = str_replace(array("\r\n", "\r", "\n\n", "\n", "\t"), ' ', $text);
    $time = strtotime($status->created_at);
    $tid =  strval($status->id);

    ## Create Tweet
    $tweet            = R::dispense($table); // Tweets

      $tweet->tid           = $tid;
      $tweet->setMeta("cast.tid", "string"); // Cast to BIGINT

      $tweet->screenname = $screenname;
      $tweet->time = $time;
      $tweet->text = $text;
      $tweet->published = 1;

      R::store($tweet);
      unset($tweet);

      $affected++;
  }

  $message = '<p>' . number_format($affected) . ' new tweets from <strong>'. $screenname .'</strong> saved.</p>';
  if ($error) $message .= '<p class="alert alert-error"><small>' . $error . '</small></p>';

  return $message;
}