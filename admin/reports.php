<?php
session_start();

require_once '../config/db.php';

$page_title = 'Reports & Analytics';

$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-30 days'));
if (isset($_GET['start_date']) && !empty($_GET['start_date'])) {
    $start_date = $_GET['start_date'];
}

if (isset($_GET['end_date']) && !empty($_GET['end_date'])) {
    $end_date = $_GET['end_date'];
}

$report_type = isset($_GET['report_type']) ? $_GET['report_type'] : 'reservations';

$report_data = [];
$chart_data = [];
$total_revenue = 0;
$total_reservations = 0;
$avg_stay_length = 0;
$popular_room = '';

try {
    // Generate report based on type
    switch ($report_type) {
        case 'reservations':
            // Get reservation statistics
            $stmt = $pdo->prepare("SELECT 
                COUNT(*) as total_count,
                SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
                FROM reservations 
                WHERE created_at BETWEEN ? AND ?");
            $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            $reservation_stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Get daily reservation counts for chart
            $stmt = $pdo->prepare("SELECT 
                DATE(created_at) as date, 
                COUNT(*) as count 
                FROM reservations 
                WHERE created_at BETWEEN ? AND ? 
                GROUP BY DATE(created_at) 
                ORDER BY date");
            $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            $daily_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for chart
            $chart_labels = [];
            $chart_values = [];
            foreach ($daily_counts as $day) {
                $chart_labels[] = date('M d', strtotime($day['date']));
                $chart_values[] = $day['count'];
            }
            
            $chart_data = [
                'labels' => $chart_labels,
                'values' => $chart_values,
                'title' => 'Daily Reservations'
            ];
            
            $report_data = $reservation_stats;
            $total_reservations = $reservation_stats['total_count'];
            break;
            
        case 'revenue':
            // Calculate revenue
            $stmt = $pdo->prepare("SELECT 
                r.id, r.check_in, r.check_out, rm.price 
                FROM reservations r 
                JOIN rooms rm ON r.room_id = rm.id 
                WHERE r.created_at BETWEEN ? AND ? 
                AND (r.status = 'confirmed' OR r.status = 'completed')");
            $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $monthly_revenue = [];
            foreach ($reservations as $res) {
                $check_in = new DateTime($res['check_in']);
                $check_out = new DateTime($res['check_out']);
                $nights = $check_out->diff($check_in)->days;
                $revenue = $nights * $res['price'];
                $total_revenue += $revenue;
                
                $month = date('M Y', strtotime($res['check_in']));
                if (!isset($monthly_revenue[$month])) {
                    $monthly_revenue[$month] = 0;
                }
                $monthly_revenue[$month] += $revenue;
            }
            
            // Format for chart
            $chart_labels = array_keys($monthly_revenue);
            $chart_values = array_values($monthly_revenue);
            
            $chart_data = [
                'labels' => $chart_labels,
                'values' => $chart_values,
                'title' => 'Monthly Revenue (MAD)'
            ];
            
            $report_data = [
                'total_revenue' => $total_revenue,
                'reservation_count' => count($reservations),
                'avg_revenue' => count($reservations) > 0 ? $total_revenue / count($reservations) : 0
            ];
            break;
            
        case 'rooms':
            // Get room occupancy statistics
            $stmt = $pdo->prepare("SELECT 
                rm.id, rm.room_number, rm.type, COUNT(r.id) as reservation_count 
                FROM rooms rm 
                LEFT JOIN reservations r ON rm.id = r.room_id AND r.created_at BETWEEN ? AND ? 
                GROUP BY rm.id 
                ORDER BY reservation_count DESC");
            $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            $room_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get room types for chart
            $stmt = $pdo->prepare("SELECT 
                rm.type, COUNT(r.id) as reservation_count 
                FROM rooms rm 
                LEFT JOIN reservations r ON rm.id = r.room_id AND r.created_at BETWEEN ? AND ? 
                GROUP BY rm.type 
                ORDER BY reservation_count DESC");
            $stmt->execute([$start_date . ' 00:00:00', $end_date . ' 23:59:59']);
            $room_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format for chart
            $chart_labels = [];
            $chart_values = [];
            foreach ($room_types as $type) {
                $chart_labels[] = $type['type'];
                $chart_values[] = $type['reservation_count'];
            }
            
            $chart_data = [
                'labels' => $chart_labels,
                'values' => $chart_values,
                'title' => 'Reservations by Room Type'
            ];
            
            $report_data = $room_stats;
            $popular_room = !empty($room_stats) ? $room_stats[0]['room_number'] . ' (' . $room_stats[0]['type'] . ')' : 'N/A';
            break;
    }
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Set alert message if any
$alert_message = '';
$alert_type = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'report_generated':
            $alert_message = 'Report has been generated successfully.';
            $alert_type = 'success';
            break;
        case 'error':
            $alert_message = 'An error occurred. Please try again.';
            $alert_type = 'error';
            break;
    }
}

// Add page-specific CSS and JS
$page_css = '<link rel="stylesheet" href="css/reports.css">';
$page_js = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

// Make sure the CSS file is directly included
echo '<style>
@import url("css/reports.css");
</style>';
// Make sure Chart.js is directly included
echo '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';
// Add jsPDF and html2canvas for PDF export
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>';

?>

<?php include 'includes/header.php'; ?>

    <main class="admin-main">
        <!-- Alert Messages -->
        <?php if ($alert_message): ?>
        <div class="alert alert-<?php echo $alert_type; ?>">
            <?php echo $alert_message; ?>
            <button class="close-btn">&times;</button>
        </div>
        <?php endif; ?>

        <div class="admin-container">
            <div class="admin-header">
                <h1>Reports & Analytics</h1>
                <p>Generate and view reports about hotel performance</p>
            </div>

            <div class="report-container">
                <!-- Report Filters -->
                <div class="report-filters">
                    <form action="" method="get" class="filter-form">
                        <div class="filter-row">
                            <div class="filter-item">
                                <label for="report_type" class="filter-label">Report Type:</label>
                                <select name="report_type" id="report_type" class="filter-select">
                                    <option value="reservations" <?php echo $report_type === 'reservations' ? 'selected' : ''; ?>>Reservations</option>
                                    <option value="revenue" <?php echo $report_type === 'revenue' ? 'selected' : ''; ?>>Revenue</option>
                                    <option value="rooms" <?php echo $report_type === 'rooms' ? 'selected' : ''; ?>>Room Occupancy</option>
                                </select>
                            </div>
                            <div class="filter-item">
                                <label for="start_date" class="filter-label">Start Date:</label>
                                <input type="date" name="start_date" id="start_date" value="<?php echo $start_date; ?>" class="date-input">
                            </div>
                            <div class="filter-item">
                                <label for="end_date" class="filter-label">End Date:</label>
                                <input type="date" name="end_date" id="end_date" value="<?php echo $end_date; ?>" class="date-input">
                            </div>
                            <div class="filter-item button-group">
                                <button type="submit" class="btn-filter">Generate Report</button>
                                <button type="button" id="exportBtn" class="btn-export">Export PDF</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Report Summary -->
                <div class="report-summary">
                    <div class="summary-cards">
                        <?php if ($report_type === 'reservations'): ?>
                            <div class="summary-card">
                                <div class="summary-icon reservations-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Total Reservations</h3>
                                    <div class="summary-value"><?php echo number_format($total_reservations); ?></div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon confirmed-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <polyline points="9 11 12 14 22 4"></polyline>
                                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Confirmed</h3>
                                    <div class="summary-value"><?php echo number_format($report_data['confirmed_count']); ?></div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon completed-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Completed</h3>
                                    <div class="summary-value"><?php echo number_format($report_data['completed_count']); ?></div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon cancelled-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="15" y1="9" x2="9" y2="15"></line>
                                        <line x1="9" y1="9" x2="15" y2="15"></line>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Cancelled</h3>
                                    <div class="summary-value"><?php echo number_format($report_data['cancelled_count']); ?></div>
                                </div>
                            </div>
                        <?php elseif ($report_type === 'revenue'): ?>
                            <div class="summary-card">
                                <div class="summary-icon revenue-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="1" x2="12" y2="23"></line>
                                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Total Revenue</h3>
                                    <div class="summary-value"><?php echo number_format($total_revenue, 2); ?> MAD</div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon bookings-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                        <line x1="16" y1="2" x2="16" y2="6"></line>
                                        <line x1="8" y1="2" x2="8" y2="6"></line>
                                        <line x1="3" y1="10" x2="21" y2="10"></line>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Reservations</h3>
                                    <div class="summary-value"><?php echo number_format($report_data['reservation_count']); ?></div>
                                </div>
                            </div>
                            <div class="summary-card">
                                <div class="summary-icon average-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M8 3v3a2 2 0 0 1-2 2H3m18 0h-3a2 2 0 0 1-2-2V3m0 18v-3a2 2 0 0 1 2-2h3M3 16h3a2 2 0 0 1 2 2v3"></path>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Avg. Revenue</h3>
                                    <div class="summary-value"><?php echo number_format($report_data['avg_revenue'], 2); ?> MAD</div>
                                </div>
                            </div>
                        <?php elseif ($report_type === 'rooms'): ?>
                            <div class="summary-card">
                                <div class="summary-icon rooms-icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                        <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                    </svg>
                                </div>
                                <div class="summary-content">
                                    <h3>Most Popular Room</h3>
                                    <div class="summary-value"><?php echo $popular_room; ?></div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Report Chart -->
                <div class="report-chart">
                    <canvas id="reportChart"></canvas>
                </div>

                <!-- Report Data Table -->
                <?php if ($report_type === 'rooms' && !empty($report_data)): ?>
                <div class="report-table">
                    <h3>Room Occupancy Details</h3>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Room Number</th>
                                    <th>Type</th>
                                    <th>Reservations</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($report_data as $room): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                                    <td><?php echo htmlspecialchars($room['type']); ?></td>
                                    <td><?php echo $room['reservation_count']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Close alert messages
        const alertCloseButtons = document.querySelectorAll('.alert .close-btn');
        alertCloseButtons.forEach(button => {
            button.addEventListener('click', function() {
                const alert = this.parentElement;
                alert.style.opacity = '0';
                setTimeout(() => {
                    alert.style.display = 'none';
                }, 300);
            });
        });

        // Initialize chart
        const ctx = document.getElementById('reportChart').getContext('2d');
        const chartLabels = <?php echo json_encode($chart_data['labels'] ?? []); ?>;
        const chartValues = <?php echo json_encode($chart_data['values'] ?? []); ?>;
        const chartTitle = <?php echo json_encode($chart_data['title'] ?? 'Report Data'); ?>;
        
        const reportChart = new Chart(ctx, {
            type: '<?php echo $report_type === 'rooms' ? 'pie' : 'bar'; ?>',
            data: {
                labels: chartLabels,
                datasets: [{
                    label: chartTitle,
                    data: chartValues,
                    backgroundColor: [
                        'rgba(0, 73, 144, 0.7)',
                        'rgba(184, 157, 92, 0.7)',
                        'rgba(0, 102, 204, 0.7)',
                        'rgba(212, 194, 142, 0.7)',
                        'rgba(40, 167, 69, 0.7)',
                        'rgba(220, 53, 69, 0.7)',
                        'rgba(255, 193, 7, 0.7)'
                    ],
                    borderColor: [
                        'rgba(0, 73, 144, 1)',
                        'rgba(184, 157, 92, 1)',
                        'rgba(0, 102, 204, 1)',
                        'rgba(212, 194, 142, 1)',
                        'rgba(40, 167, 69, 1)',
                        'rgba(220, 53, 69, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: '<?php echo $report_type === 'rooms' ? 'right' : 'top'; ?>',
                    },
                    title: {
                        display: true,
                        text: chartTitle,
                        font: {
                            size: 16
                        }
                    }
                },
                scales: {
                    <?php if ($report_type !== 'rooms'): ?>
                    y: {
                        beginAtZero: true
                    }
                    <?php endif; ?>
                }
            }
        });

        // Export PDF functionality
        document.getElementById('exportBtn').addEventListener('click', function() {
            // Get report type and date range for the filename
            const reportType = '<?php echo $report_type; ?>';
            const startDate = '<?php echo $start_date; ?>';
            const endDate = '<?php echo $end_date; ?>';
            const filename = 'hilton_' + reportType + '_report_' + startDate + '_to_' + endDate + '.pdf';
            
            // Create PDF using jsPDF
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            
            // Add title
            doc.setFontSize(18);
            doc.setTextColor(0, 73, 144); // Hilton blue
            doc.text('Hilton Hotel Report', 105, 20, { align: 'center' });
            
            // Add report type and date range
            doc.setFontSize(12);
            doc.setTextColor(0, 0, 0);
            doc.text('Report Type: ' + reportType.charAt(0).toUpperCase() + reportType.slice(1), 105, 30, { align: 'center' });
            doc.text('Period: ' + startDate + ' to ' + endDate, 105, 37, { align: 'center' });
            
            // Add current date
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            doc.setFontSize(10);
            doc.text('Generated on: ' + formattedDate, 105, 45, { align: 'center' });
            
            // Add logo
            // Note: We'll use html2canvas to capture the chart
            
            // Capture the summary cards
            html2canvas(document.querySelector('.summary-cards')).then(canvas => {
                // Add the summary data
                const summaryImgData = canvas.toDataURL('image/png');
                doc.addImage(summaryImgData, 'PNG', 20, 55, 170, 60);
                
                // Capture the chart
                html2canvas(document.querySelector('#reportChart')).then(chartCanvas => {
                    // Add the chart
                    const chartImgData = chartCanvas.toDataURL('image/png');
                    doc.addImage(chartImgData, 'PNG', 20, 125, 170, 100);
                    
                    // Add footer
                    doc.setFontSize(8);
                    doc.setTextColor(128, 128, 128);
                    doc.text('© ' + new Date().getFullYear() + ' Hilton Hotels & Resorts. All rights reserved.', 105, 280, { align: 'center' });
                    
                    // Save the PDF
                    doc.save(filename);
                });
            });
        });
    });
    </script>

<?php include 'includes/footer.php'; ?>
