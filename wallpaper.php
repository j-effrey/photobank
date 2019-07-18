<?php
    include("includes/init.php");

    $tag_str = "";
    $tag_arr = array();

    if (isset($_GET['id'])) {
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $sql = "SELECT * FROM images WHERE id = :id;";
        $params = array(
            ':id' => $id
        );
        $result = exec_sql_query($db, $sql, $params);
        if ($result) {
            $target_wallpapers = $result->fetchAll();
            if ( count($target_wallpapers) > 0 ) {
                $target_wallpaper = $target_wallpapers[0];
                $action_link = "wallpaper.php?id=".$target_wallpaper['id'];
            }
            $tag_sql = "SELECT tag FROM tags WHERE id IN (SELECT tag_id FROM tag_assignment WHERE image_id = :imgID)";
            $params1 = array(
                ':imgID' => $target_wallpaper['id']
            );
            $result1 = exec_sql_query($db, $tag_sql, $params1)->fetchAll();
            if (count($result1) > 0) {
                foreach($result1 as $record) {
                    $tag_str = $tag_str.$record['tag'].", ";
                    array_push($tag_arr, $record['tag']);
                }
                $tag_str = substr($tag_str, 0, -2);
            }
        }

    }

    // NOTE - removing the seed images uploaded by the user will result in a blank image.
    // This is by design and is because seed images are generated everytime the database is
    // created, and when you remove an image, you also remove that file (.jpg) from the directory.
    // no more image means a blank image takes its place in the gallery slot.
    // So, best to test this function by uploading your own image, and then removing it
    if ( isset($_POST["remove_button"]) ) {
        $img_ID = $target_wallpaper['id'];
        $img_filename = $img_ID . "." . $target_wallpaper['img_ext'];
        unlink('uploads/images/'.$img_filename);
        $sql = "DELETE FROM images WHERE id = :img_ID";
        $params = array(
            ':img_ID' => $img_ID
        );
        exec_sql_query($db, $sql, $params);

        $remove_foreign_tags = "DELETE FROM tag_assignment WHERE image_id = :img_ID";
        exec_sql_query($db, $remove_foreign_tags, $params);

        header('Location: index.php');
    }

    if ( isset($_POST["modify_tags"]) ) {
        // first remove all tag assignments in sql db. then add according to checklist
        // only the user who uploaded the image can delete tags (by design)
        if (is_user_logged_in() && $target_wallpaper['user_id'] === $current_user[0]) {
            $first_sql = "DELETE FROM tag_assignment WHERE image_id = :imageID";
            $first_param = array(
                ":imageID" => $target_wallpaper['id']
            );
            exec_sql_query($db, $first_sql, $first_param);
         }
         // anonymous users can still add tags to any image. only the uploader can delete respective tags.
        // add each tag individually iteratively
        foreach ($_POST['tag'] as $selected) {
            $sqled_str = "\"".$selected."\"";
            $find_tag_id_sql = "SELECT id FROM tags WHERE tag = :sqlstr";
            $params3 = array(
                ':sqlstr' => trim($sqled_str, "'\"")
            );
            $all_tags = exec_sql_query($db, $find_tag_id_sql, $params3)->fetchAll();
            if (count($all_tags) > 0) {
                $our_tag_id = $all_tags[0][0];
                $insert_sql = "INSERT INTO tag_assignment (image_id, tag_id) VALUES (:iid, :tid)";
                $insert_param = array(
                    ":iid" => $target_wallpaper['id'],
                    ":tid" => $our_tag_id
                );
                exec_sql_query($db, $insert_sql, $insert_param);
            }
            header('Location: index.php');
        }
    }

    if ( isset($_POST["add_tags"]) && !empty($_POST["add_field"])) {

        try {
            // anyone can add new tags to an image
            $target_add = "'". filter_input(INPUT_POST, 'add_field', FILTER_SANITIZE_STRING). "'";
            $params4 = array(
                ':new_tag' => trim($target_add, "'")
            );
            $tag_insertion_sql = "INSERT INTO tags (tag) VALUES (:new_tag)";
            exec_sql_query($db, $tag_insertion_sql, $params4);
            $tag_id_sql = "SELECT id FROM tags WHERE tag = :new_tag";
            $all_tags = exec_sql_query($db, $tag_id_sql, $params4)->fetchAll();
            if (count($all_tags) > 0) {
                $our_tag_id = $all_tags[0][0];
                $insert_sql = "INSERT INTO tag_assignment (image_id, tag_id) VALUES (:iid, :tid)";
                $insert_param = array(
                    ":iid" => $target_wallpaper['id'],
                    ":tid" => $our_tag_id
                );
                exec_sql_query($db, $insert_sql, $insert_param);
            }
            header('Location: index.php');
        } catch (Exception $e) {
            header('Location: index.php');
        }

    }


?>

<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="stylesheet" href="styles/styles.css" type="text/css">


        <title>Image</title>
    </head>

    <body>
        <div class="container">
            <a href="index.php"><h3>Return to Gallery.</h3></a>
        </div>
        <div>
            <?php if ( isset($target_wallpaper) ) { ?>
                <img class="single_wallpaper" alt="image" src="uploads/images/<?php echo htmlspecialchars($target_wallpaper['id']).".".htmlspecialchars($target_wallpaper['img_ext']); ?>"/>
                <p class="source_text">Wallpaper provided by: <?php echo htmlspecialchars($target_wallpaper['usr_name']) ?></p>
                <p class="source_text">Tags: <?php echo htmlspecialchars($tag_str); ?></p>
                <p class="source_text">Source accredited from: <?php echo htmlspecialchars($target_wallpaper['img_src']) ?></p>
                <?php if ( is_user_logged_in() && $target_wallpaper['user_id'] === $current_user[0]) { ?>
                    <form action="<?php echo $action_link?>" method="post">
                        <button type="submit" class="r_button" name="remove_button">Remove Image</button>
                    </form>
                <?php } ?>
                <form action="<?php echo $action_link ?>" method="post" class="source_text">

                    <?php
                        $sql = "SELECT * FROM tags";
                        $records = exec_sql_query($db, $sql, array());

                        foreach($records as $record) {
                            $tagName = $record["tag"];
                            if (in_array($tagName, $tag_arr)) {
                                $tagName = str_replace("'", "", $tagName);
                                $tagName = str_replace("\"", "", $tagName);
                                echo "<input type=\"checkbox\" name=\"tag[]\" value=\"" . htmlspecialchars($tagName) . "\" " . "checked" . "> " . htmlspecialchars($tagName);
                            } else {
                                $tagName = str_replace("'", "", $tagName);
                                $tagName = str_replace("\"", "", $tagName);
                                echo "<input type=\"checkbox\" name=\"tag[]\" value=\"" . htmlspecialchars($tagName) . "\"" . "> " . htmlspecialchars($tagName);
                            }
                        }

                    ?>
                    <button type="submit" class="r_button" name="modify_tags">Modify Tags</button>
                </form>
                <form action="<?php echo $action_link ?>" method="post" class="source_text">
                    New Tag: <input type="text" id="add_field" name="add_field">
                    <button type="submit" class="r_button" name="add_tags">Add a New Tag</button>
                </form>
            <?php } else { ?>
                <p id="catch">The image has been removed or does not exist.</p>
            <?php } ?>
        </div>

    </body>
</html>
