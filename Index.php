<?php
require_once("class.DBManager.php");
require_once("class.redis.php");

if (isset($_REQUEST["add"])) {
  $todo     = $_REQUEST["todo"];
  if ($todo) {
    $dbmanager  = new CDBManager();
    $id = $dbmanager->run_sql_return_int("INSERT INTO `todos` (`todo`) VALUES ('".mysql_real_escape_string($todo)."')");
    publishToRedis(json_encode(
                      array(
                        "event" => "add", 
                        "data"  => array(
                          "id" => $id, 
                          "todo" => $todo
                        )
                      )
                    ));
  }
} elseif (isset($_REQUEST["update"])) {
  $id     = $_REQUEST["id"];
  $todo     = $_REQUEST["todo"];
  if ($todo && $id) {
    $dbmanager  = new CDBManager();
    $dbmanager->run_sql("UPDATE `todos` SET `todo` = '".mysql_real_escape_string($todo)."' WHERE id = ".$id);
    publishToRedis(json_encode(
                      array(
                        "event" => "update", 
                        "data"  => array(
                          "id" => $id, 
                          "todo" => $todo
                        )
                      )
                    ));
  } 
} elseif (isset($_REQUEST["delete"])) {
  $id     = $_REQUEST["id"];
  if ($id) {
    $dbmanager  = new CDBManager();
    $dbmanager->run_sql("DELETE FROM `todos` WHERE id = ".$id);
    publishToRedis(json_encode(
                      array(
                        "event" => "delete", 
                        "data"  => array(
                          "id" => $id
                        )
                      )
                    ));
  }
}

$html = <<<EOF

<html>
  <head>
    <title>The World's First and Finest To-Do App</title>
    <script type="text/javascript" src="jquery.js"></script>
    <script type="text/javascript">
      $(document).ready(function () {
        $("span").on('click',function () {
          var todo = prompt("Update the todo:");
          if (todo!=null && todo!="") {
            var id = $(this).data("id");
            window.location.href = '/?update=true&id=' + id + '&todo=' + todo;
          }
          return false;
        });
      });
    </script>
  </head>
  <body>
    <h1>The World's First and Finest To-Do App</h1>
    <ul>
EOF;

  $dbmanager  = new CDBManager();
  $results  = $dbmanager->run_sql_return_rs("SELECT * FROM `todos`");
  while ($record  = mysql_fetch_array($results)) {
    $html .= '<li id="item-'.$record["id"].'"><span data-id="'.$record["id"].'">'.$record["todo"].'</span> [<a href="?delete=true&id='.$record["id"].'">Delete</a>]</li>';
  }
  $html .= <<<EOF
    </ul>
    <form>Add Item: <input type="text" name="todo" /><input type="submit" value="Add" name="add" /></form>  

    

    <script src="http://localhost:8080/socket.io/socket.io.js" type="text/javascript"></script>
    

    <script type="text/javascript">
      $(document).ready(function () {           
        var Socket = io.connect('http://localhost:8080');

        console.log('Socket created.');

        Socket.on('message', function (message) {

          var data = message.data;

          console.log('Hello! Yes this is dog.');

          switch (message.event) {
            case "delete":
              $("#item-" + data.id).remove();
              break;
            case "update":
              $("span", "#item-" + data.id).text(data.todo);
              break;
            case "add":
              $("ul").append('<li id="item-' + data.id + '"><span data-id="' + data.id + '">' + data.todo + '</span> [<a href="?delete=true&id=' + data.id + '">Delete</a>]</li>');
              break;
          }       
        }); 
        
      });
    </script>
  </body>
</html>
EOF;

die($html);
?>