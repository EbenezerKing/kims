<?php
require_once '../includes/auth.php';
require_once '../config/db.php';
require_login();

$pageTitle = 'Purchase Reports';
$showBackButton = true;

// Get all companies for filter dropdown
$companies_query = "SELECT DISTINCT company_name FROM purchases ORDER BY company_name";
$companies = $conn->query($companies_query)->fetch_all(MYSQLI_ASSOC);

// Get date range if set
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');
$selected_company = isset($_GET['company']) ? $_GET['company'] : '';

// Build query with filters
$query = "SELECT DATE(submitted_at) as date,
          COUNT(*) as total_forms,
          SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_forms,
          SUM(CASE WHEN status = 'Complete' THEN 1 ELSE 0 END) as completed_forms,
          SUM(amount) as total_amount
          FROM purchases 
          WHERE submitted_at BETWEEN ? AND ?";

if (!empty($selected_company)) {
    $query .= " AND company_name = ?";
}

$query .= " GROUP BY DATE(submitted_at)
           ORDER BY DATE(submitted_at) ASC";
$stmt = $conn->prepare($query);

if (!empty($selected_company)) {
    $stmt->bind_param("sss", $start_date, $end_date, $selected_company);
} else {
    $stmt->bind_param("ss", $start_date, $end_date);
}

$stmt->execute();
$result = $stmt->get_result();
$reports = $result->fetch_all(MYSQLI_ASSOC);

// Calculate totals
$total_amount = 0;
$total_forms = 0;
$total_pending = 0;
$total_completed = 0;

foreach ($reports as $report) {
    $total_amount += $report['total_amount'];
    $total_forms += $report['total_forms'];
    $total_pending += $report['pending_forms'];
    $total_completed += $report['completed_forms'];
}

require_once '../includes/header.php';
?>

<!-- Add these in the head section -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
    @media print {
        body {
            padding: 0;
            margin: 0;
        }
        .no-print {
            display: none !important;
        }
        .print-only {
            display: block !important;
        }
        .card {
            border: none !important;
            box-shadow: none !important;
        }
        @page {
            size: landscape;
            margin: 1cm;
        }
    }
    .print-only {
        display: none;
    }
</style>

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2">Purchase Reports</h1>
            <p class="text-muted">Generate and analyze purchase form data</p>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Company</label>
                    <select class="form-select" name="company">
                        <option value="">All Companies</option>
                        <?php foreach ($companies as $company): ?>
                            <option value="<?= htmlspecialchars($company['company_name']) ?>"
                                <?= $selected_company === $company['company_name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($company['company_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-info text-white w-100">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Print Header -->
    <div class="print-only text-center mb-4">
        <h2>Purchase Forms Report</h2>
        <p>
            Period: <?= date('M d, Y', strtotime($start_date)) ?> - 
            <?= date('M d, Y', strtotime($end_date)) ?>
            <?= $selected_company ? " | Company: $selected_company" : '' ?>
        </p>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card bg-info bg-opacity-10 border-0">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-0">Total Forms</h6>
                    <h2 class="mt-2 mb-0"><?= number_format($total_forms) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card bg-warning bg-opacity-10 border-0">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-0">Pending Forms</h6>
                    <h2 class="mt-2 mb-0"><?= number_format($total_pending) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card bg-success bg-opacity-10 border-0">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-0">Completed Forms</h6>
                    <h2 class="mt-2 mb-0"><?= number_format($total_completed) ?></h2>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card bg-primary bg-opacity-10 border-0">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-0">Total Amount</h6>
                    <h2 class="mt-2 mb-0">GHC <?= number_format($total_amount, 2) ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Daily Purchase Amounts</h5>
                    <div style="height: 250px;">
                        <canvas id="amountChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Forms Status Distribution</h5>
                    <div style="width: 250px; margin: 0 auto;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="reportsTable" class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Forms</th>
                        <th>Pending</th>
                        <th>Completed</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                    <tr>
                        <td><?= date('M d, Y', strtotime($report['date'])) ?></td>
                        <td><?= number_format($report['total_forms']) ?></td>
                        <td><?= number_format($report['pending_forms']) ?></td>
                        <td><?= number_format($report['completed_forms']) ?></td>
                        <td>GHC <?= number_format($report['total_amount'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add these before closing body tag -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

<script>
// Initialize DataTable with export buttons
$(document).ready(function() {
    $('#reportsTable').DataTable({
        dom: 'Bfrtip',
        buttons: [
            {
                extend: 'excel',
                text: '<i class="bi bi-file-earmark-excel me-2"></i>Excel',
                className: 'btn btn-success btn-sm'
            },
            {
                extend: 'pdf',
                text: '<i class="bi bi-file-earmark-pdf me-2"></i>PDF',
                className: 'btn btn-danger btn-sm',
                orientation: 'landscape'
            },
            {
                extend: 'print',
                text: '<i class="bi bi-printer me-2"></i>Print',
                className: 'btn btn-primary btn-sm'
            }
        ]
    });
});

// Initialize Charts
const statusChart = new Chart(document.getElementById('statusChart'), {
    type: 'pie',
    data: {
        labels: ['Pending', 'Completed'],
        datasets: [{
            data: [<?= $total_pending ?>, <?= $total_completed ?>],
            backgroundColor: ['#ffc107', '#198754']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

const amountChart = new Chart(document.getElementById('amountChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_map(function($r) { 
            return date('M d', strtotime($r['date'])); 
        }, $reports)) ?>,
        datasets: [{
            label: 'Daily Amount (GHC)',
            data: <?= json_encode(array_map(function($r) { 
                return $r['total_amount']; 
            }, $reports)) ?>,
            borderColor: '#0dcaf0',
            tension: 0.1,
            fill: false
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    maxTicksLimit: 5
                }
            },
            x: {
                ticks: {
                    maxRotation: 45,
                    minRotation: 45
                }
            }
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>