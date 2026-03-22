<?php
// Get Database credentials from Vercel Environment Variables
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT') ? getenv('DB_PORT') : 3306;

// Create connection
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- AUTOMATIC SETUP: Create Tables ---

// 1. Team Members Table Setup (Roles Removed)
$table_check = $conn->query("SHOW TABLES LIKE 'neighbours_team'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE neighbours_team (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        reg_number VARCHAR(50) NOT NULL,
        student_number VARCHAR(50) NOT NULL
    )";
    $conn->query($create_table);

    $insert_data = "INSERT INTO neighbours_team (name, reg_number, student_number) VALUES 
        ('MBABAZI JOROME', '23/U/0759', '2300700759'),
        ('BWIRE FRANCO', '23/U/07833/EVE', '2300707833'),
        ('TUHIMBISE DICKENS AMON', '23/U/18027/PS', '2300718027'),
        ('NAMYENYA JENNIFER', '23/U/15000/EVE', '2300715000'),
        ('NAMATOVU SARAH', '23/U/23731', '2300723731'),
        ('KATAMBA ESTHER NANCY', '23/U/09221/PS', '2300709221'),
        ('BATAMULIZA HADIJAH', '23/U/07467/PS', '2300707467'),
        ('LAMAJI JESSICA JOANITA', '23/U/0681', '2300700681'),
        ('ASIIMWE JOSHUA', '23/U/00674/EVE', '2300706674'),
        ('KISAKYE MARTHA', '23/U/10021/EVE', '2300710021')";
    $conn->query($insert_data);
}

// 2. Emergency Reports Table Setup
$msg_table_check = $conn->query("SHOW TABLES LIKE 'emergency_reports'");
if ($msg_table_check->num_rows == 0) {
    $create_msg_table = "CREATE TABLE emergency_reports (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reporter_name VARCHAR(100) NOT NULL,
        emergency_type VARCHAR(50) NOT NULL,
        location VARCHAR(150) NOT NULL,
        description TEXT NOT NULL,
        status VARCHAR(20) DEFAULT 'Unread',
        submit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_msg_table);
}

// --- FORM PROCESSING LOGIC ---
$alert_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_emergency'])) {
    // Sanitize inputs
    $name = $conn->real_escape_string($_POST['reporter_name']);
    $type = $conn->real_escape_string($_POST['emergency_type']);
    $location = $conn->real_escape_string($_POST['location']);
    $description = $conn->real_escape_string($_POST['description']);

    // Insert into database
    $insert_sql = "INSERT INTO emergency_reports (reporter_name, emergency_type, location, description, status) 
                   VALUES ('$name', '$type', '$location', '$description', 'Unread')";
    
    if ($conn->query($insert_sql) === TRUE) {
        $alert_message = "<div class='alert success'>🚨 <strong>URGENT ALERT SENT:</strong> " . htmlspecialchars($name) . ", your emergency has been recorded. Stay calm, help is being notified.</div>";
    } else {
        $alert_message = "<div class='alert error'>❌ System Error: " . $conn->error . "</div>";
    }
}

// Check which page the user clicked on (default is 'home')
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Neighbour's Call | Emergency Network</title>
    <style>
        /* General Styling - Red Theme */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fef2f2; margin: 0; padding: 0; color: #333; }
        
        /* Navbar Styling */
        .navbar { background-color: #7f1d1d; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; border-bottom: 3px solid #b91c1c; }
        .navbar a { float: left; display: block; color: #fee2e2; text-align: center; padding: 16px 24px; text-decoration: none; font-size: 16px; font-weight: 600; transition: 0.3s; }
        .navbar a:hover { background-color: #991b1b; color: white; }
        .navbar a.active { background-color: #b91c1c; color: white; border-bottom: 4px solid #fca5a5; }
        
        /* Container and Card Styling */
        .container { padding: 40px 20px; max-width: 1000px; margin: auto; }
        .card { background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(153, 27, 27, 0.08); margin-bottom: 30px; border-top: 5px solid #dc2626; }
        
        h1 { color: #7f1d1d; margin-top: 0; border-bottom: 2px solid #fecaca; padding-bottom: 10px; }
        h2 { color: #991b1b; }
        p { line-height: 1.8; color: #4a5568; font-size: 16px; }
        
        /* Table Styling */
        table { width: 100%; margin-top: 20px; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        th, td { border-bottom: 1px solid #fecaca; text-align: left; padding: 15px; }
        th { background-color: #dc2626; color: white; font-weight: 600; text-transform: uppercase; font-size: 14px; }
        tr:hover { background-color: #fef2f2; }
        
        /* Badges for Status */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .badge-unread { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
        .badge-read { background-color: #e2e8f0; color: #4a5568; border: 1px solid #cbd5e0; }
        .badge-pinned { background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
        .badge-type { background-color: #7f1d1d; color: white; }

        /* Form Styling */
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #7f1d1d; }
        input[type="text"], select, textarea { width: 100%; padding: 12px; border: 2px solid #fecaca; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 15px; background-color: #fffafb;}
        input[type="text"]:focus, select:focus, textarea:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2); }
        button { background-color: #dc2626; color: white; padding: 14px 28px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; text-transform: uppercase; transition: 0.3s; width: 100%; }
        button:hover { background-color: #991b1b; }
        
        /* Alert Message */
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert.success { background-color: #fef2f2; color: #991b1b; border: 2px solid #fca5a5; }
        .alert.error { background-color: #fff5f5; color: #c53030; border: 2px solid #feb2b2; }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="?page=home" class="<?php echo $page == 'home' ? 'active' : ''; ?>">🚨 Report Emergency</a>
        <a href="?page=feed" class="<?php echo $page == 'feed' ? 'active' : ''; ?>">📡 Live Emergency Feed</a>
        <a href="?page=team" class="<?php echo $page == 'team' ? 'active' : ''; ?>">🛡️ Response Team</a>
    </div>

    <div class="container">
        
        <?php if ($page == 'home'): ?>
            <div class="card">
                <h1>Neighbour's Call</h1>
                <p>Welcome to the <strong>Neighbour's Call</strong> rapid response network. If you or someone near you is in danger, experiencing a medical crisis, or witnessing a security threat, please log the incident immediately below. Our system tracks and broadcasts emergencies to local responders.</p>
            </div>

            <div class="card">
                <h2>Submit an Emergency Report</h2>
                
                <?php echo $alert_message; ?>

                <form method="POST" action="?page=home">
                    <div class="form-group">
                        <label for="reporter_name">Your Name (or Anonymous)</label>
                        <input type="text" id="reporter_name" name="reporter_name" placeholder="E.g. John Doe or 'Concerned Neighbour'" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="emergency_type">Type of Emergency</label>
                        <select id="emergency_type" name="emergency_type" required>
                            <option value="" disabled selected>Select the emergency category...</option>
                            <option value="Medical Emergency">🚑 Medical Emergency</option>
                            <option value="Fire / Hazard">🔥 Fire / Hazard</option>
                            <option value="Crime / Security">🚔 Crime / Security Incident</option>
                            <option value="Accident / Crash">💥 Accident / Crash</option>
                            <option value="Suspicious Activity">👁️ Suspicious Activity</option>
                            <option value="Other">⚠️ Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="location">Exact Location</label>
                        <input type="text" id="location" name="location" placeholder="E.g. Building 4, Floor 2, Main Campus" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Emergency Details (Please be specific)</label>
                        <textarea id="description" name="description" rows="5" placeholder="Describe what is happening, who is involved, and what help is needed..." required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_emergency">🚨 BROADCAST EMERGENCY</button>
                </form>
            </div>
        
        <?php elseif ($page == 'feed'): ?>
            <div class="card">
                <h1>Live Emergency Feed</h1>
                <p>Tracked emergencies in the local area. Dispatchers will update the status of these tickets as they are reviewed and resolved.</p>
                <table>
                    <tr>
                        <th>Status</th>
                        <th>Type & Location</th>
                        <th>Reporter</th>
                        <th>Details</th>
                        <th>Time</th>
                    </tr>
                    <?php
                    // Fetch emergencies from newest to oldest
                    $sql = "SELECT * FROM emergency_reports ORDER BY submit_date DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            // Determine badge color based on status
                            $status_class = 'badge-unread';
                            if ($row['status'] == 'Read') $status_class = 'badge-read';
                            if ($row['status'] == 'Pinned') $status_class = 'badge-pinned';

                            echo "<tr>
                                    <td><span class='badge {$status_class}'>" . $row["status"] . "</span></td>
                                    <td>
                                        <span class='badge badge-type'>" . htmlspecialchars($row["emergency_type"]) . "</span><br>
                                        <small style='color: #7f1d1d; font-weight: bold; margin-top: 5px; display: inline-block;'>📍 " . htmlspecialchars($row["location"]) . "</small>
                                    </td>
                                    <td><strong>" . htmlspecialchars($row["reporter_name"]). "</strong></td>
                                    <td>" . nl2br(htmlspecialchars($row["description"])). "</td>
                                    <td style='white-space: nowrap; font-size: 13px; color: #718096;'>" . $row["submit_date"]. "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' style='text-align: center; color: #276749; padding: 20px; background-color: #f0fff4;'>✅ All clear! No active emergencies reported in the area.</td></tr>";
                    }
                    ?>
                </table>
            </div>

        <?php elseif ($page == 'team'): ?>
            <div class="card">
                <h1>Response Team Roster</h1>
                <p>The following individuals are registered in the Neighbour's Call network system database.</p>
                <table>
                    <tr>
                        <th>#</th>
                        <th>MEMBER NAME</th>
                        <th>REGISTRATION NUMBER</th>
                        <th>STUDENT NUMBER</th>
                    </tr>
                    <?php
                    $sql = "SELECT * FROM neighbours_team";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["id"]. "</td>
                                    <td style='color: #991b1b;'><strong>" . htmlspecialchars($row["name"]). "</strong></td>
                                    <td>" . htmlspecialchars($row["reg_number"]). "</td>
                                    <td>" . htmlspecialchars($row["student_number"]). "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No team members found</td></tr>";
                    }
                    ?>
                </table>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>
