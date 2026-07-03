<?php
// Database connection
$host = 'localhost';
$dbname = 'ai_solutions';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get and sanitize form data
    $full_name   = htmlspecialchars(trim($_POST['full_name']));
    $email       = htmlspecialchars(trim($_POST['email']));
    $phone       = htmlspecialchars(trim($_POST['phone']));
    $company     = htmlspecialchars(trim($_POST['company']));
    $country     = htmlspecialchars(trim($_POST['country']));
    $job_title   = htmlspecialchars(trim($_POST['job_title']));
    $job_details = htmlspecialchars(trim($_POST['job_details']));

    // Validate all fields are filled
    if (empty($full_name) || empty($email) || empty($phone) || empty($company) || empty($country) || empty($job_title) || empty($job_details)) {
        header('Location: contact.html?status=error');
        exit;
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: contact.html?status=error');
        exit;
    }

    // Insert into database
    $stmt = $pdo->prepare("INSERT INTO inquiries (full_name, email, phone, company, country, job_title, job_details) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $phone, $company, $country, $job_title, $job_details]);

    // Redirect to success
    header('Location: contact.html?status=success');
    exit;

} catch (PDOException $e) {
    header('Location: contact.html?status=error');
    exit;
}
?>
