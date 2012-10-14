<?php
if (!$app->checkSession()) {

  if (isset($_POST['username'])) {

    $_POST = sanitize($_POST);

    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $pass     = isset($_POST['password']) ? $_POST['password'] : null;

		// Default usergroups
		// 1 = Super administrator
		// 2 = Admin
		// 3 = Sales
		$userquery = R::getRow('SELECT u.*, ug.usergroup_id AS `group` FROM user u
														INNER JOIN user_usergroup ug ON u.id = ug.user_id AND ug.usergroup_id IN (1, 2 ,3)
														WHERE u.username = ? AND SHA1(CONCAT(?,u.salt)) = u.password
														LIMIT 1', array($username, $pass) );

    if ($userquery) {

        // Login successful: Create a session and redirect to the admin homepage
        $_SESSION['user']   = $userquery['username'];
        $_SESSION['userid'] = $userquery['id'];
        $_SESSION['name']   = $userquery['name'];
        $_SESSION['mail']   = $userquery['email'];
        $_SESSION['grp']    = $userquery['group'];
        $_SESSION['region'] = (isset($_POST['region'])) ? $_POST['region'] : null;

        // Header redirect to prevent form resubmission
        header( "Location: /admin/index.php" );

      } else {

        // Login failed: display an error message to the user
        $dict['error'] = "<h5><small>Incorrect credentials. Please try again...</small></h5>";
        echo $twig->render('login.twig', $dict);

      }

  } else {

    // User has not posted the login form yet: display the form
    echo $twig->render('login.twig', $dict);

  }
} else {

  echo $twig->render('admin.twig');

}