<?php
include 'db.php';

$sql = "SELECT br.*, p.product_name, p.quantity AS stock
        FROM buy_requests br
        JOIN products p ON br.product_id = p.id
        ORDER BY br.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
<title>Farmer Requests</title>

<style>
body { font-family: Arial; background:#eef2f7; }
.container { width: 90%; margin:auto; }

.card {
    background:white;
    padding:15px;
    margin:15px 0;
    border-radius:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.1);
}

button {
    padding:10px;
    border:none;
    color:white;
    border-radius:6px;
    cursor:pointer;
}

.approve { background:green; }
.reject { background:red; }
</style>
</head>

<body>

<div class="container">

<h2><i class='fas fa-wheat-awn'></i> Farmer Approval Panel</h2>

<?php while($row = $result->fetch_assoc()) { ?>

<div class="card">

    <h3>Product ID: <?= $row['product_id'] ?></h3>

    <p><b>Quantity Requested:</b> <?= $row['quantity'] ?></p>
    <p><b>Status:</b> <?= $row['status'] ?></p>

    <form action="process_approval.php" method="POST">

        <input type="hidden" name="request_id" value="<?= $row['id'] ?>">
        <input type="hidden" name="product_id" value="<?= $row['product_id'] ?>">
        <input type="hidden" name="quantity" value="<?= $row['quantity'] ?>">

        <button class="approve" name="action" value="approve">Approve</button>
        <button class="reject" name="action" value="reject">Reject</button>

    </form>

</div>

<?php } ?>

</div>

</body>
</html>