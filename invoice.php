<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get reservation ID from query string
if (!isset($_GET['reservation_id'])) {
    header("Location: clerk_dashboard.php");
    exit();
}

$reservationId = $_GET['reservation_id'];

// Fetch reservation details
$stmt = $pdo->prepare("SELECT r.*, g.first_name, g.last_name, g.email, g.phone, 
                      rm.room_number, rm.room_type, rm.rate as room_rate,
                      u.full_name as clerk_name
                      FROM reservations r
                      JOIN guests g ON r.guest_id = g.guest_id
                      JOIN rooms rm ON r.room_id = rm.room_id
                      JOIN users u ON r.checked_in_by = u.user_id
                      WHERE r.reservation_id = ?");
$stmt->execute([$reservationId]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header("Location: clerk_dashboard.php");
    exit();
}

// Calculate stay duration
$checkin = new DateTime($reservation['checkin_date']);
$checkout = new DateTime($reservation['checkout_date']);
$interval = $checkin->diff($checkout);
$nights = $interval->days;
if ($interval->h > 12 || ($interval->days == 0 && $interval->h >= 1)) {
    $nights++; // Count as a full night if more than 12 hours or at least 1 hour for same-day
}

// Calculate room charges
$roomCharges = $nights * $reservation['room_rate'];

// Fetch additional services
$services = $pdo->prepare("SELECT s.service_name, s.price, bs.quantity, bs.service_date
                          FROM billed_services bs
                          JOIN services s ON bs.service_id = s.service_id
                          WHERE bs.reservation_id = ?");
$services->execute([$reservationId]);
$services = $services->fetchAll();

// Calculate service charges
$serviceCharges = 0;
foreach ($services as $service) {
    $serviceCharges += $service['price'] * $service['quantity'];
}

// Calculate tax (10%)
$taxRate = 0.10;
$taxAmount = ($roomCharges + $serviceCharges) * $taxRate;

// Calculate total
$totalAmount = $roomCharges + $serviceCharges + $taxAmount;

// Handle invoice generation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_invoice'])) {
    try {
        $pdo->beginTransaction();
        
        // Create invoice record
        $stmt = $pdo->prepare("INSERT INTO invoices (reservation_id, room_charges, service_charges, tax_amount, total_amount, 
                                payment_method, issued_by, issue_date) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([
            $reservationId,
            $roomCharges,
            $serviceCharges,
            $taxAmount,
            $totalAmount,
            $_POST['payment_method'],
            $_SESSION['user_id']
        ]);
        
        $invoiceId = $pdo->lastInsertId();
        
        // Update reservation with invoice ID
        $stmt = $pdo->prepare("UPDATE reservations SET invoice_id = ? WHERE reservation_id = ?");
        $stmt->execute([$invoiceId, $reservationId]);
        
        $pdo->commit();
        
        // Redirect to print view
        header("Location: print_invoice.php?invoice_id=$invoiceId");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error generating invoice: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Luxora Hotel Suite - Invoice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2980b9;
            --accent-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px;
            background-color: white;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        
        .invoice-header {
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .hotel-logo {
            max-width: 150px;
        }
        
        .invoice-title {
            color: var(--primary-color);
            font-weight: 700;
        }
        
        .table th {
            background-color: var(--light-color);
        }
        
        .total-row {
            font-weight: bold;
            background-color: rgba(52, 152, 219, 0.1);
        }
        
        .signature-line {
            border-top: 1px dashed #ccc;
            margin-top: 80px;
            padding-top: 10px;
        }
        
        @media print {
            body {
                background-color: white;
            }
            
            .invoice-container {
                box-shadow: none;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="invoice-container">
            <!-- Invoice Header -->
            <div class="invoice-header">
                <div class="row">
                    <div class="col-md-6">
                        <img src="logo.png" alt="Luxora Hotel Suite" class="hotel-logo mb-3">
                        <h2 class="invoice-title">INVOICE</h2>
                        <p class="mb-1"><strong>Invoice #:</strong> <?= str_pad($reservationId, 6, '0', STR_PAD_LEFT) ?></p>
                        <p class="mb-1"><strong>Date:</strong> <?= date('M j, Y') ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <h4>Luxora Hotel Suite</h4>
                        <p>123 Ocean View Drive<br>
                        Colombo, Sri Lanka<br>
                        Phone: +94 11 234 5678<br>
                        Email: info