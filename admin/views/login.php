<?php
if (!$app->checkSession()) {

  if (isset($_POST['username'])) {

    $_POST = sanitize($_POST);

    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $password = isset($_POST['password']) ? sha1($_POST['password']) : null;

    $userquery = R::getRow( 'SELECT * FROM users WHERE username = "' . $username . '" AND password = "' . $password . '" AND `group` = "superadmin"' );

      if ($userquery) {

        // Login successful: Create a session and redirect to the admin homepage
        $_SESSION['user']   = $userquery['username'];
        $_SESSION['userid'] = $userquery['id'];
        $_SESSION['fname']  = $userquery['firstname'];
        $_SESSION['lname']  = $userquery['lastname'];
        $_SESSION['mail']   = $userquery['email'];
        $_SESSION['grp']    = $userquery['group'];
        $_SESSION['region'] = (isset($_POST['region'])) ? $_POST['region'] : null;

        // Header redirect to prevent form resubmission
        header( "Location: /admin/index.php" );

      } else {

        // Login failed: display an error message to the user
        $dict['error'] = "<h5><small>Incorrect credentials. Please try again...</small></h5>";
        echo $twig->render('login.html', $dict);

      }

  } else {

    // User has not posted the login form yet: display the form
    echo $twig->render('login.html');

  }
} else {

  echo $twig->render('admin.html');

}