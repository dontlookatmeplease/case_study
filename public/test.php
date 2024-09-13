<?php

echo "<p>Hello world!</p>";
echo "<hr>";

$connect = mysqli_connect(
    'db',  # service name
    'cs_user', # username
    'wWLPt0DV975gUrV', # password
    'case_study_db' # db table
);

$table_name = "cs_product_list";

$query = "SELECT * FROM $table_name";

$response = mysqli_query($connect, $query);

echo "<strong>$table_name: </strong>";
while($i = mysqli_fetch_assoc($response))
{
    echo "<p>".$i['pl_title']."</p>";
    echo "<p>".$i['pl_body']."</p>";
    echo "<p>".$i['date_created']."</p>";
    echo "<hr>";
}

echo '<pre>';
var_dump('');die;