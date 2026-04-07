// ==========================================
// BCP Landing Page - JavaScript
// ==========================================

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    
    // ==========================================
    // 1. Initialize AOS (Animate On Scroll)
    // ==========================================
    AOS.init({
        duration: 800,
        easing: 'ease-in-out',
        once: true,
        offset: 100
    });

    // ==========================================
    // 2. Navbar Scroll Effect
    // ==========================================
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelectorAll('.nav-link');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // ==========================================
    // 3. Active Navigation Link on Scroll
    // ==========================================
    const sections = document.querySelectorAll('section[id]');
    
    function highlightNavigation() {
        const scrollY = window.pageYOffset;
        
        sections.forEach(section => {
            const sectionHeight = section.offsetHeight;
            const sectionTop = section.offsetTop - 100;
            const sectionId = section.getAttribute('id');
            const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
            
            if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                navLinks.forEach(link => link.classList.remove('active'));
                if (navLink) {
                    navLink.classList.add('active');
                }
            }
        });
    }
    
    window.addEventListener('scroll', highlightNavigation);

    // ==========================================
    // 4. Smooth Scrolling for Navigation Links
    // ==========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            
            // Don't prevent default for dropdown toggles
            if (href === '#' || this.getAttribute('data-bs-toggle')) {
                return;
            }
            
            e.preventDefault();
            const target = document.querySelector(href);
            
            if (target) {
                const navbarHeight = navbar.offsetHeight;
                const targetPosition = target.offsetTop - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Close mobile menu if open
                const navbarCollapse = document.querySelector('.navbar-collapse');
                if (navbarCollapse.classList.contains('show')) {
                    const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                    bsCollapse.hide();
                }
            }
        });
    });

    // ==========================================
    // 5. Counter Animation
    // ==========================================
    const counters = document.querySelectorAll('.counter');
    let counterAnimated = false;

    function animateCounters() {
        if (counterAnimated) return;
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000; // 2 seconds
            const increment = target / (duration / 16); // 60fps
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString();
                }
            };
            
            updateCounter();
        });
        
        counterAnimated = true;
    }

    // Trigger counter animation when stats section is visible
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const observerOptions = {
            threshold: 0.5
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                }
            });
        }, observerOptions);
        
        observer.observe(statsSection);
    }

    // ==========================================
    // 6. Back to Top Button
    // ==========================================
    const backToTopButton = document.getElementById('backToTop');
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            backToTopButton.classList.add('show');
        } else {
            backToTopButton.classList.remove('show');
        }
    });
    
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });

    // ==========================================
    // 7. Form Handling - Enrollment Form
    // ==========================================
    const enrollmentForm = document.getElementById('enrollmentForm');
    
    if (enrollmentForm) {
        enrollmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(enrollmentForm);
            
            // Show loading state
            const submitBtn = enrollmentForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Submitting...';
            submitBtn.disabled = true;
            
            // Send data via AJAX
            fetch('components/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showAlert('success', 'Thank you! Your application has been submitted successfully. We will contact you soon.');
                    enrollmentForm.reset();
                } else {
                    showAlert('danger', data.message || 'An error occurred. Please try again.');
                }
            })
            .catch(error => {
                showAlert('danger', 'An error occurred. Please try again later.');
                console.error('Error:', error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }

    // ==========================================
    // 8. Form Handling - Contact Form
    // ==========================================
    const contactForm = document.getElementById('contactForm');
    const contactAlert = document.getElementById('contactAlert');
    const contactAlertMessage = document.getElementById('contactAlertMessage');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form data
            const formData = new FormData(contactForm);
            
            // Show loading state
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
            submitBtn.disabled = true;
            
            // Hide previous alerts
            contactAlert.classList.add('d-none');
            
            // Send data via AJAX
            fetch('components/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    contactAlert.classList.remove('d-none', 'alert-danger');
                    contactAlert.classList.add('alert-success');
                    contactAlertMessage.textContent = 'Thank you for your message! We will get back to you soon.';
                    contactForm.reset();
                    
                    // Scroll to alert
                    contactAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                } else {
                    contactAlert.classList.remove('d-none', 'alert-success');
                    contactAlert.classList.add('alert-danger');
                    contactAlertMessage.textContent = data.message || 'An error occurred. Please try again.';
                }
            })
            .catch(error => {
                contactAlert.classList.remove('d-none', 'alert-success');
                contactAlert.classList.add('alert-danger');
                contactAlertMessage.textContent = 'An error occurred. Please try again later.';
                console.error('Error:', error);
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });
    }

    // ==========================================
    // 9. Helper Function - Show Alert
    // ==========================================
    const referenceCodeInput = document.getElementById('referenceCodeInput');
    const checkReferenceBtn = document.getElementById('checkReferenceBtn');
    const referenceStatusResult = document.getElementById('referenceStatusResult');

    if (referenceCodeInput && checkReferenceBtn && referenceStatusResult) {
        const renderReferenceResult = function(type, message, data) {
            referenceStatusResult.classList.remove('d-none', 'reference-status-success', 'reference-status-error', 'reference-status-pending', 'reference-status-validated', 'reference-status-enrolled');

            if (type === 'error') {
                referenceStatusResult.classList.add('reference-status-error');
                referenceStatusResult.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i>' + message;
                return;
            }

            const status = String(data.status || '').toLowerCase();
            const statusClass = status === 'enrolled' ? 'reference-status-enrolled' : (status === 'validated' ? 'reference-status-validated' : 'reference-status-pending');
            referenceStatusResult.classList.add('reference-status-success', statusClass);
            referenceStatusResult.innerHTML =
                '<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">' +
                    '<div><strong>' + data.applicant_name + '</strong><div class="small">Reference: ' + data.reference_number + '</div></div>' +
                    '<span class="badge bg-light text-dark px-3 py-2">Status: ' + data.status + '</span>' +
                '</div>';
        };

        const checkReferenceStatus = function() {
            const reference = referenceCodeInput.value.trim();

            if (!reference) {
                renderReferenceResult('error', 'Please enter your reference number.', null);
                return;
            }

            const originalBtnText = checkReferenceBtn.innerHTML;
            checkReferenceBtn.disabled = true;
            checkReferenceBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Checking...';

            fetch('api/check-reference-status.php?reference=' + encodeURIComponent(reference))
                .then(function(response) {
                    return response.json().then(function(payload) {
                        return { ok: response.ok, payload: payload };
                    });
                })
                .then(function(result) {
                    if (!result.ok || !result.payload.success) {
                        renderReferenceResult('error', result.payload.message || 'Unable to check status right now.', null);
                        return;
                    }

                    renderReferenceResult('success', '', result.payload.data || {});
                })
                .catch(function() {
                    renderReferenceResult('error', 'Network error while checking status. Please try again.', null);
                })
                .finally(function() {
                    checkReferenceBtn.disabled = false;
                    checkReferenceBtn.innerHTML = originalBtnText;
                });
        };

        checkReferenceBtn.addEventListener('click', checkReferenceStatus);
        referenceCodeInput.addEventListener('keydown', function(event) {
            if (event.key === 'Enter') {
                event.preventDefault();
                checkReferenceStatus();
            }
        });
    }

    function showAlert(type, message) {
        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 100px; right: 20px; z-index: 9999; min-width: 300px;';
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'exclamation-triangle-fill'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto dismiss after 5 seconds
        setTimeout(() => {
            alertDiv.classList.remove('show');
            setTimeout(() => alertDiv.remove(), 150);
        }, 5000);
    }

    // ==========================================
    // 10. Form Validation Enhancement
    // ==========================================
    const forms = document.querySelectorAll('form');
    
    forms.forEach(form => {
        const inputs = form.querySelectorAll('input, textarea, select');
        
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });
            
            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });
    });

    function validateField(field) {
        const value = field.value.trim();
        let isValid = true;
        let errorMessage = '';
        
        // Required field validation
        if (field.hasAttribute('required') && value === '') {
            isValid = false;
            errorMessage = 'This field is required';
        }
        
        // Email validation
        if (field.type === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Please enter a valid email address';
            }
        }
        
        // Phone validation
        if (field.type === 'tel' && value !== '') {
            const phoneRegex = /^[\d\s\-\+\(\)]+$/;
            if (!phoneRegex.test(value) || value.length < 10) {
                isValid = false;
                errorMessage = 'Please enter a valid phone number';
            }
        }
        
        // Update field state
        if (isValid) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            removeErrorMessage(field);
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            showErrorMessage(field, errorMessage);
        }
        
        return isValid;
    }

    function showErrorMessage(field, message) {
        removeErrorMessage(field);
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        field.parentNode.appendChild(errorDiv);
    }

    function removeErrorMessage(field) {
        const existingError = field.parentNode.querySelector('.invalid-feedback');
        if (existingError) {
            existingError.remove();
        }
    }

    // ==========================================
    // 11. Program Cards - Read More Toggle
    // ==========================================
    const programCards = document.querySelectorAll('.program-card');
    
    programCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.querySelector('.card-img-top').style.transform = 'scale(1.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.querySelector('.card-img-top').style.transform = 'scale(1)';
        });
    });

    // ==========================================
    // 12. Lazy Loading for Images
    // ==========================================
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        
        images.forEach(img => imageObserver.observe(img));
    }

    // ==========================================
    // 13. Mobile Menu Close on Click Outside
    // ==========================================
    document.addEventListener('click', function(event) {
        const navbarCollapse = document.querySelector('.navbar-collapse');
        const navbarToggler = document.querySelector('.navbar-toggler');
        
        if (navbarCollapse && navbarCollapse.classList.contains('show')) {
            if (!navbarCollapse.contains(event.target) && !navbarToggler.contains(event.target)) {
                const bsCollapse = new bootstrap.Collapse(navbarCollapse);
                bsCollapse.hide();
            }
        }
    });

    // ==========================================
    // 14. Preloader (Optional)
    // ==========================================
    window.addEventListener('load', function() {
        document.body.classList.add('loaded');
    });

    // ==========================================
    // 15. Add active class to current page
    // ==========================================
    const currentLocation = window.location.hash;
    if (currentLocation) {
        const targetLink = document.querySelector(`.nav-link[href="${currentLocation}"]`);
        if (targetLink) {
            navLinks.forEach(link => link.classList.remove('active'));
            targetLink.classList.add('active');
        }
    }

    // ==========================================
    // 16. Initialize Tooltips (Bootstrap 5)
    // ==========================================
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // ==========================================
    // 17. Handle External Links
    // ==========================================
    const externalLinks = document.querySelectorAll('a[href^="http"]');
    externalLinks.forEach(link => {
        if (!link.href.includes(window.location.hostname)) {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer');
        }
    });

    // ==========================================
    // 18. Print Functionality
    // ==========================================
    window.addEventListener('beforeprint', function() {
        // Add any print-specific modifications here
        console.log('Preparing to print...');
    });

    window.addEventListener('afterprint', function() {
        // Restore any changes made for printing
        console.log('Print completed');
    });

    // ==========================================
    // 19. Performance Monitoring (Optional)
    // ==========================================
    if ('performance' in window) {
        window.addEventListener('load', function() {
            const perfData = window.performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            console.log(`Page Load Time: ${pageLoadTime}ms`);
        });
    }

    // ==========================================
    // 20. Console Welcome Message
    // ==========================================
    console.log('%c🎓 Welcome to Bestlink College of the Philippines! 🎓', 
        'color: #2563eb; font-size: 20px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);');
    console.log('%cWebsite developed with ❤️', 
        'color: #666; font-size: 14px;');
});

// ==========================================
// Additional Global Functions
// ==========================================

// Function to check if element is in viewport
function isInViewport(element) {
    const rect = element.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}

// Function to format phone numbers
function formatPhoneNumber(phoneNumberString) {
    const cleaned = ('' + phoneNumberString).replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    if (match) {
        return '(' + match[1] + ') ' + match[2] + '-' + match[3];
    }
    return phoneNumberString;
}

// Function to debounce events
function debounce(func, wait, immediate) {
    let timeout;
    return function() {
        const context = this, args = arguments;
        const later = function() {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}