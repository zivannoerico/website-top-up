<?php
include __DIR__ . '/../../convig/Database.php';

// ===============================
// === PROSES TAMBAH & EDIT ===
// ===============================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST['action'] ?? '';
    $method_name = trim($_POST['method_name'] ?? '');

    if ($method_name === '') {
        echo "<script>alert('⚠️ Nama metode tidak boleh kosong!'); window.history.back();</script>";
        exit;
    }

    // ==== TAMBAH METODE ====
    if ($action === 'add_method') {
        // Cek duplikat
        $check = $conn->prepare("SELECT payment_method FROM paymentmethods WHERE method_name = ?");
        $check->bind_param("s", $method_name);
        $check->execute();
        $result = $check->get_result();
        if ($result->num_rows > 0) {
            echo "<script>alert('⚠️ Nama metode sudah ada!'); window.history.back();</script>";
            exit;
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO paymentmethods (method_name) VALUES (?)");
        $stmt->bind_param("s", $method_name);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Metode pembayaran berhasil ditambahkan!'); window.location='?page=payment_methods';</script>";
        } else {
            echo "<script>alert('❌ Gagal menambahkan metode!');</script>";
        }
        $stmt->close();
    }

    // ==== EDIT METODE ====
    if ($action === 'edit_method') {
        $payment_method = intval($_POST['payment_method']);

        $stmt = $conn->prepare("UPDATE paymentmethods SET method_name=? WHERE payment_method=?");
        $stmt->bind_param("si", $method_name, $payment_method);

        if ($stmt->execute()) {
            echo "<script>alert('✅ Metode berhasil diperbarui!'); window.location='?page=payment_methods';</script>";
        } else {
            echo "<script>alert('❌ Gagal memperbarui metode!');</script>";
        }
        $stmt->close();
    }
}

// ===============================
// === PROSES HAPUS ===
// ===============================
if (isset($_GET['delete_method'])) {
    $payment_method = intval($_GET['delete_method']);
    $stmt = $conn->prepare("DELETE FROM paymentmethods WHERE payment_method=?");
    $stmt->bind_param("i", $payment_method);
    if ($stmt->execute()) {
        echo "<script>alert('🗑️ Metode berhasil dihapus!'); window.location='?page=payment_methods';</script>";
        exit;
    } else {
        echo "<script>alert('❌ Gagal menghapus metode!');</script>";
    }
    $stmt->close();
}
?>

<!-- ============================== -->
<!-- === TAMPILAN DASHBOARD UI === -->
<!-- ============================== -->

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
    max-width: 700px;
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

input {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    width: 100%;
    box-sizing: border-box;
}

input:focus {
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
    <h2><?= isset($_GET['edit_method']) ? 'Edit Metode Pembayaran' : 'Tambah Metode Pembayaran Baru' ?></h2>
    <form method="POST" action="">
        <?php
        $edit_method = null;
        if (isset($_GET['edit_method'])) {
            $edit_id = intval($_GET['edit_method']);
            $edit_method = $conn->query("SELECT * FROM paymentmethods WHERE payment_method=$edit_id")->fetch_assoc();
        }
        ?>
        <input type="hidden" name="action" value="<?= isset($_GET['edit_method']) ? 'edit_method' : 'add_method' ?>">
        <?php if ($edit_method): ?>
            <input type="hidden" name="payment_method" value="<?= $edit_method['payment_method'] ?>">
        <?php endif; ?>

        <label for="method_name">Nama Metode Pembayaran:</label>
        <input id="method_name" type="text" name="method_name" required value="<?= htmlspecialchars($edit_method['method_name'] ?? '') ?>" placeholder="Contoh: Dana, OVO, GoPay, Transfer Bank">

        <button type="submit"><?= isset($_GET['edit_method']) ? 'Perbarui Metode' : 'Simpan Metode' ?></button>
    </form>
</div>

<div class="table-container">
    <h2>Daftar Metode Pembayaran</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Nama Metode</th>
            <th>Aksi</th>
        </tr>

        <?php
        $result = $conn->query("SELECT * FROM paymentmethods ORDER BY payment_method DESC");
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['payment_method']}</td>
                        <td>" . htmlspecialchars($row['method_name']) . "</td>
                        <td>
                            <a href='?page=payment_methods&edit_method={$row['payment_method']}' class='edit-btn'>Edit</a>
                            <a href='?page=payment_methods&delete_method={$row['payment_method']}' onclick=\"return confirm('Yakin hapus metode ini?');\" class='delete-btn'>Delete</a>
                        </td>
                      </tr>";
            }
        } else {
            echo "<tr><td colspan='3' style='text-align:center;'>Belum ada metode pembayaran.</td></tr>";
        }
        ?>
    </table>
</div>
