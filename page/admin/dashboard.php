<?php
// Koneksi ke database
include '../../convig/Database.php';

// === PROSES TAMBAH DATA USER ===
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone_number = $_POST['phone_number'];
    $status = $_POST['status'];

    // Query insert ke tabel users
    $sql = "INSERT INTO users (username, password, phone_number, status, created_at)
        VALUES ('$username', '$password', '$phone_number', '$status', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('✅ User berhasil ditambahkan!'); window.location='../../page/admin/dashboard.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - VansStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
    padding: 20px;
    text-align: center;
    letter-spacing: 0.5px;
}

main {
    padding: 30px;
    max-width: 900px;
    margin: auto;
}

h1, h2 {
    color: #333;
    margin-bottom: 15px;
}

/* --- CARD STYLE --- */
.form-container, .table-container {
    background: #ffffff;
    padding: 25px 30px;
    border-radius: 10px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

/* --- FORM STYLE --- */
label {
    display: block;
    font-size: 15px;
    font-weight: 600;
    color: #111;
    margin-top: 12px;
    margin-bottom: 5px;
    letter-spacing: 0.3px;
}

input, select {
    padding: 10px 12px;
    margin-bottom: 10px;
    width: 100%;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    background: #fff;
    transition: all 0.2s ease;
    box-sizing: border-box;
}

input:focus, select:focus {
    border-color: #1e90ff;
    outline: none;
    box-shadow: 0 0 4px rgba(30,144,255,0.3);
}

button {
    padding: 10px 20px;
    background: #1e90ff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: 0.2s ease;
    margin-top: 10px;
}

button:hover {
    background: #005bbb;
}

/* --- TABLE STYLE --- */
table {
    border-collapse: collapse;
    width: 100%;
    font-size: 14px;
}

th, td {
    border: 1px solid #ccc;
    padding: 12px;
    text-align: left;
    color: #222;
}

th {
    background-color: #1e90ff;
    color: white;
    font-weight: 600;
}

tr:hover {
    background-color: #f1f1f1;
}

/* --- RESPONSIVE --- */
@media (max-width: 600px) {
    main {
        padding: 15px;
    }
    .form-container, .table-container {
        padding: 15px;
    }
    th, td {
        font-size: 13px;
    }
}

    </style>
</head>
<body>

<header>
    <h1>Dashboard Admin VansStore</h1>
</header>

<main>
    <!-- FORM TAMBAH USER -->
    <div class="form-container">
        <h2>Tambah User Baru</h2>
        <form method="POST">
            <label>Username:</label><br>
            <input type="text" name="username" required><br><br>

            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>

            <label>No HP:</label><br>
            <input type="text" name="phone_number"><br><br>

            <label>Status:</label><br>
            <select name="status">
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
            </select><br><br>

            <button type="submit">Simpan</button>
        </form>
    </div>

    <!-- TABEL USER -->
    <div class="table-container">
        <h2>Daftar Pengguna</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>No HP</th>
                <th>Status</th>
                <th>Tanggal Buat</th>
            </tr>

            <?php
            $result = $conn->query("SELECT * FROM users ORDER BY user_id DESC");
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['user_id']}</td>
                            <td>{$row['username']}</td>
                            <td>{$row['phone_number']}</td>
                            <td>{$row['status']}</td>
                            <td>{$row['created_at']}</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Belum ada user terdaftar.</td></tr>";
            }
            ?>
        </table>
    </div>
</main>

</body>
</html>
