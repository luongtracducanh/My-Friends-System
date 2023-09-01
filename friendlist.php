<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Friends System</title>
  <link rel="stylesheet" href="style/style.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous" />
</head>

<body class="bg-image">
  <div " class=" header">
    <h1>My Friends System</h1>
    <div class="nav">
      <ul>
        <li class="nav-link"> <a href="friendadd.php"> Add friends </a></li>
        <li class="nav-link"> <a href="logout.php"> Log out </a></li>
      </ul>
    </div>
  </div>
  <?php
  session_start();

  // Check if the user is logged in using session email and loggedIn session variable
  if (!isset($_SESSION["email"]) || !isset($_SESSION["loggedIn"])) {
    // Redirect to the login page
    header("Location: login.php");
    exit();
  }

  require_once("settings.php");

  // Query the result for profile name and number of friends
  $sql = "SELECT friend_id, profile_name, num_of_friends FROM friends WHERE friend_email = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $_SESSION["email"]);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $row = mysqli_fetch_assoc($result);

  // Get the profile name and number of friends
  $profileName = $row["profile_name"];
  $numOfFriends = $row["num_of_friends"];
  $userId = $row["friend_id"];

  // Get the list of friends of the logged in user
  $sql = "SELECT f.friend_id, f.profile_name
          FROM $table1 f JOIN $table2 mf 
          ON f.friend_id = mf.friend_id1 OR f.friend_id = mf.friend_id2
          WHERE (mf.friend_id1 = ? OR mf.friend_id2 = ?) 
          AND f.friend_id != ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "iii", $userId, $userId, $userId);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  function deleteFriend($friendId)
  {
    global $conn, $numOfFriends, $userId, $table1, $table2;

    // Delete the friend from the myfriends table
    $sql = "DELETE FROM $table2 WHERE (friend_id1 = ? AND friend_id2 = ?) OR (friend_id1 = ? AND friend_id2 = ?)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiii", $userId, $friendId, $friendId, $userId);
    mysqli_stmt_execute($stmt);

    // Update the number of friends of the logged in user
    $numOfFriends--;
    $sql = "UPDATE $table1 SET num_of_friends = ? WHERE friend_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $numOfFriends, $userId);
    mysqli_stmt_execute($stmt);

    // Get the number of friends of the friend
    $sql = "SELECT num_of_friends FROM $table1 WHERE friend_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $friendId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $numOfFriends2 = $row["num_of_friends"];

    // Update the number of friends of the friend
    $numOfFriends2--;
    $sql = "UPDATE $table1 SET num_of_friends = ? WHERE friend_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $numOfFriends2, $friendId);
    mysqli_stmt_execute($stmt);
  }

  // Unfriend button
  if (isset($_POST["unfriend"])) {
    deleteFriend($_POST["friendId"]);

    // Redirect to the friendlist page
    header("Location: friendlist.php");
    exit();
  }
  ?>
  <div class="content">
    <h2><?php echo $profileName ?>'s Friend List Page</h2>
    <h2>Total number of friends is <?php echo $numOfFriends ?></h2>
    <!-- Table displaying friends and unfriend button -->
    <div class='addfriendtable'>
      <?php
      // Check if any friends are found
      if (mysqli_num_rows($result) > 0) {
        echo "<table class='table table-bordered table-info table-striped'>";
        echo "<thead><tr><th>Profile Name</th><th>Action</th></tr></thead>";
        while ($row = mysqli_fetch_assoc($result)) {
          $friendId = $row["friend_id"];
          $friendProfileName = $row["profile_name"];
          echo "<tbody>";
          echo "<tr>";
          echo "<td>{$friendProfileName}</td>";
          echo "<td>
                  <form method='post' action='friendlist.php'>
                    <input type='hidden' name='friendId' value='{$friendId}'>
                    <input class='btn btn-outline-danger' type='submit' name='unfriend' value='Unfriend'>
                  </form>
                </td>";
          echo "</tr>";
          echo "</tbody>";
        }
        echo "</table>";
      } else {
        echo "<p class='nofriend'>No friend found.</p>";
      }

      mysqli_stmt_close($stmt);
      mysqli_close($conn);
      ?>

      <div class="friendslinks">
        <p><a class="link" href="friendadd.php"><span>Add Friends</span></a></p>
        <br>
        <p><a class="link" href="logout.php"><span>Log out</span></a></p>
      </div>
    </div>
  </div>
</body>

</html>