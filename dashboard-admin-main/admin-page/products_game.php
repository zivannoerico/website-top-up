<?php
include __DIR__ . '/../../convig/Database.php';

// ===============================
// === PROSES TAMBAH & EDIT ===
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';
    $game_name = trim($_POST['game_name'] ?? '');
    $product_code = trim($_POST['product_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $currency = trim($_POST['currency'] ?? 'IDR');
    $available = intval($_POST['available'] ?? 1);

    if ($game_name === '' || $product_code === '' || $price <= 0) {
      echo "<script>alert('⚠️ Kode produk sudah digunakan oleh produk lain!'); window.history.back();</script>";
        $check->close();
    } else {

        // ==== CEK DUPLIKAT KODE PRODUK ====
        if ($action === 'add_game') {
            $check = $conn->prepare("SELECT product_id FROM gameproducts WHERE product_code = ?");
            $check->bind_param("s", $product_code);
            $check->execute();
            $check_result = $check->get_result();

            if ($check_result->num_rows > 0) {
                echo "<script>alert('⚠️ Kode produk sudah digunakan oleh produk lain!'); window.history.back();</script>";
        $check->close();
                exit;
            }
            $check->close();
        }

        if ($action === 'edit_game') {
            $product_id = intval($_POST['product_id']);
            $check = $conn->prepare("SELECT product_id FROM gameproducts WHERE product_code = ? AND product_id != ?");
            $check->bind_param("si", $product_code, $product_id);
            $check->execute();
            $check_result = $check->get_result();

            if ($check_result->num_rows > 0) {
                echo "<script>alert('⚠️ Kode produk sudah digunakan oleh produk lain!');</script>";
                $check->close();
                exit;
            }
            $check->close();
        }

        // ==== TAMBAH PRODUK BARU ====
if ($action === 'add_game') {

    // ✅ Cari ID terkecil yang belum dipakai
    $result = $conn->query("SELECT product_id FROM gameproducts ORDER BY product_id ASC");
    $next_id = 1; // Default ID awal

    while ($row = $result->fetch_assoc()) {
        if ($row['product_id'] == $next_id) {
            $next_id++;
        } else {
            break;
        }
    }

    // ✅ Matikan auto_increment sementara dan isi ID manual
    $stmt = $conn->prepare("INSERT INTO gameproducts (product_id, game_name, product_code, description, price, currency, available, created_at)
                            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issdsii", $next_id, $game_name, $product_code, $description, $price, $currency, $available);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Produk berhasil ditambahkan (ID: $next_id)!'); window.location='?page=products_game';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Gagal menambahkan produk!');</script>";
    }
    $stmt->close();
}


        // ==== EDIT PRODUK ====
        if ($action === 'edit_game') {
            $product_id = intval($_POST['product_id']);
            $stmt = $conn->prepare("UPDATE gameproducts 
                                    SET game_name=?, product_code=?, description=?, price=?, currency=?, available=? 
                                    WHERE product_id=?");
            $stmt->bind_param("sssdsii", $game_name, $product_code, $description, $price, $currency, $available, $product_id);

            if ($stmt->execute()) {
                echo "<script>alert('✅ Produk berhasil diperbarui!'); window.location='?page=products_game';</script>";
                exit;
            } else {
                echo "<script>alert('❌ Gagal memperbarui produk!');</script>";
            }
            $stmt->close();
        }
    }
}

// ===============================
// === PROSES HAPUS PRODUK ===
// ===============================
if (isset($_GET['delete_game'])) {
    $product_id = intval($_GET['delete_game']);
    $stmt = $conn->prepare("DELETE FROM gameproducts WHERE product_id=?");
    $stmt->bind_param("i", $product_id);
    if ($stmt->execute()) {
        echo "<script>alert('🗑️ Produk berhasil dihapus!'); window.location='?page=products_game';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Gagal menghapus produk!');</script>";
    }
    $stmt->close();
}
?>

<!-- ======================================================= -->
<!--                 GAME PRODUCTS SECTION                   -->
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

.edit-btn {
    background: #27ae60;
}

.delete-btn {
    background: #e74c3c;
}

.edit-btn:hover {
    background: #2ecc71;
}

.delete-btn:hover {
    background: #c0392b;
}
</style>

<div class="form-container">
    <h2><?= isset($_GET['edit_game']) ? 'Edit Produk Game' : 'Tambah Produk Game Baru' ?></h2>
    <form method="POST" action="">
        <?php
        $edit_game = null;
        if (isset($_GET['edit_game'])) {
            $edit_game_id = intval($_GET['edit_game']);
            $edit_game = $conn->query("SELECT * FROM gameproducts WHERE product_id=$edit_game_id")->fetch_assoc();
        }
        ?>
        <input type="hidden" name="action" value="<?= isset($_GET['edit_game']) ? 'edit_game' : 'add_game' ?>">
        <?php if ($edit_game): ?>
            <input type="hidden" name="product_id" value="<?= $edit_game['product_id'] ?>">
        <?php endif; ?>

        <label for="game_name">Nama Game:</label>
        <input id="game_name" type="text" name="game_name" required value="<?= htmlspecialchars($edit_game['game_name'] ?? '') ?>" placeholder="Contoh: Mobile Legends">

        <label for="product_code">Kode Produk:</label>
        <input id="product_code" type="text" name="product_code" required value="<?= htmlspecialchars($edit_game['product_code'] ?? '') ?>" placeholder="Contoh: ML001">

        <label for="description">Deskripsi:</label>
        <input id="description" type="text" name="description" value="<?= htmlspecialchars($edit_game['description'] ?? '') ?>" placeholder="Deskripsi singkat produk">

        <label for="price">Harga:</label>
        <input id="price" type="number" step="0.01" name="price" required value="<?= htmlspecialchars($edit_game['price'] ?? '') ?>" placeholder="Contoh: 15000.00">

        <label for="currency">Mata Uang:</label>
        <select id="currency" name="currency" required>
            <?php
            $currencies = ["IDR", "USD", "MYR", "SGD", "PHP"];
            $selected_currency = $edit_game['currency'] ?? 'IDR';
            foreach ($currencies as $curr) {
                $selected = ($curr == $selected_currency) ? 'selected' : '';
                echo "<option value='$curr' $selected>$curr</option>";
            }
            ?>
        </select>

        <label for="available">Status:</label>
        <select id="available" name="available">
            <option value="1" <?= (isset($edit_game['available']) && $edit_game['available']==1) ? 'selected' : '' ?>>Tersedia</option>
            <option value="0" <?= (isset($edit_game['available']) && $edit_game['available']==0) ? 'selected' : '' ?>>Tidak Tersedia</option>
        </select>

        <button type="submit"><?= isset($_GET['edit_game']) ? 'Perbarui Produk' : 'Simpan Produk' ?></button>
    </form>
</div>

<div class="table-container">
    <h2>Daftar Produk Game</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Game</th>
            <th>Kode Produk</th>
            <th>Deskripsi</th>
            <th>Harga</th>
            <th>Mata Uang</th>
            <th>Status</th>
            <th>Tanggal Buat</th>
            <th>Aksi</th>
        </tr>

        <?php
        $result_games = $conn->query("SELECT * FROM gameproducts ORDER BY product_id DESC");
        if ($result_games && $result_games->num_rows > 0) {
            while ($row = $result_games->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['product_id']}</td>
                        <td>" . htmlspecialchars($row['game_name']) . "</td>
                        <td>" . htmlspecialchars($row['product_code']) . "</td>
                        <td>" . htmlspecialchars($row['description']) . "</td>
                        <td>" . number_format($row['price'], 2) . "</td>
                        <td>{$row['currency']}</td>
                        <td>" . ($row['available'] ? '✅' : '❌') . "</td>
                        <td>{$row['created_at']}</td>
                        <td>
                            <a href='?page=products_game&edit_game={$row['product_id']}' class='edit-btn'>Edit</a>
                            <a href='?page=products_game&delete_game={$row['product_id']}' onclick=\"return confirm('Hapus produk ini?');\" class='delete-btn'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='9' style='text-align:center;'>Belum ada produk game terdaftar.</td></tr>";
        }
        ?>
    </table>
</div>
