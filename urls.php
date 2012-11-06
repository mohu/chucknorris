<?php
$valid_paths = array(
  // Home page
  '^/$' => array('home', 'home'),
  // Examples
  '^/about$' => array('about', 'about'),
  '^/blog/(?<page>\d+)/(?<slug>[\w-]+)$' => array('blog', 'blog'),
  '^/blog/(?<slug>[\w-]+)$' => array('blog', 'post'),
);