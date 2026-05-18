<?php
session_start();
$current_page = basename($_SERVER['PHP_SELF']);
$search_query = htmlspecialchars($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AGRIC DSS - Premium Agricultural Marketplace</title>
<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<!-- FontAwesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- AOS Animation CSS -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />

<style>
    :root {
        --primary: #1b5e20;
        --primary-light: #4caf50;
        --secondary: #ff8f00;
        --secondary-dark: #e65100;
        --dark: #0f172a;
        --light: #f8fafc;
        --gray: #64748b;
    }

    body {
        font-family: 'Outfit', sans-serif;
        background-color: var(--light);
        color: var(--dark);
        margin: 0;
        overflow-x: hidden;
    }

    /* GLASSMORPHISM UTILS */
    .glass {
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
    }

    .glass-dark {
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    /* BUTTON HOVER GLOW */
    .btn-glow {
        position: relative;
        overflow: hidden;
        z-index: 1;
    }
    .btn-glow::before {
        content: '';
        position: absolute;
        top: 0; left: -100%; width: 100%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: 0.5s;
        z-index: -1;
    }
    .btn-glow:hover::before {
        left: 100%;
    }

    /* HERO SECTION with Parallax */
    .hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding-top: 80px;
        background: url('images/hero_farm.png') center/cover no-repeat fixed; /* Parallax effect */
    }

    .hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.4));
        z-index: 1;
    }

    .hero-content {
        position: relative;
        z-index: 2;
        color: white;
    }

    .hero-tag {
        display: inline-block;
        padding: 8px 16px;
        background: rgba(76, 175, 80, 0.2);
        color: var(--primary-light);
        border-radius: 50px;
        font-weight: 600;
        margin-bottom: 24px;
        border: 1px solid rgba(76, 175, 80, 0.3);
    }

    .hero h1 {
        font-size: 4.5rem;
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 24px;
    }

    .hero h1 span {
        background: linear-gradient(135deg, #4caf50, #81c784);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero p {
        font-size: 1.25rem;
        color: #cbd5e1;
        max-width: 600px;
        margin-bottom: 40px;
    }

    /* SEARCH BAR */
    .hero-search {
        display: flex;
        padding: 10px;
        border-radius: 50px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .hero-search:focus-within {
        transform: translateY(-2px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
        background: rgba(255, 255, 255, 0.3);
    }

    .hero-search input {
        flex: 1;
        background: transparent;
        border: none;
        padding: 15px 30px;
        color: white;
        font-size: 1.1rem;
        outline: none;
    }

    .hero-search input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .hero-search button {
        background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
        color: white;
        border: none;
        padding: 15px 40px;
        border-radius: 40px;
        font-weight: 700;
        font-size: 1.1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .hero-search button:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 20px rgba(230, 81, 0, 0.4);
    }

    /* PARTNER MARQUEE */
    .marquee-section {
        background: white;
        padding: 25px 0;
        overflow: hidden;
        border-bottom: 1px solid #e2e8f0;
    }
    .marquee-content {
        display: flex;
        width: max-content;
        animation: marquee 20s linear infinite;
        align-items: center;
        gap: 60px;
        opacity: 0.5;
        filter: grayscale(100%);
        transition: 0.3s ease;
    }
    .marquee-content:hover {
        opacity: 0.8;
        animation-play-state: paused;
    }
    .marquee-content h4 {
        margin: 0;
        font-weight: 800;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: var(--dark);
    }
    @keyframes marquee {
        to { transform: translateX(-50%); }
    }

    /* STATS SECTION */
    .stats-container {
        position: relative;
        z-index: 10;
        margin-top: -60px;
        padding: 0 20px;
    }

    .stat-card {
        background: white;
        padding: 30px;
        border-radius: 20px;
        text-align: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 15px 35px rgba(0,0,0,0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .stat-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 25px 45px rgba(0,0,0,0.12);
    }

    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--primary);
        margin-bottom: 10px;
    }

    .stat-card p {
        color: var(--gray);
        font-weight: 700;
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 0.9rem;
    }

    /* ABOUT SECTION */
    .about-section {
        padding: 100px 0;
        background: white;
    }

    .about-img-wrapper {
        position: relative;
        border-radius: 30px;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    .about-img-wrapper img {
        width: 100%;
        height: auto;
        display: block;
        transition: transform 0.7s ease;
    }

    .about-img-wrapper:hover img {
        transform: scale(1.05);
    }

    .about-badge {
        position: absolute;
        bottom: -20px;
        right: -20px;
        background: white;
        padding: 20px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .about-badge i {
        font-size: 40px;
        color: var(--secondary);
    }

    .about-content h2 {
        font-size: 3rem;
        font-weight: 800;
        margin-bottom: 24px;
        color: var(--dark);
    }

    .about-content p {
        font-size: 1.1rem;
        color: var(--gray);
        line-height: 1.8;
        margin-bottom: 30px;
    }

    /* CATEGORIES SECTION */
    .categories-section {
        padding: 100px 0;
        background: var(--light);
    }

    .section-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .section-header h2 {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 15px;
    }

    .section-header p {
        color: var(--gray);
        font-size: 1.1rem;
    }

    .category-card {
        border-radius: 24px;
        overflow: hidden;
        position: relative;
        height: 320px;
        cursor: pointer;
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    }

    .category-card img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.7s ease;
    }

    .category-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0) 60%);
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 30px;
        transition: all 0.4s ease;
    }

    .category-card:hover img {
        transform: scale(1.1);
    }

    .category-card:hover .category-overlay {
        background: linear-gradient(to top, rgba(30,94,32,0.9) 0%, rgba(0,0,0,0) 80%);
    }

    .category-overlay h4 {
        color: white;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0;
        transform: translateY(10px);
        transition: transform 0.4s ease;
    }

    .category-overlay span {
        color: var(--secondary);
        font-weight: 600;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.4s ease;
    }

    .category-card:hover .category-overlay h4 {
        transform: translateY(0);
    }

    .category-card:hover .category-overlay span {
        opacity: 1;
        transform: translateY(5px);
    }

    /* FEATURES GRID */
    .features-section {
        padding: 100px 0;
        background: white;
    }

    .feature-icon-wrapper {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        border-radius: 15px;
        background: rgba(76, 175, 80, 0.1);
        color: var(--primary-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        margin-right: 20px;
        transition: all 0.3s ease;
    }

    .d-flex:hover .feature-icon-wrapper {
        background: var(--primary-light);
        color: white;
        transform: rotateY(180deg);
    }

    .testimonials-section {
        padding: 100px 0;
        background: var(--primary);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .testimonials-section::before {
        content: '';
        position: absolute;
        top: -50%; right: -20%;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 70%);
    }

    .testimonial-card {
        background: white;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        border-radius: 20px;
        padding: 40px;
        height: 100%;
        margin-bottom: 40px; /* Space for swiper pagination */
        transition: transform 0.3s ease;
    }

    .testimonial-card:hover {
        transform: translateY(-5px);
    }

    .testimonial-card p {
        font-size: 1.15rem;
        line-height: 1.7;
        font-style: italic;
        margin-bottom: 25px;
        color: var(--dark);
        font-weight: 500;
    }

    .testimonial-author {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .testimonial-author img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--primary-light);
    }

    .testimonial-author h5 {
        margin: 0;
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--dark);
    }

    .testimonial-author span {
        color: var(--secondary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* CTA SECTION */
    .cta-section {
        padding: 120px 0;
        background: url('images/maizes.jpg') center/cover fixed; /* Parallax effect */
        position: relative;
        border: none;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(27,94,32,0.95), rgba(10,61,22,0.85));
        z-index: 1;
    }
    
    .cta-container-inner {
        position: relative;
        z-index: 2;
    }

    .cta-content-wrapper {
        position: relative;
        z-index: 2;
        padding: 60px 30px;
        text-align: center;
    }

    .btn-cta {
        background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
        color: white;
        padding: 15px 40px;
        font-size: 1.1rem;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
        box-shadow: 0 10px 20px rgba(255, 143, 0, 0.3);
    }

    .btn-cta:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 15px 25px rgba(255, 143, 0, 0.5);
        color: white;
    }

    /* FAT FOOTER */
    .fat-footer {
        background: #020617;
        color: #94a3b8;
        padding: 80px 0 30px;
    }

    .footer-brand {
        color: white;
        font-size: 1.8rem;
        font-weight: 800;
        text-decoration: none;
        margin-bottom: 20px;
        display: inline-block;
    }

    .fat-footer h5 {
        color: white;
        font-weight: 700;
        margin-bottom: 25px;
    }

    .footer-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links li {
        margin-bottom: 15px;
    }

    .footer-links a {
        color: #94a3b8;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .footer-links a:hover {
        color: var(--primary-light);
    }

    .footer-socials {
        display: flex;
        gap: 15px;
        margin-top: 20px;
    }

    .footer-socials a {
        font-size: 1.5rem;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .footer-socials a:hover {
        color: var(--secondary);
        transform: translateY(-3px);
    }

    .footer-newsletter input {
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        padding: 12px 20px;
        border-radius: 30px;
        width: 100%;
        margin-bottom: 15px;
        outline: none;
    }

    .footer-newsletter button {
        background: var(--primary-light);
        color: white;
        border: none;
        padding: 12px 20px;
        border-radius: 30px;
        width: 100%;
        font-weight: 600;
        transition: background 0.3s ease;
    }

    .footer-newsletter button:hover {
        background: #388e3c;
    }

    .footer-bottom {
        border-top: 1px solid rgba(255,255,255,0.1);
        margin-top: 60px;
        padding-top: 30px;
        text-align: center;
        font-size: 0.9rem;
    }

    /* FLOATING ACTION BUTTON (WhatsApp) */
    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
    }

    .floating-btn {
        width: 60px;
        height: 60px;
        background: #25D366;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        box-shadow: 0 10px 20px rgba(37, 211, 102, 0.4);
        text-decoration: none;
        position: relative;
        transition: transform 0.3s ease;
    }

    .floating-btn:hover {
        transform: scale(1.1);
        color: white;
    }

    .floating-btn::after {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: #25D366;
        border-radius: 50%;
        z-index: -1;
        animation: pulse-fab 2s infinite;
    }

    @keyframes pulse-fab {
        0% { transform: scale(1); opacity: 0.8; }
        100% { transform: scale(1.5); opacity: 0; }
    }

    /* RESPONSIVE */
    @media (max-width: 991px) {
        .hero h1 { font-size: 3.5rem; }
        .about-content { margin-top: 40px; }
        .stat-card { margin-bottom: 20px; }
    }

    @media (max-width: 768px) {
        .hero h1 { font-size: 2.5rem; }
        .hero-search { flex-direction: column; padding: 20px; border-radius: 20px; }
        .hero-search input { margin-bottom: 15px; padding: 10px; }
        .hero-search button { width: 100%; border-radius: 10px; }
    }
</style>
</head>
<body>

<?php include 'navbar.php'; ?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="hero-content">
                    <span class="hero-tag" data-aos="fade-down" data-aos-duration="1000"><i class="fas fa-leaf me-2"></i> Next-Gen Agriculture</span>
                    <h1 data-aos="fade-right" data-aos-duration="1000" data-aos-delay="200">Empowering the Future of <span>Farming.</span></h1>
                    <p data-aos="fade-right" data-aos-duration="1000" data-aos-delay="400">Connect directly with trusted farmers, procure fresh produce securely, and hire premium agricultural services in one integrated ecosystem.</p>
                    
                    <div class="hero-search-wrapper" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="600">
                        <div class="hero-search glass">
                            <i class="fas fa-search" style="padding: 18px 10px 0 20px; color: rgba(255,255,255,0.7);"></i>
                            <input type="text" id="main-search" name="q" placeholder="Search for fresh maize, tractors, or farmers..." onkeypress="if(event.key === 'Enter') { const q = this.value; if(q) window.location.href='search.php?q=' + encodeURIComponent(q); else window.location.href='browse_products.php'; }">
                            <button type="button" onclick="const q = document.getElementById('main-search').value; if(q) window.location.href='search.php?q=' + encodeURIComponent(q); else window.location.href='browse_products.php';" class="btn-glow">Explore Market</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MARQUEE SECTION -->
<div class="marquee-section">
    <div class="marquee-content">
        <!-- Duplicate elements for infinite loop effect -->
        <h4><i class="fas fa-check-circle text-success me-2"></i> 100% Verified Farmers</h4>
        <h4><i class="fas fa-shield-alt text-success me-2"></i> Secure Escrow Payments</h4>
        <h4><i class="fas fa-truck text-success me-2"></i> Reliable Transport Network</h4>
        <h4><i class="fas fa-tractor text-success me-2"></i> Premium Farm Services</h4>
        <h4><i class="fas fa-seedling text-success me-2"></i> High-Quality Produce</h4>
        
        <h4><i class="fas fa-check-circle text-success me-2"></i> 100% Verified Farmers</h4>
        <h4><i class="fas fa-shield-alt text-success me-2"></i> Secure Escrow Payments</h4>
        <h4><i class="fas fa-truck text-success me-2"></i> Reliable Transport Network</h4>
        <h4><i class="fas fa-tractor text-success me-2"></i> Premium Farm Services</h4>
        <h4><i class="fas fa-seedling text-success me-2"></i> High-Quality Produce</h4>
    </div>
</div>

<!-- FLOATING STATS -->
<div class="stats-container container">
    <div class="row g-4 justify-content-center">
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="stat-card">
                <h3 class="counter" data-target="5000">0</h3>
                <p>Active Farmers</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="stat-card">
                <h3 class="counter" data-target="12000">0</h3>
                <p>Products Listed</p>
            </div>
        </div>
        <div class="col-md-4" data-aos="fade-up" data-aos-delay="500">
            <div class="stat-card">
                <h3 class="counter" data-target="99">0</h3>
                <p>Secure Transactions (%)</p>
            </div>
        </div>
    </div>
</div>

<!-- ABOUT SECTION -->
<section class="about-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 position-relative pe-lg-5" data-aos="fade-right" data-aos-duration="1200">
                <div class="about-img-wrapper">
                    <img src="images/farmer_portrait.png" alt="Proud Farmer">
                </div>
                <div class="about-badge d-none d-md-flex" data-aos="zoom-in" data-aos-delay="600">
                    <i class="fas fa-shield-check" style="color: #4caf50;"></i>
                    <div>
                        <h4 style="margin:0; font-weight: 700;">100% Verified</h4>
                        <span style="color: #64748b; font-size: 0.9rem;">Trusted Sellers</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 about-content mt-5 mt-lg-0" data-aos="fade-left" data-aos-duration="1200">
                <h2>Directly from the Source to You.</h2>
                <p>AGRIC DSS bridges the gap between local farmers and buyers by providing a transparent, secure, and highly efficient digital marketplace. We eliminate the middlemen, ensuring farmers get fair value for their hard work and buyers receive the freshest produce available.</p>
                <p>Whether you need bulk grains for manufacturing, fresh vegetables for your restaurant, or a tractor for land preparation, our platform makes it seamless.</p>
                
                <div class="d-flex gap-3 mt-4">
                    <a href="register.php" class="btn btn-dark btn-glow" style="padding: 15px 30px; border-radius: 30px; font-weight: 600;">Join as Farmer</a>
                    <a href="browse_products.php" class="btn btn-outline-dark" style="padding: 15px 30px; border-radius: 30px; font-weight: 600;">Browse Market</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CATEGORIES SECTION -->
<section class="categories-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <h2>Explore Categories</h2>
            <p>Discover a wide range of premium agricultural products and services.</p>
        </div>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="100">
                <div class="category-card" onclick="window.location.href='search.php?q=vegetables'">
                    <img src="images/vegetables.jpg" alt="Fresh Vegetables">
                    <div class="category-overlay">
                        <h4>Fresh Vegetables</h4>
                        <span>View Listings →</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="200">
                <div class="category-card" onclick="window.location.href='search.php?q=grains'">
                    <img src="images/maize.jpg" alt="Grains & Cereals">
                    <div class="category-overlay">
                        <h4>Grains & Cereals</h4>
                        <span>View Listings →</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="300">
                <div class="category-card" onclick="window.location.href='search.php?q=livestock'">
                    <img src="images/cattle.jpg" alt="Livestock">
                    <div class="category-overlay">
                        <h4>Livestock</h4>
                        <span>View Listings →</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6" data-aos="zoom-in" data-aos-delay="400">
                <div class="category-card" onclick="window.location.href='search.php?q=tractor'">
                    <img src="images/bg.jpg" alt="Farm Services">
                    <div class="category-overlay">
                        <h4>Farm Services</h4>
                        <span>View Listings →</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FEATURES SECTION -->
<section class="features-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-5 mb-5 mb-lg-0" data-aos="fade-right" data-aos-duration="1000">
                <div class="position-relative">
                    <img src="images/bg.jpg" alt="Why Choose Us" style="width: 100%; height: 600px; object-fit: cover; border-radius: 30px; box-shadow: 0 30px 60px rgba(0,0,0,0.1);">
                    <div style="position: absolute; bottom: -30px; right: -30px; background: white; padding: 25px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); max-width: 250px;">
                        <h4 style="color: var(--primary); font-weight: 800; margin: 0; font-size: 2rem;">10K+</h4>
                        <p style="margin: 0; color: var(--gray); font-weight: 500;">Successful Trades</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1">
                <div data-aos="fade-left" data-aos-duration="1000">
                    <h2 style="font-size: 3rem; font-weight: 800; margin-bottom: 20px;">Why Choose AGRIC DSS?</h2>
                    <p style="color: var(--gray); font-size: 1.1rem; margin-bottom: 40px;">We provide the tools, security, and market access you need to trade with absolute confidence.</p>
                </div>
                
                <div class="feature-list">
                    <div class="d-flex mb-4" data-aos="fade-left" data-aos-delay="200">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-handshake"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; margin-bottom: 8px;">Dual-Verification System</h4>
                            <p style="color: var(--gray); line-height: 1.6; margin: 0;">Both parties confirm transactions with unique PIN codes, ensuring payment safety and successful deliveries.</p>
                        </div>
                    </div>
                    <div class="d-flex mb-4" data-aos="fade-left" data-aos-delay="400">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; margin-bottom: 8px;">Real-time Market Prices</h4>
                            <p style="color: var(--gray); line-height: 1.6; margin: 0;">Access up-to-date pricing trends for different commodities, helping you make informed buying decisions.</p>
                        </div>
                    </div>
                    <div class="d-flex" data-aos="fade-left" data-aos-delay="600">
                        <div class="feature-icon-wrapper">
                            <i class="fas fa-tractor"></i>
                        </div>
                        <div>
                            <h4 style="font-weight: 700; margin-bottom: 8px;">Comprehensive Ecosystem</h4>
                            <p style="color: var(--gray); line-height: 1.6; margin: 0;">Rent heavy machinery, request loans, and hire specialized farm labor all in one seamlessly integrated place.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- TESTIMONIALS SECTION -->
<section class="testimonials-section">
    <div class="container" data-aos="fade-up">
        <div class="section-header text-center mb-5">
            <h2 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 15px; color: white;">Trusted by Thousands</h2>
            <p style="color: rgba(255,255,255,0.8); font-size: 1.1rem;">Hear what our community has to say about their experience.</p>
        </div>
        
        <!-- Swiper -->
        <div class="swiper testimonialSwiper">
            <div class="swiper-wrapper">
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p>"Since I joined AGRIC DSS, my sales have doubled. The escrow system gives me peace of mind knowing I will be paid once I deliver."</p>
                        <div class="testimonial-author">
                            <img src="images/farmer_portrait.png" alt="Author">
                            <div>
                                <h5>Samuel Mensah</h5>
                                <span>Commercial Maize Farmer</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p>"As a restaurant owner, sourcing fresh vegetables used to be a headache. Now I buy directly from farmers at great prices. The quality is unmatched!"</p>
                        <div class="testimonial-author">
                            <i class="fas fa-user-circle text-secondary" style="font-size: 50px;"></i>
                            <div>
                                <h5>Abigail Osei</h5>
                                <span>Restaurant Owner (Buyer)</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="testimonial-card">
                        <div class="text-warning mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                        </div>
                        <p>"Renting a tractor for land preparation was seamless. The service provider was verified, prompt, and the pricing was very transparent."</p>
                        <div class="testimonial-author">
                            <i class="fas fa-user-circle text-secondary" style="font-size: 50px;"></i>
                            <div>
                                <h5>Kwame Tetteh</h5>
                                <span>Smallholder Farmer</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section">
    <div class="container cta-container-inner">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9" data-aos="zoom-in" data-aos-duration="1000">
                <div class="cta-content-wrapper">
                    <div style="margin-bottom: 25px;">
                        <img src="images/vegetables.jpg" alt="Fresh Produce" style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.2); margin: 0 -5px; position: relative; z-index: 2; display: inline-block;">
                        <img src="images/cattle.jpg" alt="Livestock" style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 15px 30px rgba(0,0,0,0.3); margin: 0 -15px; transform: translateY(-10px); position: relative; z-index: 3; display: inline-block;">
                        <img src="images/maize.jpg" alt="Grains" style="width: 90px; height: 90px; object-fit: cover; border-radius: 50%; border: 3px solid white; box-shadow: 0 10px 20px rgba(0,0,0,0.2); margin: 0 -5px; position: relative; z-index: 1; display: inline-block;">
                    </div>
                    <h2 style="font-size: 2.8rem; font-weight: 800; margin-bottom: 20px; color: white; line-height: 1.2;">Ready to transform your agricultural business?</h2>
                    <p style="font-size: 1.1rem; color: rgba(255,255,255,0.9); margin-bottom: 35px; max-width: 500px; margin-left: auto; margin-right: auto;">Join thousands of verified farmers and buyers experiencing the future of agricultural trading today.</p>
                    <?php if(!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn-cta btn-glow">Get Started for Free</a>
                    <?php else: ?>
                        <a href="dashboard.php" class="btn-cta btn-glow">Go to Dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAT FOOTER -->
<footer class="fat-footer">
    <div class="container">
        <div class="row g-5">
            <div class="col-lg-4 col-md-6">
                <a href="#" class="footer-brand"><i class="fas fa-wheat-awn text-success"></i> AGRIC DSS</a>
                <p style="line-height: 1.7; margin-bottom: 25px;">The ultimate digital marketplace bridging the gap between farmers, buyers, and agricultural service providers.</p>
                <div class="footer-socials">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h5>Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="browse_products.php">Marketplace</a></li>
                    <li><a href="register.php">Sell Produce</a></li>
                    <li><a href="buy.php">Farm Services</a></li>
                    <li><a href="loan.php">Agric Loans</a></li>
                </ul>
            </div>
            
            <div class="col-lg-2 col-md-6">
                <h5>Support</h5>
                <ul class="footer-links">
                    <li><a href="#">Help Center</a></li>
                    <li><a href="#">Safety Center</a></li>
                    <li><a href="#">Community Guidelines</a></li>
                    <li><a href="#">Contact Us</a></li>
                </ul>
            </div>
            
            <div class="col-lg-4 col-md-6">
                <h5>Subscribe to Newsletter</h5>
                <p>Get the latest market prices and platform updates delivered to your inbox.</p>
                <form class="footer-newsletter">
                    <input type="email" placeholder="Your Email Address" required>
                    <button type="button">Subscribe Now</button>
                </form>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p class="mb-0">&copy; <?= date('Y') ?> AGRIC DSS. All rights reserved. | <a href="#" style="color: #94a3b8; text-decoration: none;">Privacy Policy</a> | <a href="#" style="color: #94a3b8; text-decoration: none;">Terms of Service</a></p>
        </div>
    </div>
</footer>

<!-- FLOATING ACTION BUTTON (WhatsApp) -->
<div class="fab-container">
    <a href="https://wa.me/1234567890" target="_blank" class="floating-btn" title="Chat with Support">
        <i class="fab fa-whatsapp"></i>
    </a>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>

<!-- AOS Animation JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<script>
    // Initialize AOS
    AOS.init({
        once: true, // whether animation should happen only once - while scrolling down
        offset: 100, // offset (in px) from the original trigger point
    });

    // Initialize Swiper
    var swiper = new Swiper(".testimonialSwiper", {
        slidesPerView: 1,
        spaceBetween: 30,
        loop: true,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
        breakpoints: {
            768: {
                slidesPerView: 2,
            },
            1024: {
                slidesPerView: 3,
            },
        },
    });

    // Counter Animation
    const counters = document.querySelectorAll('.counter');
    const speed = 200; 

    const animateCounters = () => {
        counters.forEach(counter => {
            const updateCount = () => {
                const target = +counter.getAttribute('data-target');
                const count = +counter.innerText.replace('+', '');
                
                const inc = target / speed;

                if (count < target) {
                    counter.innerText = Math.ceil(count + inc);
                    setTimeout(updateCount, 15);
                } else {
                    counter.innerText = target + (target > 100 ? '+' : '%');
                }
            };
            updateCount();
        });
    }

    const observer = new IntersectionObserver((entries) => {
        if(entries[0].isIntersecting) {
            animateCounters();
            observer.disconnect();
        }
    });
    
    if(counters.length > 0) {
        observer.observe(counters[0]);
    }
</script>
</body>
</html>