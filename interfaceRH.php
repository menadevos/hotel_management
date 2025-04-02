<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RH Dashboard - TetraVilla</title>
    <link rel="stylesheet" href="admin_finance_styles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="RHpic.jpg" alt="Admin Photo" class="admin-photo">
                <h2>RH</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#">Gérer Employés</a></li>
                <li><a href="#">Gérer Demandes</a></li>
                <li><a href="#">Se déconnecter</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>RH Dashboard</h1>
                <div class="logo">
                    <img src="maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                    <span>TetraVilla</span>
                </div>
            </header>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Budget Total -->
                <section id="budget" class="dashboard-card">
                    <h3>Budget Total</h3>
                    <p>
                        <?php
                            $totalBudget = 150000; // Example value, you can replace with dynamic data
                            echo "Total Budget: $" . number_format($totalBudget, 2);
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Budget Overview)</div>
                </section>

                <!-- Distribution de Monnaie -->
                <section id="distribution" class="dashboard-card">
                    <h3>Distribution de Monnaie</h3>
                    <p>
                        <?php
                            $distribution = [
                                "Marketing" => 50000,
                                "Operations" => 70000,
                                "R&D" => 30000
                            ];
                            echo "<ul>";
                            foreach ($distribution as $category => $amount) {
                                echo "<li>$category: $" . number_format($amount, 2) . "</li>";
                            }
                            echo "</ul>";
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Distribution Pie Chart)</div>
                </section>

                <!-- Rapport -->
                <section id="rapport" class="dashboard-card">
                    <h3>Rapport</h3>
                    <p>
                        <?php
                            $reportSummary = "The financial performance for this quarter shows a 15% increase in revenue compared to the last quarter. Expenses have been managed effectively, with a focus on optimizing operational costs.";
                            echo $reportSummary;
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Performance Trends)</div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>