<?php
 // INCLUDE ON EVERY TOP-LEVEL PAGE!
include("includes/init.php");

$show_all = TRUE;

// code referenced using Prof Harms lecture notes.
// see my comments for updates to his code.
if ( isset($_POST["submit_upload"]) && is_user_logged_in() ) {
  // code for uploading form
  $upload_info = $_FILES["form_image"];
  // check error status for upload
  if ($upload_info['error'] === UPLOAD_ERR_OK) {
    $basename = basename($upload_info['name']);
    $upload_ext = strtolower( pathinfo($basename, PATHINFO_EXTENSION) );
    $users_name = $current_user['username'];
    $usern = "\"".$current_user['username']."\"";
    $param1 = array(
      ':username' => trim($usern, '"')
    );

    // get the username
    $sql_to_get_id = "SELECT id FROM users WHERE username = :username";
    $result2 = exec_sql_query($db, $sql_to_get_id, $param1)->fetchAll();
    $account_id = $result2[0][0];

    // insert images with all necessary parameters
    $sql = "INSERT INTO images (user_id, usr_name, img_name, img_ext) VALUES (:usr_id, :usr_name, :img_name, :img_ext)";
    $params = array(
      ':usr_id' => $account_id,
      ':usr_name' => $users_name,
      ':img_name' => $basename,
      ':img_ext' => $upload_ext
    );

    $result = exec_sql_query($db, $sql, $params);

    $new_file_name = $db->lastInsertId("id");
    $new_file_ext = $upload_ext;

    // move the file to uploads/images/.jpg
    $new_path = "uploads/images/".$new_file_name.".".$new_file_ext;
    move_uploaded_file( $_FILES["form_image"]["tmp_name"], $new_path );
  }
}

// display images on the gallery
function display($db, $sql, $params) {
  $records = exec_sql_query($db, $sql, $params)->fetchAll();

  if (count($records) > 0) {
    foreach($records as $record) {
      $imgID = $record["id"];
      echo "<div class=\"image\">
              <a href=\"wallpaper.php?" . http_build_query( array( 'id' => $imgID ) ) . "\">
                <img src=\"uploads/images/" . htmlspecialchars($record["id"]) . "." . htmlspecialchars($record["img_ext"]) . "\" class=\"item\" alt=\"image\">
              </a>
            </div>";
    }
  } else {
    echo '<p><strong>No images here.</strong></p>';
  }
}

if (isset($_GET['go'])) {
  $target_tag = "'".filter_input(INPUT_GET, 'tags', FILTER_SANITIZE_STRING)."'";
  if ($target_tag !== '\'\'') {
    $filter_sql = "SELECT * FROM images WHERE id IN (SELECT image_id FROM tag_assignment WHERE tag_id = (SELECT id FROM tags WHERE tag = :targettag))";
    $param2 = array(
      ':targettag' => trim($target_tag, "'\"")
    );
    $show_all = FALSE;
  } else {
    $show_all = TRUE;
  }
}


?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="styles/styles.css" type="text/css">


  <title>Home</title>
</head>

<body>

  <!-- TODO: This should be your main page for your site. -->
  <?php
    if (!is_user_logged_in()) { ?>
      <header>
          <nav id="navigation">
            <form id="loginForm" action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] ); ?>" method="post" class="login enter_details">
              <label for="username">Username: </label>
              <input type="text" id="username" name="username"/>
              <label for="password">Password: </label>
              <input type="password" id="password" name="password"/>
              <button name="login" type="submit">Login</button>
            </form>
          </nav>
      </header>
  <?php
    } else { ?>
      <header>
          <nav id="navigation">
            <form id="logoutForm" action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] ); ?>" method="post" class="login enter_details">
              <button name="logout" type="submit">Logout</button>
            </form>
          </nav>
      </header>
  <?php
    }
  ?>


  <div class="container">
    <h1>Photobank.</h1>
    <h2>A place to find beautiful, high-resolution wallpapers</h2>

    <form id="search" action="index.php" method="get">
      <br>
      <label for="tag_field">Filter on tags: </label>
      <select name="tags" id="tag_field">
        <option value="">All</option>
        <?php
          $sql = "SELECT * FROM tags";
          $records = exec_sql_query($db, $sql, array());

          if (count($records) > 0) {
            foreach($records as $record) {
              $tagName = $record["tag"];
              $tagName = str_replace("'", "", $tagName);
              $tagName = str_replace("\"", "", $tagName);
              echo "<option value=\"" . htmlspecialchars($tagName) . "\">" . htmlspecialchars($tagName) . "</option>";
            }
          }
        ?>
        <!-- <option value="Animals">Animals</option>
        <option value="Artwork">Artwork</option>
        <option value="Landscape">Landscape</option>
        <option value="Space">Space</option>
        <option value="Technology">Technology</option> -->
      </select>
      <button name="go" type="submit">Go</button>
      <br><br><br>
    </form>
    <?php
        // adopted code from Harms for uploading images. Seems like the standard way to do this
        if (is_user_logged_in()) {
          ?><form id="uploadFile" action="index.php" method="post" enctype="multipart/form-data">
              <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
              <!-- changed for id name -->
              <label for="form_image">Upload Image:</label>
              <input id="form_image" type="file" name="form_image">
              <!-- need one for tags -->
              <button name="submit_upload" type="submit">Upload Image</button>
          </form>
      <?php
        }
      ?>

  </div>

  <div class="row">
    <div class="gallery">
      <?php
        if ($show_all) {
          $sql = 'SELECT * FROM images';
          display($db, $sql, array());
        } else {
          display($db, $filter_sql, $param2);
        }

      ?>
    </div>
  </div>

</body>
</html>
