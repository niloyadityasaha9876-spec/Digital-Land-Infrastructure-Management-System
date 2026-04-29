<?php
// index.php (Landing Page)
session_start();
require_once 'db_connect.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admin_dashboard.php");
    exit;
} elseif (isset($_SESSION['user_id'])) {
    header("Location: user_dashboard.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="container">
    <section class="hero">
        <div class="hero-content">
            <h1>Welcome to Land Management System</h1>
            <p>Efficiently manage land records, transactions, and property information with our comprehensive land management solution.</p>
            <div class="hero-buttons">
                <a href="login.php" class="btn btn-primary btn-lg">Sign In</a>
                <a href="user_register.php" class="btn btn-secondary btn-lg">Register as Citizen</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Land Records Management</h3>
                <p>Maintain accurate and up-to-date land ownership records, parcel information, and cadastral maps.</p>
            </div>
            <div class="feature-card">
                <h3>Transaction Processing</h3>
                <p>Streamline land transfer processes, mortgage registrations, and property transactions with automated workflows.</p>
            </div>
            <div class="feature-card">
                <h3>Secure Access Control</h3>
                <p>Role-based access ensures that citizens, officials, and administrators have appropriate permissions to view and modify data.</p>
            </div>
            <div class="feature-card">
                <h3>Real-time Analytics</h3>
                <p>Generate reports and insights on land usage, property values, and transaction trends for informed decision-making.</p>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about">
        <h2>About Our System</h2>
        <p>The Land Management System (LMS) is designed to modernize land administration processes, reduce fraud, and improve service delivery to citizens. Our system integrates various land-related functions into a single, user-friendly platform.</p>
        <p>Whether you're a citizen looking to verify property ownership, a government official managing land records, or an administrator overseeing the entire system, LMS provides the tools you need to work efficiently and transparently.</p>
    </section>
</div>

<?php include 'footer.php'; ?>

<style>
    /* Custom styles for landing page */
    .hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 4rem 2rem;
        margin: -20px -20px 2rem -20px;
    }

    .hero-content h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    .hero-content p {
        font-size: 1.25rem;
        margin-bottom: 2rem;
        opacity: 0.9;
    }

    .hero-buttons .btn {
        margin: 0 0.5rem;
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
    }

    .features {
        padding: 2rem;
        background-color: #f8f9fa;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .feature-card {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        text-align: center;
    }

    .feature-card h3 {
        color: #333;
        margin-bottom: 1rem;
    }

    .about {
        padding: 2rem;
        max-width: 800px;
        margin: 0 auto;
    }

    .about h2 {
        color: #333;
        text-align: center;
        margin-bottom: 1.5rem;
    }

    .about p {
        line-height: 1.6;
        color: #666;
        text-align: center;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hero-content h1 {
            font-size: 2rem;
        }

        .hero-content p {
            font-size: 1.1rem;
        }

        .feature-grid {
            grid-template-columns: 1fr;
        }
    }
</style>