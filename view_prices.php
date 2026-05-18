<?php 
session_start();
include 'db.php'; 
include 'back_button.php';
?>

<!DOCTYPE html>
<html>
<head>
<title>Market Prices</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f8f9fa; }
.price-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>
<?php back_button(); ?>

<div class="container mt-4">
    <div class="price-container">
        <h2 class="mb-4">📈 Market Price Information</h2>
        
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Crop</th>
                    <th>Market</th>
                    <th>Current Price (GHS)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $res=$conn->query("SELECT crops.crop_name, markets.market_name, price FROM prices 
                JOIN crops ON crops.id=prices.crop_id 
                JOIN markets ON markets.id=prices.market_id");

                if ($res && $res->num_rows > 0) {
                    while($r=$res->fetch_assoc()){
                        echo "<tr>
                            <td>{$r['crop_name']}</td>
                            <td>{$r['market_name']}</td>
                            <td><b>GHS " . number_format($r['price'], 2) . "</b></td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>No price data available yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>