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

// 1. Members Table Setup
$table_check = $conn->query("SHOW TABLES LIKE 'group_members_v2'");
if ($table_check->num_rows == 0) {
    $create_table = "CREATE TABLE group_members_v2 (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        reg_number VARCHAR(50) NOT NULL,
        student_number VARCHAR(50) NOT NULL,
        role VARCHAR(50) NOT NULL
    )";
    $conn->query($create_table);

    $insert_data = "INSERT INTO group_members_v2 (name, reg_number, student_number, role) VALUES 
        ('MBABAZI JOROME', '23/U/0759', '2300700759', 'Project Manager'),
        ('BWIRE FRANCO', '23/U/07833/EVE', '2300707833', 'DevOps Engineer'),
        ('TUHIMBISE DICKENS AMON', '23/U/18027/PS', '2300718027', 'Lead Developer'),
        ('NAMYENYA JENNIFER', '23/U/15000/EVE', '2300715000', 'UI/UX Designer'),
        ('NAMATOVU SARAH', '23/U/23731', '2300723731', 'Frontend Developer'),
        ('KATAMBA ESTHER NANCY', '23/U/09221/PS', '2300709221', 'Backend Developer'),
        ('BATAMULIZA HADIJAH', '23/U/07467/PS', '2300707467', 'Database Administrator'),
        ('LAMAJI JESSICA JOANITA', '23/U/0681', '2300700681', 'QA Tester'),
        ('ASIIMWE JOSHUA', '23/U/00674/EVE', '2300706674', 'Systems Analyst'),
        ('KISAKYE MARTHA', '23/U/10021/EVE', '2300710021', 'Technical Writer')";
    $conn->query($insert_data);
}

// 2. Contact Messages Table Setup
$msg_table_check = $conn->query("SHOW TABLES LIKE 'contact_messages'");
if ($msg_table_check->num_rows == 0) {
    $create_msg_table = "CREATE TABLE contact_messages (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        message TEXT NOT NULL,
        submit_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($create_msg_table);
}

// --- FORM PROCESSING LOGIC ---
$alert_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_contact'])) {
    // Sanitize inputs to prevent SQL errors
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $message = $conn->real_escape_string($_POST['message']);

    // Insert into database
    $insert_sql = "INSERT INTO contact_messages (name, email, message) VALUES ('$name', '$email', '$message')";
    
    if ($conn->query($insert_sql) === TRUE) {
        $alert_message = "<div class='alert'>✅ Success! <strong>" . htmlspecialchars($name) . "</strong>, your message has been securely saved to the database.</div>";
    } else {
        $alert_message = "<div class='alert' style='background-color: #fed7d7; color: #9b2c2c; border-color: #feb2b2;'>❌ Database Error: " . $conn->error . "</div>";
    }
}

// Check which page the user clicked on (default is 'home')
$page = isset($_GET['page']) ? $_GET['page'] : 'home';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jero's Group Project</title>
    <style>
        /* General Styling */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f5; margin: 0; padding: 0; color: #333; }
        
        /* Navbar Styling */
        .navbar { background-color: #1a365d; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 100; }
        .navbar a { float: left; display: block; color: #e2e8f0; text-align: center; padding: 16px 24px; text-decoration: none; font-size: 16px; font-weight: 600; transition: 0.3s; }
        .navbar a:hover { background-color: #2b6cb0; color: white; }
        .navbar a.active { background-color: #2b6cb0; color: white; border-bottom: 4px solid #f6ad55; }
        
        /* Container and Card Styling */
        .container { padding: 40px 20px; max-width: 1000px; margin: auto; }
        .card { background-color: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        
        h1 { color: #1a365d; margin-top: 0; border-bottom: 2px solid #e2e8f0; padding-bottom: 10px; }
        h2 { color: #2d3748; }
        p { line-height: 1.8; color: #4a5568; font-size: 16px; }
        ul { line-height: 1.8; color: #4a5568; }
        li { margin-bottom: 10px; }
        
        /* Table Styling */
        table { width: 100%; margin-top: 20px; border-collapse: collapse; border-radius: 8px; overflow: hidden; }
        th, td { border-bottom: 1px solid #e2e8f0; text-align: left; padding: 15px; }
        th { background-color: #2b6cb0; color: white; font-weight: 600; text-transform: uppercase; font-size: 14px; }
        tr:hover { background-color: #f7fafc; }
        .role-badge { background-color: #edf2f7; padding: 6px 12px; border-radius: 20px; font-size: 13px; color: #2b6cb0; font-weight: bold; border: 1px solid #e2e8f0; }
        
        /* Form Styling */
        .form-group { margin-bottom: 20px; }
        label { display: block; font-weight: bold; margin-bottom: 8px; color: #4a5568; }
        input[type="text"], input[type="email"], textarea { width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box; font-family: inherit; font-size: 15px; }
        input[type="text"]:focus, input[type="email"]:focus, textarea:focus { outline: none; border-color: #2b6cb0; box-shadow: 0 0 0 3px rgba(43, 108, 176, 0.2); }
        button { background-color: #2b6cb0; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 16px; font-weight: bold; transition: 0.3s; }
        button:hover { background-color: #1a365d; }
        
        /* Alert Message */
        .alert { padding: 15px; background-color: #c6f6d5; color: #22543d; border-radius: 6px; margin-bottom: 20px; border: 1px solid #9ae6b4; }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="?page=home" class="<?php echo $page == 'home' ? 'active' : ''; ?>">Home</a>
        <a href="?page=members" class="<?php echo $page == 'members' ? 'active' : ''; ?>">Group Members</a>
        <a href="?page=messages" class="<?php echo $page == 'messages' ? 'active' : ''; ?>">View Messages</a>
    </div>

    <div class="container">
        
        <?php if ($page == 'home'): ?>
            <div class="card">
                <h1>Welcome to Jero's Group Project</h1>
                <p>This project demonstrates a fully containerized web application infrastructure. The architecture utilizes several industry-standard technologies working together seamlessly:</p>
                <ul>
                    <li><strong>Docker:</strong> Used to containerize the application, ensuring a consistent environment. We built a custom multi-container setup using Docker Compose.</li>
                    <li><strong>Apache Server & PHP:</strong> The backend is powered by a custom Debian-based Apache web server image with the PHP 8.2 module installed, serving dynamic content.</li>
                    <li><strong>MySQL Database:</strong> A dedicated database container securely stores our project data, which is fetched dynamically via PHP.</li>
                    <li><strong>Bind9 DNS Server:</strong> A local Domain Name System was configured using Bind9 to map our application to a custom domain (<code>jero.com</code>), bypassing standard localhost routing.</li>
                </ul>
            </div>

            <div class="card">
                <h2>Contact Us</h2>
                
                <?php echo $alert_message; ?>

                <form method="POST" action="?page=home">
                    <div class="form-group">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" placeholder="John Doe" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="john@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" rows="5" placeholder="Write your message here..." required></textarea>
                    </div>
                    <button type="submit" name="submit_contact">Send Message</button>
                </form>
            </div>
        
        <?php elseif ($page == 'members'): ?>
            <div class="card">
                <h1>Group Members & Roles</h1>
                <table>
                    <tr>
                        <th>#</th>
                        <th>NAME</th>
                        <th>REGISTRATION NUMBER</th>
                        <th>STUDENT NUMBER</th>
                        <th>ROLE</th>
                    </tr>
                    <?php
                    $sql = "SELECT * FROM group_members_v2";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . $row["id"]. "</td>
                                    <td><strong>" . htmlspecialchars($row["name"]). "</strong></td>
                                    <td>" . htmlspecialchars($row["reg_number"]). "</td>
                                    <td>" . htmlspecialchars($row["student_number"]). "</td>
                                    <td><span class='role-badge'>" . htmlspecialchars($row["role"]). "</span></td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No members found</td></tr>";
                    }
                    ?>
                </table>
            </div>

        <?php elseif ($page == 'messages'): ?>
            <div class="card">
                <h1>Inbox: Contact Form Submissions</h1>
                <p>These messages are pulled dynamically from the <code>contact_messages</code> MySQL database table.</p>
                <table>
                    <tr>
                        <th>Date & Time</th>
                        <th>Sender Name</th>
                        <th>Email Address</th>
                        <th>Message Content</th>
                    </tr>
                    <?php
                    // Fetch messages from newest to oldest
                    $sql = "SELECT * FROM contact_messages ORDER BY submit_date DESC";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td style='white-space: nowrap; font-size: 13px; color: #718096;'>" . $row["submit_date"]. "</td>
                                    <td><strong>" . htmlspecialchars($row["name"]). "</strong></td>
                                    <td><a href='mailto:" . htmlspecialchars($row["email"]) . "' style='color: #2b6cb0; text-decoration: none;'>" . htmlspecialchars($row["email"]). "</a></td>
                                    <td>" . nl2br(htmlspecialchars($row["message"])). "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center; color: #718096; padding: 20px;'>No messages found yet. Go to the Home tab and submit the form!</td></tr>";
                    }
                    ?>
                </table>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>
