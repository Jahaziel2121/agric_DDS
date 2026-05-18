<?php include 'db.php'; ?>

<form method="POST">

<select name="crop_id">
<?php
$res=$conn->query("SELECT * FROM crops");
while($r=$res->fetch_assoc()){
echo "<option value='{$r['id']}'>{$r['crop_name']}</option>";
}
?>
</select>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<select name="market_id">
<?php
$res=$conn->query("SELECT * FROM markets");
while($r=$res->fetch_assoc()){
echo "<option value='{$r['id']}'>{$r['market_name']}</option>";
}
?>
</select>

<input name="price" placeholder="Price">
<button name="add">Add</button>
</form>

<?php
if(isset($_POST['add'])){
$conn->query("INSERT INTO prices VALUES(NULL,'$_POST[crop_id]','$_POST[market_id]','$_POST[price]',CURDATE())");
}
?>
