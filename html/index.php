<?php
$host = 'inventory_mysql';
$db   = 'inventory_db';
$user = 'root';
$pass = 'rootpassword';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Kết nối CSDL thất bại: " . $conn->connect_error);
}

// Xử lý XÓA sản phẩm
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: index.php");
    exit();
}

// Xử lý THÊM sản phẩm mới
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $code = $conn->real_escape_string($_POST['product_code']);
    $name = $conn->real_escape_string($_POST['product_name']);
    $qty  = intval($_POST['quantity']);
    $price= floatval($_POST['price']);

    if (!empty($code) && !empty($name)) {
        $conn->query("INSERT INTO products (product_code, product_name, quantity, price) VALUES ('$code', '$name', $qty, $price)");
    }
    header("Location: index.php");
    exit();
}

// Xử lý CẬP NHẬT TỒN KHO (Nhập / Xuất kho)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_stock'])) {
    $id     = intval($_POST['product_id']);
    $change = intval($_POST['stock_change']); // Số dương là nhập, số âm là xuất
    $conn->query("UPDATE products SET quantity = GREATEST(0, quantity + ($change)) WHERE id = $id");
    header("Location: index.php");
    exit();
}

$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hệ thống Quản lý Kho hàng</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 30px; background-color: #f4f6f9; color: #333; }
        h2, h3 { color: #2c3e50; }
        .card { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        form { display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
        input[type="text"], input[type="number"] { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        button, .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; text-decoration: none; font-size: 14px; }
        .btn-add { background-color: #28a745; }
        .btn-in { background-color: #17a2b8; }
        .btn-out { background-color: #ffc107; color: #000; }
        .btn-del { background-color: #dc3545; }
        table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background-color: #007bff; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .action-group { display: flex; gap: 5px; }
    </style>
</head>
<body>

    <h2>📦 Hệ Thống Quản Lý Kho Hàng (Inventory System)</h2>

    <!-- FORM THÊM SẢN PHẨM MỚI -->
    <div class="card">
        <h3>➕ Thêm Sản Phẩm Mới</h3>
        <form method="POST">
            <input type="text" name="product_code" placeholder="Mã SP (ví dụ: SP005)" required>
            <input type="text" name="product_name" placeholder="Tên sản phẩm" required>
            <input type="number" name="quantity" placeholder="Số lượng ban đầu" min="0" required>
            <input type="number" name="price" placeholder="Giá bán (VNĐ)" step="1000" required>
            <button type="submit" name="add_product" class="btn btn-add">Thêm vào kho</button>
        </form>
    </div>

    <!-- BẢNG DANH SÁCH & NHẬP / XUẤT / XÓA -->
    <div class="card">
        <h3>📋 Danh Sách Sản Phẩm Trong Kho</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã SP</th>
                    <th>Tên Sản Phẩm</th>
                    <th>Số Lượng Tồn</th>
                    <th>Giá Bán</th>
                    <th>Nhập / Xuất Kho Quickly</th>
                    <th>Hành Động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><strong><?= htmlspecialchars($row['product_code']) ?></strong></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td>
                                <span style="font-weight:bold; color: <?= $row['quantity'] > 0 ? '#28a745' : '#dc3545' ?>;">
                                    <?= $row['quantity'] ?>
                                </span>
                            </td>
                            <td><?= number_format($row['price'], 0, ',', '.') ?> VNĐ</td>
                            <td>
                                <!-- Form Nhập / Xuất Kho nhanh -->
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <input type="number" name="stock_change" style="width: 70px;" placeholder="+/- SL" required>
                                    <button type="submit" name="update_stock" class="btn btn-in">Cập nhật</button>
                                </form>
                            </td>
                            <td>
                                <a href="index.php?delete=<?= $row['id'] ?>" class="btn btn-del" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align:center;">Chưa có sản phẩm nào trong kho. Hãy thêm sản phẩm mới ở trên!</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>
<?php $conn->close(); ?>
