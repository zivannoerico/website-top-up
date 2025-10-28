<?php
include __DIR__ . '/../../convig/Database.php';

// ===============================
// === PROSES TAMBAH & EDIT ===
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';
    $voucher_code = trim($_POST['voucher_code'] ?? '');
    $product_id = intval($_POST['product_id'] ?? 0);
    $discount_pct = floatval($_POST['discount_pct'] ?? 0);
    $valid_from = $_POST['valid_from'] ?? null;
    $valid_until = $_POST['valid_until'] ?? null;

    if ($voucher_code === '') {
        echo "<script>alert('⚠️ Kode voucher tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }

    // ==== CEK DUPLIKAT KODE ====
    if ($action === 'add_voucher') {
        $check = $conn->prepare("SELECT voucher_id FROM vouchers WHERE voucher_code = ?");
        $check->bind_param("s", $voucher_code);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('⚠️ Kode voucher sudah digunakan!'); window.history.back();</script>";
            exit;
        }
        $check->close();
    }

    if ($action === 'edit_voucher') {
        $voucher_id = intval($_POST['voucher_id']);
        $check = $conn->prepare("SELECT voucher_id FROM vouchers WHERE voucher_code = ? AND voucher_id != ?");
        $check->bind_param("si", $voucher_code, $voucher_id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo "<script>alert('⚠️ Kode voucher sudah digunakan!'); window.history.back();</script>";
            exit;
        }
        $check->close();
    }

    // ==== TAMBAH VOUCHER BARU ====
    if ($action === 'add_voucher') {
        $stmt = $conn->prepare("INSERT INTO vouchers (voucher_code, product_id, discount_pct, valid_from, valid_until, created_at)
                                VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("sidss", $voucher_code, $product_id, $discount_pct, $valid_from, $valid_until);
        if ($stmt->execute()) {
            echo "<script>alert('✅ Voucher berhasil ditambahkan!'); window.location='?page=vouchers';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Gagal menambahkan voucher!');</script>";
        }
        $stmt->close();
    }

    // ==== EDIT VOUCHER ====
    if ($action === 'edit_voucher') {
        $voucher_id = intval($_POST['voucher_id']);
        $stmt = $conn->prepare("UPDATE vouchers 
                                SET voucher_code=?, product_id=?, discount_pct=?, valid_from=?, valid_until=? 
                                WHERE voucher_id=?");
        $stmt->bind_param("sidssi", $voucher_code, $product_id, $discount_pct, $valid_from, $valid_until, $voucher_id);
        if ($stmt->execute()) {
            echo "<script>alert('✅ Voucher berhasil diperbarui!'); window.location='?page=vouchers';</script>";
            exit;
        } else {
            echo "<script>alert('❌ Gagal memperbarui voucher!');</script>";
        }
        $stmt->close();
    }
}

// ===============================
// === PROSES HAPUS VOUCHER ===
// ===============================
if (isset($_GET['delete_voucher'])) {
    $voucher_id = intval($_GET['delete_voucher']);
    $stmt = $conn->prepare("DELETE FROM vouchers WHERE voucher_id=?");
    $stmt->bind_param("i", $voucher_id);
    if ($stmt->execute()) {
        echo "<script>alert('🗑️ Voucher berhasil dihapus!'); window.location='?page=vouchers';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Gagal menghapus voucher!');</script>";
    }
    $stmt->close();
}
?>

<!-- ======================================================= -->
<!--                     VOUCHER SECTION                     -->
<!-- ======================================================= -->

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f6f8fa;
    color: #333;
    margin: 0;
    padding: 20px;
}
h2 {
    color: #2c3e50;
    text-align: center;
    margin-bottom: 20px;
}
.form-container, .table-container {
    background: #fff;
    padding: 20px;
    margin: 20px auto;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    max-width: 900px;
}
form {
    display: flex;
    flex-direction: column;
    gap: 10px;
}
label {
    font-weight: 600;
    color: #34495e;
}
input, select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    width: 100%;
    box-sizing: border-box;
}
input:focus, select:focus {
    border-color: #3498db;
    outline: none;
}
button {
    background: #3498db;
    color: #fff;
    border: none;
    padding: 12px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: background 0.3s;
}
button:hover {
    background: #2980b9;
}
.table-container table {
    width: 100%;
    border-collapse: collapse;
}
.table-container th {
    background: #3498db;
    color: #fff;
    padding: 10px;
    text-align: center;
}
.table-container td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: center;
}
.table-container tr:nth-child(even) {
    background: #f9f9f9;
}
.edit-btn, .delete-btn {
    padding: 6px 10px;
    border-radius: 5px;
    color: white;
    font-weight: 600;
    text-decoration: none;
}
.edit-btn { background: #27ae60; }
.delete-btn { background: #e74c3c; }
.edit-btn:hover { background: #2ecc71; }
.delete-btn:hover { background: #c0392b; }
</style>

<div class="form-container">
    <h2><?= isset($_GET['edit_voucher']) ? 'Edit Voucher' : 'Tambah Voucher Baru' ?></h2>
    <form method="POST" action="">
        <?php
        $edit_voucher = null;
        if (isset($_GET['edit_voucher'])) {
            $voucher_id = intval($_GET['edit_voucher']);
            $edit_voucher = $conn->query("SELECT * FROM vouchers WHERE voucher_id=$voucher_id")->fetch_assoc();
        }

        $games = $conn->query("SELECT product_id, game_name FROM gameproducts ORDER BY game_name ASC");
        ?>

        <input type="hidden" name="action" value="<?= isset($_GET['edit_voucher']) ? 'edit_voucher' : 'add_voucher' ?>">
        <?php if ($edit_voucher): ?>
            <input type="hidden" name="voucher_id" value="<?= $edit_voucher['voucher_id'] ?>">
        <?php endif; ?>

        <label for="voucher_code">Kode Voucher:</label>
        <input id="voucher_code" type="text" name="voucher_code" required value="<?= htmlspecialchars($edit_voucher['voucher_code'] ?? '') ?>" placeholder="Contoh: DISC20">

        <label for="product_id">Game Terkait:</label>
        <select id="product_id" name="product_id" required>
            <option value="">-- Pilih Game --</option>
            <?php while ($g = $games->fetch_assoc()): ?>
                <option value="<?= $g['product_id'] ?>" <?= (isset($edit_voucher['product_id']) && $edit_voucher['product_id'] == $g['product_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($g['game_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <label for="discount_pct">Diskon (%):</label>
        <input id="discount_pct" type="number" step="0.01" min="0" max="100" name="discount_pct" required
               value="<?= htmlspecialchars($edit_voucher['discount_pct'] ?? 0) ?>" placeholder="Contoh: 20">

        <label for="valid_from">Berlaku Dari:</label>
        <input id="valid_from" type="date" name="valid_from" value="<?= htmlspecialchars($edit_voucher['valid_from'] ?? '') ?>">

        <label for="valid_until">Berlaku Sampai:</label>
        <input id="valid_until" type="date" name="valid_until" value="<?= htmlspecialchars($edit_voucher['valid_until'] ?? '') ?>">

        <button type="submit"><?= isset($_GET['edit_voucher']) ? 'Perbarui Voucher' : 'Simpan Voucher' ?></button>
    </form>
</div>

<div class="table-container">
    <h2>Daftar Voucher</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Kode Voucher</th>
            <th>Game</th>
            <th>Diskon (%)</th>
            <th>Berlaku Dari</th>
            <th>Berlaku Sampai</th>
            <th>Tanggal Buat</th>
            <th>Aksi</th>
        </tr>

        <?php
$result_vouchers = $conn->query("
    SELECT vouchers.*, gameproducts.game_name 
    FROM vouchers
    LEFT JOIN gameproducts ON vouchers.product_id = gameproducts.product_id
    ORDER BY voucher_id DESC
");


        if ($result_vouchers && $result_vouchers->num_rows > 0) {
            while ($row = $result_vouchers->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['voucher_id']}</td>
                        <td>" . htmlspecialchars($row['voucher_code']) . "</td>
                        <td>" . htmlspecialchars($row['game_name'] ?? '-') . "</td>
                        <td>{$row['discount_pct']}%</td>
                        <td>{$row['valid_from']}</td>
                        <td>{$row['valid_until']}</td>
                        <td>{$row['created_at']}</td>
                        <td>
                            <a href='?page=vouchers&edit_voucher={$row['voucher_id']}' class='edit-btn'>Edit</a>
                            <a href='?page=vouchers&delete_voucher={$row['voucher_id']}' onclick=\"return confirm('Hapus voucher ini?');\" class='delete-btn'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='8' style='text-align:center;'>Belum ada voucher terdaftar.</td></tr>";
        }
        ?>
    </table>
</div>
