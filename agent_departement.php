<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Département Dashboard - TetraVilla</title>
    <link rel="stylesheet" href="agent_departement_styles.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <img src="admin.jpeg" alt="Agent Photo" class="admin-photo">
                <h2>Agent</h2>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#budget">Budget Département</a></li>
                <li><a href="#rapport">Rapport</a></li>
                <li><a href="#demande">Demande</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <header>
                <h1>Département Dashboard</h1>
                <div class="logo">
                    <img src="maqlog.jpg" alt="TetraVilla Logo" class="logo-img">
                    <span>TetraVilla</span>
                </div>
            </header>

            <!-- Dashboard Sections -->
            <div class="dashboard-sections">
                <!-- Budget Département -->
                <section id="budget" class="dashboard-card">
                    <h3>Budget Département</h3>
                    <p>
                        <?php
                            $deptBudget = 75000; // Example value, you can replace with dynamic data
                            echo "Budget Département: $" . number_format($deptBudget, 2);
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Department Budget Overview)</div>
                </section>

                <!-- Rapport -->
                <section id="rapport" class="dashboard-card">
                    <h3>Rapport</h3>
                    <p>
                        <?php
                            $reportSummary = "The department has achieved 85% of its quarterly goals. Key projects are on track, with minor delays in resource allocation.";
                            echo $reportSummary;
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Performance Trends)</div>
                </section>

                <!-- Demande -->
                <section id="demande" class="dashboard-card">
                    <h3>Demande</h3>
                    <p>
                        <?php
                            $requests = [
                                "Request #1: Additional staffing for Q2",
                                "Request #2: Budget increase for training",
                                "Request #3: New equipment for operations"
                            ];
                            echo "<ul>";
                            foreach ($requests as $request) {
                                echo "<li>$request</li>";
                            }
                            echo "</ul>";
                        ?>
                    </p>
                    <div class="chart-placeholder">Chart Placeholder (Request Status)</div>
                </section>
            </div>
        </div>
    </div>
</body>
</html>