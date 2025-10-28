<?php
include __DIR__ . '/../../convig/Database.php';

// === TAMBAH TRANSAKSI ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $user_id = intval($_POST['user_id']);
    $product_id = intval($_POST['product_id']);
    $payment_method = intval($_POST['payment_method']);
    $voucher_id = !empty($_POST['voucher_id']) ? intval($_POST['voucher_id']) : null;

    // Ambil harga produk
    $price_result = $conn->query("SELECT price FROM GameProducts WHERE product_id = $product_id");
    $price_data = $price_result->fetch_assoc();
    $price = floatval($price_data['price'] ?? 0);

    // Ambil persentase diskon jika ada voucher
    $discount_pct = 0;
    if (!empty($voucher_id)) {
        $voucher_result = $conn->query("SELECT discount_pct FROM Vouchers WHERE voucher_id = $voucher_id");
        if ($voucher_result && $voucher_result->num_rows > 0) {
            $voucher_data = $voucher_result->fetch_assoc();
            $discount_pct = floatval($voucher_data['discount_pct']);
        }
    }

    // Hitung total harga setelah diskon (pastikan tidak negatif)
    $discount_amount = $price * ($discount_pct / 100);
    $total_price = max(0, $price - $discount_amount);

    // Simpan ke database
    $stmt = $conn->prepare("INSERT INTO TopUpTransactions (user_id, product_id, payment_method, voucher_id, total_price)
                            VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiid", $user_id, $product_id, $payment_method, $voucher_id, $total_price);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Transaksi berhasil ditambahkan dengan diskon $discount_pct%!'); window.location='../dashboard-admin-main/?page=topup_transactions';</script>";
        exit;
    } else {
        $error_msg = '❌ Gagal menyimpan transaksi: ' . $stmt->error;
    }

    $stmt->close();
}

// === EDIT TRANSAKSI ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['action'] === 'edit') {
    $id = intval($_POST['transaction_id']);
    $user_id = intval($_POST['user_id']);
    $product_id = intval($_POST['product_id']);
    $payment_method = $_POST['payment_method'] !== '' ? intval($_POST['payment_method']) : null;
    $voucher_id = $_POST['voucher_id'] !== '' ? intval($_POST['voucher_id']) : null;
    $total_price = floatval($_POST['total_price']);

    $stmt = $conn->prepare("UPDATE TopUpTransactions 
                            SET user_id=?, product_id=?, payment_method=?, voucher_id=?, total_price=? 
                            WHERE transaction_id=?");
    $stmt->bind_param("iiiidi", $user_id, $product_id, $payment_method, $voucher_id, $total_price, $id);
    if ($stmt->execute()) {
        echo "<script>alert('✅ Transaksi diperbarui!');window.location='../dashboard-admin-main/?page=topup_transactions';</script>";
        exit;
    } else $error_msg = "Gagal update: " . $stmt->error;
}

// === HAPUS ===
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM TopUpTransactions WHERE transaction_id=$id");
    echo "<script>alert('🗑️ Transaksi dihapus!');window.location='../dashboard-admin-main/?page=topup_transactions';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Transaksi TopUp - Admin</title>
<style>
body {font-family: Arial; background:#f5f6fa; margin:0;}
h2 {margin-bottom:10px;}
.container {max-width:900px; margin:30px auto; background:white; padding:20px; border-radius:10px;}
input,select,button{padding:8px;width:100%;margin:5px 0;border:1px solid #ccc;border-radius:5px;}
button{background:#1e90ff;color:white;font-weight:bold;border:none;cursor:pointer;}
button:hover{background:#006ad1;}
table{width:100%;border-collapse:collapse;margin-top:15px;}
th,td{border:1px solid #ccc;padding:8px;text-align:left;}
th{background:#1e90ff;color:white;}
a.btn{padding:5px 10px;border-radius:5px;text-decoration:none;color:white;}
a.edit{background:#ffa502;}a.delete{background:#ff4757;}
</style>
</head>
<body>

<div class="container">
<h2>Manajemen Transaksi TopUp</h2>

<?php if (!empty($error_msg)): ?>
<div style="color:red;background:#ffe6e6;padding:8px;border-radius:5px;"><?= htmlspecialchars($error_msg) ?></div>
<?php endif; ?>

<form method="POST">
<?php
$edit = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $edit = $conn->query("SELECT * FROM TopUpTransactions WHERE transaction_id=$id")->fetch_assoc();
}
$users = $conn->query("SELECT user_id, username FROM Users");
$products = $conn->query("SELECT product_id, game_name FROM GameProducts");
$methods = $conn->query("SELECT payment_method, method_name FROM PaymentMethods");
$vouchers = $conn->query("SELECT voucher_id, voucher_code FROM Vouchers");
?>
<input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
<?php if ($edit): ?><input type="hidden" name="transaction_id" value="<?= $edit['transaction_id'] ?>"><?php endif; ?>

<label>User:</label>
<select name="user_id" required>
<option value="">-- pilih --</option>
<?php while($u=$users->fetch_assoc()): ?>
<option value="<?= $u['user_id'] ?>" <?= ($edit && $edit['user_id']==$u['user_id'])?'selected':'' ?>>
<?= htmlspecialchars($u['username']) ?></option>
<?php endwhile; ?>
</select>

<label>Produk Game:</label>
<select name="product_id" required>
<option value="">-- pilih --</option>
<?php while($p=$products->fetch_assoc()): ?>
<option value="<?= $p['product_id'] ?>" <?= ($edit && $edit['product_id']==$p['product_id'])?'selected':'' ?>>
<?= htmlspecialchars($p['game_name']) ?></option>
<?php endwhile; ?>
</select>

<label>Metode Pembayaran:</label>
<select name="payment_method">
<option value="">(opsional)</option>
<?php while($m=$methods->fetch_assoc()): ?>
<option value="<?= $m['payment_method'] ?>" <?= ($edit && $edit['payment_method']==$m['payment_method'])?'selected':'' ?>>
<?= htmlspecialchars($m['method_name']) ?></option>
<?php endwhile; ?>
</select>

<label>Voucher:</label>
<select name="voucher_id">
<option value="">(opsional)</option>
<?php while($v=$vouchers->fetch_assoc()): ?>
<option value="<?= $v['voucher_id'] ?>" <?= ($edit && $edit['voucher_id']==$v['voucher_id'])?'selected':'' ?>>
<?= htmlspecialchars($v['voucher_code']) ?></option>
<?php endwhile; ?>
</select>

<label>Total Harga (Rp):</label>
<input type="number" step="0.01" name="total_price" required value="<?= htmlspecialchars($edit['total_price'] ?? '') ?>">

<button type="submit"><?= $edit ? 'Perbarui' : 'Simpan' ?></button>
</form>

<h3>Daftar Transaksi</h3>
<table>
<tr>
<th>ID</th><th>User</th><th>Game</th><th>Metode</th><th>Voucher</th><th>Total</th><th>Tanggal</th><th>Aksi</th>
</tr>
<?php
$result = $conn->query("
    SELECT t.transaction_id, u.username, g.game_name, p.method_name,
           v.voucher_code, t.total_price, t.created_at
    FROM TopUpTransactions t
    LEFT JOIN Users u ON t.user_id=u.user_id
    LEFT JOIN GameProducts g ON t.product_id=g.product_id
    LEFT JOIN PaymentMethods p ON t.payment_method=p.payment_method
    LEFT JOIN Vouchers v ON t.voucher_id=v.voucher_id
    ORDER BY t.transaction_id DESC
");

if($result->num_rows>0){
    while($r=$result->fetch_assoc()){
        echo "<tr>
        <td>{$r['transaction_id']}</td>
        <td>{$r['username']}</td>
        <td>{$r['game_name']}</td>
        <td>".($r['method_name']??'-')."</td>
        <td>".($r['voucher_code']??'-')."</td>
        <td>Rp ".number_format($r['total_price'],0,',','.')."</td>
        <td>{$r['created_at']}</td>
        <td>
            <a href='?edit={$r['transaction_id']}' class='btn edit'>Edit</a>
         <a href='topup_transactions.php?delete={$r['transaction_id']}' class='btn delete' onclick="return confirm('Hapus transaksi ini?')">Hapus</a>


        </tr>";
    }
}else echo "<tr><td colspan='8' align='center'>Belum ada transaksi</td></tr>";
?>
</table>
</div>
</body>
</html>
