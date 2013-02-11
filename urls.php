<?php
## Define valid URL paths here
$valid_paths = array(
  // Home page
  '^/$' => array('home', 'home'),
  // Examples
  '^/about/$' => array('about', 'about'),
  '^/(?<year>\d{4})/(?<month>\w{1,2})/(?<slug>[0-9A-Za-z-]+)/$' => array('blog', 'post'),
  '^/(?<year>\d{4})/(?<month>\w{1,2})/$' => array('blog', 'blog'),
  '^/(?<year>\d{4})/$' => array('blog', 'blog'),
);