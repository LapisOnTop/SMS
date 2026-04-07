<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="BestSchool College of the Philippines - Quality education with modern techniques">
    <meta name="keywords" content="college, education, Philippines, enrollment, academic programs">
    <title>BestSchool College of the Philippines - Quality Education</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/website.css">
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#home">
                <img src="assets/images/logo.png" alt="BestSchool Logo" style="width:100px; height:100px; object-fit:contain;"
                    class="me-2">
                <span class="brand-text">BestSchool</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#admission">Admission</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="academicsDropdown" role="button"
                            data-bs-toggle="dropdown">
                            Academics
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#programs">Programs</a></li>
                            <li><a class="dropdown-item" href="#programs">Undergraduate</a></li>
                            <li><a class="dropdown-item" href="#programs">Graduate</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#news">News</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contact</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary ms-lg-3" href="#admission">Enroll Now</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="hero-overlay"></div>
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-8 mx-auto text-center hero-content">
                    <h1 class="display-3 fw-bold text-white mb-4" data-aos="fade-up">
                        Welcome to BestSchool College of the Philippines
                    </h1>
                    <p class="lead text-white mb-5" data-aos="fade-up" data-aos-delay="100">
                        At BestSchool College of the Philippines, We provide and promote quality education with modern
                        and
                        unique techniques to able to enhance the skill and the knowledge of our dear students to make
                        them globally competitive and productive citizens.
                    </p>
                    <a href="#admission" class="btn btn-primary btn-lg px-5 py-3 smooth-scroll" data-aos="fade-up"
                        data-aos-delay="200">
                        <i class="bi bi-pencil-square me-2"></i>Enroll Now
                    </a>
                </div>
            </div>
        </div>
        <div class="hero-wave"></div>
    </section>

    <!-- Statistics Section -->
    <section class="stats-section py-5 bg-primary text-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-3 col-6 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="0">
                    <i class="bi bi-people-fill display-4 mb-3"></i>
                    <h2 class="counter fw-bold" data-target="5000">0</h2>
                    <p class="mb-0">Students</p>
                </div>
                <div class="col-md-3 col-6 mb-4 mb-md-0" data-aos="fade-up" data-aos-delay="100">
                    <i class="bi bi-person-badge-fill display-4 mb-3"></i>
                    <h2 class="counter fw-bold" data-target="200">0</h2>
                    <p class="mb-0">Faculty Members</p>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="200">
                    <i class="bi bi-book-fill display-4 mb-3"></i>
                    <h2 class="counter fw-bold" data-target="50">0</h2>
                    <p class="mb-0">Programs</p>
                </div>
                <div class="col-md-3 col-6" data-aos="fade-up" data-aos-delay="300">
                    <i class="bi bi-award-fill display-4 mb-3"></i>
                    <h2 class="counter fw-bold" data-target="25">0</h2>
                    <p class="mb-0">Years of Excellence</p>
                </div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 section-padding">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="assets/images/about-campus.jpg" alt="Campus" class="img-fluid rounded-4 shadow-lg">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h6 class="text-primary text-uppercase fw-bold mb-2">About BestSchool</h6>
                    <h2 class="display-5 fw-bold mb-4">Building Tomorrow's Leaders Today</h2>
                    <p class="text-muted mb-4">
                        BestSchool College of the Philippines has been a beacon of quality education for over two
                        decades. We are committed to developing globally competitive graduates through innovative
                        teaching methods and state-of-the-art facilities.
                    </p>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-bullseye text-primary me-2"></i>Our Mission</h5>
                        <p class="text-muted">
                            To provide accessible, quality, and relevant education that develops competent professionals
                            and responsible citizens who contribute to nation-building.
                        </p>
                    </div>

                    <div class="mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-eye text-primary me-2"></i>Our Vision</h5>
                        <p class="text-muted">
                            To be a premier educational institution recognized for excellence in instruction, research,
                            and community engagement.
                        </p>
                    </div>

                    <a href="#about" class="btn btn-outline-primary">Learn More About Us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Programs Section -->
    <section id="programs" class="py-5 section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary text-uppercase fw-bold mb-2">Our Programs</h6>
                <h2 class="display-5 fw-bold mb-3">Explore Our Academic Programs</h2>
                <p class="text-muted">Choose from our wide range of programs designed to prepare you for success</p>
            </div>

            <div class="row g-4">
                <!-- Program Card 1 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-it.jpg" class="card-img-top" alt="IT Program">
                            <div class="card-overlay">
                                <i class="bi bi-laptop display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Information Technology</h5>
                            <p class="card-text text-muted">
                                Master the latest technologies and programming languages. Prepare for careers in
                                software development, cybersecurity, and data science.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>4-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Industry Partnerships</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Internship Opportunities
                                </li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>

                <!-- Program Card 2 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-business.jpg" class="card-img-top" alt="Business Program">
                            <div class="card-overlay">
                                <i class="bi bi-briefcase display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Business Administration</h5>
                            <p class="card-text text-muted">
                                Develop leadership and management skills essential for success in the business world.
                                Specialize in marketing, finance, or entrepreneurship.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>4-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Business Incubator</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Global Perspective</li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>

                <!-- Program Card 3 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-engineering.jpg" class="card-img-top" alt="Engineering Program">
                            <div class="card-overlay">
                                <i class="bi bi-gear display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Engineering</h5>
                            <p class="card-text text-muted">
                                Build a strong foundation in engineering principles. Choose from civil, electrical,
                                mechanical, or computer engineering tracks.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>5-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Modern Laboratories</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Licensed Instructors</li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>

                <!-- Program Card 4 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-education.jpg" class="card-img-top" alt="Education Program">
                            <div class="card-overlay">
                                <i class="bi bi-book display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Education</h5>
                            <p class="card-text text-muted">
                                Shape the future by becoming an educator. Our program prepares you for teaching careers
                                in elementary or secondary education.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>4-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Teaching Practice</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>LET Preparation</li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>

                <!-- Program Card 5 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-hospitality.jpg" class="card-img-top" alt="Hospitality Program">
                            <div class="card-overlay">
                                <i class="bi bi-cup-hot display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Hospitality Management</h5>
                            <p class="card-text text-muted">
                                Learn the art of hospitality and service excellence. Perfect for careers in hotels,
                                restaurants, tourism, and event management.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>4-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>Training Facilities</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>OJT Programs</li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>

                <!-- Program Card 6 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card program-card h-100 border-0 shadow-sm">
                        <div class="card-img-wrapper">
                            <img src="assets/images/program-accounting.jpg" class="card-img-top" alt="Accounting Program">
                            <div class="card-overlay">
                                <i class="bi bi-calculator display-3 text-white"></i>
                            </div>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold">Accountancy</h5>
                            <p class="card-text text-muted">
                                Prepare for a career in accounting, auditing, and financial management. Our program
                                helps you excel in the CPA board exam.
                            </p>
                            <ul class="list-unstyled mb-3">
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>5-Year Program</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>CPA Board Review</li>
                                <li><i class="bi bi-check-circle-fill text-primary me-2"></i>High Passing Rate</li>
                            </ul>
                            <a href="#admission" class="btn btn-primary w-100">Learn More</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- News Section -->
    <section id="news" class="py-5 section-padding">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary text-uppercase fw-bold mb-2">Latest Updates</h6>
                <h2 class="display-5 fw-bold mb-3">News & Announcements</h2>
                <p class="text-muted">Stay updated with the latest happenings at BestSchool</p>
            </div>

            <div class="row g-4">
                <!-- News Card 1 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="0">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <img src="assets/images/news-1.jpg" class="card-img-top" alt="News 1">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary me-2">Announcement</span>
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>March 15, 2026</small>
                            </div>
                            <h5 class="card-title fw-bold">Enrollment for SY 2026-2027 Now Open</h5>
                            <p class="card-text text-muted">
                                We are now accepting applications for the upcoming school year. Early bird discounts
                                available for enrollees before April 30.
                            </p>
                            <a href="#" class="btn btn-outline-primary">Read More <i
                                    class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <!-- News Card 2 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="100">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <img src="assets/images/news-2.jpg" class="card-img-top" alt="News 2">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-success me-2">Achievement</span>
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>March 10, 2026</small>
                            </div>
                            <h5 class="card-title fw-bold">BestSchool Students Win National Competition</h5>
                            <p class="card-text text-muted">
                                Our IT students brought home the championship trophy at the National Programming
                                Competition held in Manila.
                            </p>
                            <a href="#" class="btn btn-outline-primary">Read More <i
                                    class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>

                <!-- News Card 3 -->
                <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="200">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <img src="assets/images/news-3.jpg" class="card-img-top" alt="News 3">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-info me-2">Event</span>
                                <small class="text-muted"><i class="bi bi-calendar me-1"></i>March 5, 2026</small>
                            </div>
                            <h5 class="card-title fw-bold">Career Fair 2026 Successfully Held</h5>
                            <p class="card-text text-muted">
                                Over 50 companies participated in our annual career fair, offering job opportunities and
                                internships to our graduating students.
                            </p>
                            <a href="#" class="btn btn-outline-primary">Read More <i
                                    class="bi bi-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Admission Section -->
    <section id="admission" class="py-5 section-padding bg-primary text-white">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <h6 class="text-uppercase fw-bold mb-2" style="color: rgba(255,255,255,0.8);">Admission Process</h6>
                    <h2 class="display-5 fw-bold mb-4">Ready to Join BestSchool?</h2>
                    <p class="mb-4" style="color: rgba(255,255,255,0.9);">
                        Applying to BestSchool is easy. Follow these simple steps to start your
                        journey towards academic excellence.
                    </p>

                    <div class="admission-steps">
                        <div class="step-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="step-number me-3">1</div>
                                <div>
                                    <h5 class="fw-bold mb-2">Submit Application</h5>
                                    <p style="color: rgba(255,255,255,0.8);">Fill out our online application form with
                                        your personal and academic information.</p>
                                </div>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="step-number me-3">2</div>
                                <div>
                                    <h5 class="fw-bold mb-2">Take Entrance Exam</h5>
                                    <p style="color: rgba(255,255,255,0.8);">Schedule and complete the entrance
                                        examination at our testing center.</p>
                                </div>
                            </div>
                        </div>

                        <div class="step-item mb-4">
                            <div class="d-flex align-items-start">
                                <div class="step-number me-3">3</div>
                                <div>
                                    <h5 class="fw-bold mb-2">Submit Requirements</h5>
                                    <p style="color: rgba(255,255,255,0.8);">Provide necessary documents including
                                        transcripts, ID, and recommendation letters.</p>
                                </div>
                            </div>
                        </div>

                        <div class="step-item">
                            <div class="d-flex align-items-start">
                                <div class="step-number me-3">4</div>
                                <div>
                                    <h5 class="fw-bold mb-2">Enroll & Start Classes</h5>
                                    <p style="color: rgba(255,255,255,0.8);">Complete enrollment process and begin your
                                        academic journey with us.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6" data-aos="fade-left">

                    <!-- Reference Code Checker -->
                    <div class="card border-0 shadow-sm mb-4"
                        style="background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3) !important;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold mb-1 text-white">
                                <i class="bi bi-search me-2"></i>Already Applied?
                            </h6>
                            <p class="mb-3" style="color: rgba(255,255,255,0.8); font-size: 0.875rem;">
                                Enter your reference number to check your application status.
                            </p>
                            <div class="input-group">
                                <input type="text" class="form-control" id="referenceCodeInput"
                                    placeholder="e.g. REF-2026-00123" style="border-right: 0;">
                                <button class="btn btn-light fw-semibold px-4" type="button" id="checkReferenceBtn">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Check
                                </button>
                            </div>
                            <div id="referenceStatusResult" class="reference-status-result mt-3 d-none" role="status" aria-live="polite"></div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="text-dark fw-bold mb-4">Start Your Application</h4>

                            <form id="enrollmentForm" action="components/contact.php" method="POST">
                                <input type="hidden" name="form_type" value="enrollment">

                                <div class="mb-3">
                                    <label for="fullName" class="form-label text-dark">Full Name *</label>
                                    <input type="text" class="form-control" id="fullName" name="fullName" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label text-dark">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label text-dark">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>

                                <div class="mb-3">
                                    <label for="program" class="form-label text-dark">Interested Program *</label>
                                    <select class="form-select" id="program" name="program" required>
                                        <option value="">Select a program</option>
                                        <option value="Information Technology">Information Technology</option>
                                        <option value="Business Administration">Business Administration</option>
                                        <option value="Engineering">Engineering</option>
                                        <option value="Education">Education</option>
                                        <option value="Hospitality Management">Hospitality Management</option>
                                        <option value="Accountancy">Accountancy</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="message" class="form-label text-dark">Message (Optional)</label>
                                    <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 py-3">
                                    <i class="bi bi-send me-2"></i>Submit Application
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5 section-padding">
        <div class="container">
            <div class="text-center mb-5" data-aos="fade-up">
                <h6 class="text-primary text-uppercase fw-bold mb-2">Get In Touch</h6>
                <h2 class="display-5 fw-bold mb-3">Contact Us</h2>
                <p class="text-muted">Have questions? We'd love to hear from you.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="0">
                    <div class="contact-info-card text-center p-4 h-100">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Visit Us</h5>
                        <p class="text-muted">
                            BESTSCHOOL College of the Philippines<br>
                            Quirino Highway, Novaliches<br>
                            Quezon City, Philippines 1116
                        </p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="contact-info-card text-center p-4 h-100">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="bi bi-telephone-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Call Us</h5>
                        <p class="text-muted">
                            Main Office: (02) 8123-4567<br>
                            Admission Office: (02) 8123-4568<br>
                            Mobile: +63 917 123 4567
                        </p>
                    </div>
                </div>

                <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="contact-info-card text-center p-4 h-100">
                        <div class="icon-circle mx-auto mb-3">
                            <i class="bi bi-envelope-fill"></i>
                        </div>
                        <h5 class="fw-bold mb-3">Email Us</h5>
                        <p class="text-muted">
                            General: info@BestSchool.edu.ph<br>
                            Admissions: admissions@BestSchool.edu.ph<br>
                            Support: support@BestSchool.edu.ph
                        </p>
                    </div>
                </div>
            </div>

            <div class="row mt-5">
                <div class="col-lg-8 mx-auto" data-aos="fade-up">
                    <div class="card border-0 shadow-lg">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="fw-bold mb-4">Send Us a Message</h4>

                            <div id="contactAlert" class="alert alert-success d-none" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <span id="contactAlertMessage"></span>
                            </div>

                            <form id="contactForm" action="components/contact.php" method="POST">
                                <input type="hidden" name="form_type" value="contact">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="contactName" class="form-label">Your Name *</label>
                                        <input type="text" class="form-control" id="contactName" name="name" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="contactEmail" class="form-label">Your Email *</label>
                                        <input type="email" class="form-control" id="contactEmail" name="email"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <label for="contactSubject" class="form-label">Subject *</label>
                                        <input type="text" class="form-control" id="contactSubject" name="subject"
                                            required>
                                    </div>

                                    <div class="col-12">
                                        <label for="contactMessage" class="form-label">Message *</label>
                                        <textarea class="form-control" id="contactMessage" name="message" rows="5"
                                            required></textarea>
                                    </div>

                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary px-5 py-3">
                                            <i class="bi bi-send me-2"></i>Send Message
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-3">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <h5 class="fw-bold mb-4">About BestSchool</h5>
                    <p class="text-white-50 mb-4">
                        BestSchool is committed to providing quality education that prepares
                        students for global competitiveness.
                    </p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-linkedin"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-youtube"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="fw-bold mb-4">Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#home" class="text-white-50">Home</a></li>
                        <li><a href="#about" class="text-white-50">About Us</a></li>
                        <li><a href="#programs" class="text-white-50">Programs</a></li>
                        <li><a href="#admission" class="text-white-50">Admission</a></li>
                        <li><a href="#news" class="text-white-50">News</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-4">Student Resources</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="#" class="text-white-50">Student Portal</a></li>
                        <li><a href="#" class="text-white-50">Library</a></li>
                        <li><a href="#" class="text-white-50">E-Learning</a></li>
                        <li><a href="#" class="text-white-50">Career Services</a></li>
                        <li><a href="#" class="text-white-50">Alumni Network</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="fw-bold mb-4">Contact Info</h5>
                    <ul class="list-unstyled footer-contact">
                        <li class="text-white-50 mb-2">
                            <i class="bi bi-geo-alt-fill me-2"></i>
                            Quirino Highway, Novaliches, QC
                        </li>
                        <li class="text-white-50 mb-2">
                            <i class="bi bi-telephone-fill me-2"></i>
                            (02) 8123-4567
                        </li>
                        <li class="text-white-50 mb-2">
                            <i class="bi bi-envelope-fill me-2"></i>
                            info@BestSchool.edu.ph
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4 bg-white opacity-25">

            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="text-white-50 mb-0">&copy; 2026 BestSchool College of the Philippines. All Rights
                        Reserved.
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <a href="#" class="text-white-50 me-3">Privacy Policy</a>
                    <a href="#" class="text-white-50">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary back-to-top">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- AOS Animation -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/website.js"></script>
</body>

</html>