<?php

include 'db.php';
$conn->query("UPDATE insurance SET status='Approved' WHERE id=$_GET[id]");
header("Location: manage_insurance.php");

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">