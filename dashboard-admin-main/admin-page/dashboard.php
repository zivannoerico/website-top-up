<?php
include __DIR__ . '/../../convig/Database.php';

// === PROSES TAMBAH DATA USER ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'add') {
    $username = trim($_POST['username'] ?? '');
    $password_raw = $_POST['password'] ?? '';
    $phone_number = trim($_POST['phone_number'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($username === '' || $password_raw === '' || $phone_number === '') {
        $error_msg = "⚠️ Username, password, dan nomor HP wajib diisi.";
    } elseif (!preg_match('/^[0-9]{10,13}$/', $phone_number)) {
        $error_msg = "⚠️ Nomor HP harus berupa angka (10–13 digit).";
    } else {
        $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password, phone_number, status, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssss", $username, $password_hash, $phone_number, $status);
        if ($stmt->execute()) {
            echo "<script>alert('✅ User berhasil ditambahkan!'); window.location='../dashboard-admin-main';</script>";
            exit;
        } else {
            $error_msg = "Gagal menyimpan: " . $stmt->error;
        }
        $stmt->close();
    }
}

// === PROSES EDIT ===
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $user_id = intval($_POST['user_id']);
    $username = trim($_POST['username'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $password_raw = $_POST['password'] ?? '';

    if ($username === '' || $phone_number === '') {
        $error_msg = "⚠️ Username dan nomor HP wajib diisi.";
    } else {
        if ($password_raw !== '') {
            $password_hash = password_hash($password_raw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username=?, password=?, phone_number=?, status=? WHERE user_id=?");
            $stmt->bind_param("ssssi", $username, $password_hash, $phone_number, $status, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username=?, phone_number=?, status=? WHERE user_id=?");
            $stmt->bind_param("sssi", $username, $phone_number, $status, $user_id);
        }
        if ($stmt->execute()) {
            echo "<script>alert('✅ Data user berhasil diperbarui!'); window.location='../dashboard-admin-main';</script>";
            exit;
        } else {
            $error_msg = "Gagal mengupdate data: " . $stmt->error;
        }
        $stmt->close();
    }
}

// === PROSES HAPUS ===
// === PROSES HAPUS DATA USER ===
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo "<script>alert('🗑️ User berhasil dihapus!'); window.location='../dashboard-admin-main';</script>";
        exit;
    } else {
        $error_msg = "Gagal menghapus user: " . $stmt->error;
    }
    $stmt->close();
}


// === PROSES RESET ===
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    $conn->query("DELETE FROM users");
    $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
    echo "<script>alert('🧹 Semua user dihapus dan ID direset ke 1!'); window.location='../dashboard-admin-main';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - VannMarket</title>
    <link rel="stylesheet" href="/websitetopup/assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 0;
            color: #222;
        }
        header {
            background-color: #1e90ff;
            color: white;
            padding: 18px;
            text-align: center;
            letter-spacing: 0.5px;
        }
        .dashboard-header {
            background-color: #1e90ff;
            color: white;
            text-align: center;
            padding: 35px 0;
            font-size: 30px;
            font-weight: bold;
            margin-top: 60px;
            margin-bottom: 40px;
            border-radius: 0 0 12px 12px;
        }
        main {
            padding: 20px;
            max-width: 950px;
            margin: auto;
        }
        h1, h2 {
            color: #333;
            margin-bottom: 15px;
        }
        .form-container, .table-container {
            background: #fff;
            padding: 25px 30px;
            border-radius: 10px;
            box-shadow: 0 3px 8px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        label {
            display: block;
            font-size: 15px;
            font-weight: 600;
            color: #111;
            margin-top: 12px;
            margin-bottom: 6px;
        }
        input, select {
            padding: 10px 12px;
            margin-bottom: 14px;
            width: 100%;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        input:focus, select:focus {
            border-color: #1e90ff;
            outline: none;
            box-shadow: 0 0 4px rgba(30,144,255,0.3);
        }
        button {
            padding: 10px 22px;
            background: #1e90ff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            margin-top: 8px;
        }
        button:hover { background: #005bbb; }
        .reset-btn {
            background: #ff4757;
            padding: 10px 20px;
            border-radius: 6px;
            text-decoration: none;
            color: white;
            margin-left: 10px;
        }
        .reset-btn:hover { background: #c0392b; }

        table {
            border-collapse: collapse;
            width: 100%;
            font-size: 14px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 12px;
            text-align: left;
            color: #222;
        }
        th {
            background: #1e90ff;
            color: #fff;
            font-weight: 600;
        }
        tr:hover { background: #f9f9f9; }

        .edit-btn {
            background: #ffa502;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 5px;
        }
        .edit-btn:hover { background: #e0a800; }
        .delete-btn {
            background: #ff4757;
            color: white;
            padding: 6px 10px;
            border-radius: 4px;
            text-decoration: none;
        }
        .delete-btn:hover { background: #c0392b; }

        /* Tambahan spacing agar tidak nempel di header */
        body > .dashboard-header + main {
            margin-top: 30px;
        }

        @media (max-width: 600px) {
            main { padding: 15px; }
            .form-container, .table-container { padding: 15px; }
            th, td { font-size: 13px; }
            .dashboard-header { font-size: 22px; padding: 25px 0; }
        }
    </style>
</head>
<body>

<div class="dashboard-header">Dashboard Admin VannMarket</div>

<main>
    <?php if (!empty($error_msg)): ?>
        <div style="background:#ffe6e6;color:#900;padding:12px;border-radius:8px;margin-bottom:20px;text-align:center;">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h2><?= isset($_GET['edit']) ? 'Edit User' : 'Tambah User Baru' ?></h2>
        <form method="POST" action="">
            <?php
            if (isset($_GET['edit'])) {
                $edit_id = intval($_GET['edit']);
                $edit = $conn->query("SELECT * FROM users WHERE user_id=$edit_id")->fetch_assoc();
            }
            ?>
            <input type="hidden" name="action" value="<?= isset($_GET['edit']) ? 'edit' : 'add' ?>">
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="user_id" value="<?= $edit['user_id'] ?>">
            <?php endif; ?>

            <label for="username">Username:</label>
            <input id="username" type="text" name="username" required
                   value="<?= htmlspecialchars($edit['username'] ?? '') ?>" placeholder="Masukkan username">

            <label for="password">Password <?= isset($_GET['edit']) ? '(isi jika ingin mengganti)' : '' ?>:</label>
            <input id="password" type="password" name="password" placeholder="Masukkan password baru">

            <label for="phone_number">No HP:</label>
            <input id="phone_number" type="tel" name="phone_number" pattern="[0-9]{10,13}" required
                   value="<?= htmlspecialchars($edit['phone_number'] ?? '') ?>" placeholder="Contoh: 081234567890">

            <label for="status">Status:</label>
            <select id="status" name="status">
                <option value="active" <?= (isset($edit['status']) && $edit['status']=='active') ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= (isset($edit['status']) && $edit['status']=='inactive') ? 'selected' : '' ?>>Tidak Aktif</option>
            </select>

            <button type="submit"><?= isset($_GET['edit']) ? 'Perbarui' : 'Simpan' ?></button>
            <?php if (!isset($_GET['edit'])): ?>
                <a href="?reset=true" onclick="return confirm('⚠️ Yakin ingin hapus SEMUA user dan reset ID ke 1?')" class="reset-btn">Reset ID & Hapus Semua</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-container">
        <h2>Daftar Pengguna</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>No HP</th>
                <th>Status</th>
                <th>Tanggal Buat</th>
                <th>Aksi</th>
            </tr>

            <?php
            $result = $conn->query("SELECT user_id, username, phone_number, status, created_at FROM users ORDER BY user_id DESC");
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['user_id']}</td>
                            <td>" . htmlspecialchars($row['username']) . "</td>
                            <td>" . htmlspecialchars($row['phone_number']) . "</td>
                            <td>" . htmlspecialchars($row['status']) . "</td>
                            <td>" . htmlspecialchars($row['created_at']) . "</td>
                            <td>
                                <a href='?edit={$row['user_id']}' class='edit-btn'>Edit</a>
                                <a href='?delete={$row['user_id']}' onclick=\"return confirm('Hapus user ini?');\" class='delete-btn'>Delete</a>
                            </td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>Belum ada user terdaftar.</td></tr>";
            }
            ?>
        </table>
    </div>
</main>

</body>
</html>
