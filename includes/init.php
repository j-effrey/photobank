<?php
// vvv DO NOT MODIFY/REMOVE vvv

// check current php version to ensure it meets 2300's requirements
function check_php_version()
{
  if (version_compare(phpversion(), '7.0', '<')) {
    define(VERSION_MESSAGE, "PHP version 7.0 or higher is required for 2300. Make sure you have installed PHP 7 on your computer and have set the correct PHP path in VS Code.");
    echo VERSION_MESSAGE;
    throw VERSION_MESSAGE;
  }
}
check_php_version();

function config_php_errors()
{
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 0);
  error_reporting(E_ALL);
}
config_php_errors();

// open connection to database
function open_or_init_sqlite_db($db_filename, $init_sql_filename)
{
  if (!file_exists($db_filename)) {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (file_exists($init_sql_filename)) {
      $db_init_sql = file_get_contents($init_sql_filename);
      try {
        $result = $db->exec($db_init_sql);
        if ($result) {
          return $db;
        }
      } catch (PDOException $exception) {
        // If we had an error, then the DB did not initialize properly,
        // so let's delete it!
        unlink($db_filename);
        throw $exception;
      }
    } else {
      unlink($db_filename);
    }
  } else {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }
  return null;
}

function exec_sql_query($db, $sql, $params = array())
{
  $query = $db->prepare($sql);
  if ($query and $query->execute($params)) {
    return $query;
  }
  return null;
}
// ^^^ DO NOT MODIFY/REMOVE ^^^

// You may place any of your code here.

$db = open_or_init_sqlite_db('secure/gallery.sqlite', 'secure/init.sql');


// This code has been referenced using Prof. Harms lab and lecture notes.
// He did however say that we could use this code as long as we typed it
// ourselves. I'd like to acknowledge Prof. Harms in making this website
// possible and functional in terms of log in and upload.
// Please see my comments, as I did type up the code myself
define('SESSION_COOKIE_DURATION', 60*60*1); // 1 hour = 60 sec * 60 min * 1 hr
$session_messages = array();

// function that performs logging in given the username and password that a
// user types
function log_in($username, $password) {
  global $db;
  global $current_user;
  global $session_messages;

  // if user supplied a username and password
  if ( isset($username) && isset($password) ) {
    // find the username in the database (unique)
    $sql = "SELECT * FROM users WHERE username = :username;";
    $params = array(
      ':username' => $username
    );
    $records = exec_sql_query($db, $sql, $params)->fetchAll();
    if ($records) {
      $account = $records[0];

      // verify that the password is correct according to the has as well
      if ( password_verify($password, $account['password']) ) {
        // code used for session support
        $session = session_create_id();

        // insert into the sessions table our current session
        $sql = "INSERT INTO sessions (user_id, session) VALUES (:user_id, :session);";
        $params = array(
          ':user_id' => $account['id'],
          ':session' => $session
        );
        $result = exec_sql_query($db, $sql, $params);
        // if the result passed successfully, set a cookie for continuous login
        if ($result) {

          setcookie("session", $session, time() + SESSION_COOKIE_DURATION);

          $current_user = $account;
          return $current_user;
          // else, produce error messages related to the cause of failure
        } else {
          array_push($session_messages, "Log in failed.");
        }
      } else {
        array_push($session_messages, "Invalid username or password.");
      }
    } else {
      array_push($session_messages, "Invalid username or password.");
    }
  } else {
    array_push($session_messages, "No username or password given.");
  }
  $current_user = NULL;
  return NULL;
}

// locate the appropriate user given a user id
function find_user($user_id) {
  global $db;

  // access the database and filter out via id field
  $sql = "SELECT * FROM users WHERE id = :user_id;";
  $params = array(
    ':user_id' => $user_id
  );
  $records = exec_sql_query($db, $sql, $params)->fetchAll();
  if ($records) {
    return $records[0];
  }
  return NULL;
}

// locates the current session in the database
function find_session($session) {
  global $db;

  if (isset($session)) {
    $sql = "SELECT * FROM sessions WHERE session = :session;";
    $params = array(
      ':session' => $session
    );
    $records = exec_sql_query($db, $sql, $params)->fetchAll();
    if ($records) {
      return $records[0];
    }
  }
  return NULL;
}

// handles the login capability for sessions condition
function session_login() {
  global $db;
  global $current_user;

  // if there is already an existing cookie
  if (isset($_COOKIE["session"])) {
    $session = $_COOKIE["session"];

    $session_record = find_session($session);

    // if the record for that session is set then we can find the user to sign into
    if ( isset($session_record) ) {
      $current_user = find_user($session_record['user_id']);

      setcookie("session", $session, time() + SESSION_COOKIE_DURATION);

      return $current_user;
    }
  }
  $current_user = NULL;
  return NULL;
}

// checks to see if the user is logged in
function is_user_logged_in() {
  global $current_user;

  return ($current_user != NULL);
}

// function to log out the user
function log_out() {
  global $current_user;

  setcookie('session', '', time() - SESSION_COOKIE_DURATION);
  $current_user = NULL;
}

// check for logins on the website, or if there is an existing session already
if ( isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password']) ) {
  $username = trim( $_POST['username'] );
  $password = trim( $_POST['password'] );

  log_in($username, $password);
} else {
  session_login();
}

if ( isset($current_user) && ( isset($_GET['logout']) || isset($_POST['logout']) ) ) {
  log_out();
}


?>
