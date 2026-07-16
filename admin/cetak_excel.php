<?php
include __DIR__ . '/config.php';

$filter = $_GET['filter'] ?? 'monthly';
$start = $_GET['start_date'] ?? '';
$end = $_GET['end_date'] ?? '';

// Kondisi logika sama persis dengan yang ada di pemasukan.php
$kondisi = "status = 'lunas'";
if ($filter == 'custom') $kondisi .= " AND DATE(created_at) BETWEEN '$start' AND '$end'";
elseif ($filter == 'daily') $kondisi .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)";
elseif ($filter == 'weekly') $kondisi .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)";
else $kondisi .= " AND YEAR(created_at) = YEAR(CURDATE())";

$sql = "SELECT id, nama, metode, total, created_at FROM orders WHERE $kondisi ORDER BY created_at DESC";
$res = $conn->query($sql);

header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Pemasukan.xls");
?>
<table border="1">
    <thead>
        <tr style="background-color: #2563eb; color: #fff;">
            <th>ID Invoice</th><th>Pelanggan</th><th>Metode</th><th>Waktu</th><th>Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = $res->fetch_assoc()): ?>
        <tr>
            <td style="mso-number-format:'\@';">'#INV<?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= strtoupper($row['metode']) ?></td>
            <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
            <td style="mso-number-format:'\#\,\#\#0';"><?= $row['total'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>