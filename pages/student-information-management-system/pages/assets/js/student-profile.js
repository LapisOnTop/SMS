(function() {
    'use strict';

    // DOM Elements
    const studentForm = document.getElementById('studentRegistrationForm') || document.getElementById('studentProfileForm');
    const photoInput = document.getElementById('studentPhoto');
    const photoImage = document.getElementById('photoImage');
    const photoPreview = document.getElementById('photoPreview');
    const signatureCanvas = document.getElementById('signatureCanvas');
    const clearSignatureBtn = document.getElementById('clearSignatureBtn');
    const signatureInput = document.getElementById('studentSignature');
    const signatureImage = document.getElementById('signatureImage');
    const signaturePreview = document.getElementById('signaturePreview');
    const messageToast = document.getElementById('messageToast');
    const navItems = document.querySelectorAll('.nav-item');

    // Canvas context
    let canvasContext = null;
    let isDrawing = false;
    let hasSignature = false;
    let isInitialized = false;

    /**
     * Initialize all event listeners
     */
    function init() {
        if (isInitialized) {
            return;
        }
        isInitialized = true;

        // Form submission
        if (studentForm) {
            studentForm.addEventListener('submit', handleFormSubmit);
        }

        // Photo upload
        if (photoInput) {
            photoInput.addEventListener('change', handlePhotoUpload);
        }

        // Photo preview click
        if (photoPreview) {
            photoPreview.addEventListener('click', () => photoInput.click());
        }

        // Signature upload
        if (signatureInput) {
            signatureInput.addEventListener('change', handleSignatureUpload);
        }

        if (signaturePreview) {
            signaturePreview.addEventListener('click', () => signatureInput.click());
        }

        // Signature canvas
        if (signatureCanvas) {
            setupSignatureCanvas();
            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseout', stopDrawing);
            
            // Touch support
            signatureCanvas.addEventListener('touchstart', handleTouchStart);
            signatureCanvas.addEventListener('touchmove', handleTouchMove);
            signatureCanvas.addEventListener('touchend', stopDrawing);
        }

        // Clear signature button
        if (clearSignatureBtn) {
            clearSignatureBtn.addEventListener('click', clearSignature);
        }

        // Sidebar navigation
        navItems.forEach(item => {
            item.addEventListener('click', handleNavigation);
        });

        // Reference fetching (preferred)
        const btnFetchRef = document.getElementById('btnFetchReference');
        if (btnFetchRef) {
            btnFetchRef.addEventListener('click', handleFetchReference);
        }

        const refInput = document.getElementById('referenceNumber');
        if (refInput) {
            let debounceTimer;
            refInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    const v = refInput.value.trim();
                    if (v.length >= 6) {
                        handleFetchReference();
                    }
                }, 700);
            });
            refInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    handleFetchReference();
                }
            });
            refInput.addEventListener('blur', function() {
                const v = refInput.value.trim();
                if (v.length >= 6) {
                    handleFetchReference();
                }
            });
        }

        // Back-compat: older pages may still have Application ID fetching
        const btnFetchApp = document.getElementById('btnFetchApplication');
        if (btnFetchApp) {
            btnFetchApp.addEventListener('click', handleFetchApplication);
        }

        const appIdInput = document.getElementById('applicationId');
        if (appIdInput) {
            let debounceTimer;
            appIdInput.addEventListener('input', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => {
                    if (appIdInput.value.trim().length >= 1) {
                        handleFetchApplication();
                    }
                }, 800);
            });
            appIdInput.addEventListener('blur', function() {
                if (appIdInput.value.trim() !== '') {
                    handleFetchApplication();
                }
            });
        }
    }

    /**
     * Setup signature canvas size
     */
    function setupSignatureCanvas() {
        const container = signatureCanvas.parentElement;
        const rect = container.getBoundingClientRect();
        
        signatureCanvas.width = signatureCanvas.offsetWidth;
        signatureCanvas.height = signatureCanvas.offsetHeight;
        
        canvasContext = signatureCanvas.getContext('2d');
        canvasContext.lineCap = 'round';
        canvasContext.lineJoin = 'round';
        canvasContext.lineWidth = 2;
        canvasContext.strokeStyle = '#333';
    }

    /**
     * Start drawing signature
     */
    function startDrawing(e) {
        isDrawing = true;
        const rect = signatureCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        canvasContext.beginPath();
        canvasContext.moveTo(x, y);
    }

    /**
     * Draw on signature canvas
     */
    function draw(e) {
        if (!isDrawing) return;
        
        const rect = signatureCanvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        canvasContext.lineTo(x, y);
        canvasContext.stroke();
        hasSignature = true;
    }

    /**
     * Stop drawing signature
     */
    function stopDrawing() {
        if (isDrawing) {
            canvasContext.closePath();
            isDrawing = false;
        }
    }

    /**
     * Handle touch start
     */
    function handleTouchStart(e) {
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousedown', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        signatureCanvas.dispatchEvent(mouseEvent);
    }

    /**
     * Handle touch move
     */
    function handleTouchMove(e) {
        e.preventDefault();
        const touch = e.touches[0];
        const mouseEvent = new MouseEvent('mousemove', {
            clientX: touch.clientX,
            clientY: touch.clientY
        });
        signatureCanvas.dispatchEvent(mouseEvent);
    }

    /**
     * Clear signature
     */
    function clearSignature(e) {
        e.preventDefault();
        canvasContext.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
        hasSignature = false;
    }

    /**
     * Handle photo upload
     */
    function handlePhotoUpload(e) {
        const file = e.target.files[0];
        
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            showMessage('error', 'Please select a valid image file');
            return;
        }

        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            showMessage('error', 'File size must be less than 10MB');
            return;
        }

        // Read and display image
        const reader = new FileReader();
        reader.onload = (event) => {
            const img = new Image();
            img.onload = function() {
                const ratio = this.width / this.height;
                if (ratio < 0.8 || ratio > 1.2) {
                    showMessage('info', 'Note: Please ensure the photo has a white background and is a 1x1 (square) picture.');
                }
                photoImage.src = event.target.result;
                showMessage('success', 'Photo uploaded successfully');
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }

    /**
     * Handle signature upload
     */
    function handleSignatureUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            showMessage('error', 'Please select a valid image file');
            return;
        }

        const reader = new FileReader();
        reader.onload = (event) => {
            if (signatureImage) {
                signatureImage.src = event.target.result;
            }
            hasSignature = true;
            showMessage('success', 'Signature uploaded successfully');
        };
        reader.readAsDataURL(file);
    }

    /**
     * Handle fetching application data
     */
    function handleFetchApplication() {
        const idInput = document.getElementById('applicationId');
        const msgDiv = document.getElementById('fetchMessage');
        const eid = idInput ? idInput.value.trim() : '';
        
        if (!eid) {
            return;
        }
        
        if (msgDiv) {
            msgDiv.style.display = 'block';
            msgDiv.style.color = '#6b7280';
            msgDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching data from Database...';
        }
        
        fetch(`../../api/fetch-application.php?application_id=${encodeURIComponent(eid)}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const student = data.data;
                    
                    // Populate fields
                    if(document.getElementById('firstName')) document.getElementById('firstName').value = student.first_name || '';
                    if(document.getElementById('lastName')) document.getElementById('lastName').value = student.last_name || '';
                    if(document.getElementById('middleName')) document.getElementById('middleName').value = student.middle_name || '';
                    if(document.getElementById('birthdate')) document.getElementById('birthdate').value = student.birth_date || '';
                    if(document.getElementById('email')) document.getElementById('email').value = student.email || '';
                    
                    // Contact Number handling
                    if(document.getElementById('contactNumber')){
                        let phone = student.contact_number || '';
                        if(phone.startsWith('+63')) {
                            phone = phone.substring(3).trim();
                        } else if(phone.length === 11 && phone.startsWith('09')) {
                            phone = phone.substring(1).trim(); 
                        }
                        document.getElementById('contactNumber').value = phone;
                    }
                    
                    // Gender formatting
                    if(document.getElementById('gender') && student.sex) {
                        const s = student.sex.toLowerCase();
                        if (s === 'male' || s === 'female') {
                            document.getElementById('gender').value = s;
                        }
                    }
                    
                    // Address logic
                    if(document.getElementById('address')) {
                        let fullAddr = [];
                        if (student.barangay) fullAddr.push(student.barangay);
                        if (student.city_municipality) fullAddr.push(student.city_municipality);
                        if (student.region) fullAddr.push(student.region);
                        if (fullAddr.length > 0) {
                            document.getElementById('address').value = fullAddr.join(', ');
                        }
                    }
                    
                    if(document.getElementById('city')) document.getElementById('city').value = student.city_municipality || '';
                    if(document.getElementById('province')) document.getElementById('province').value = student.region || '';
                    if(document.getElementById('programId')) document.getElementById('programId').value = student.program_id || '';
                    if(document.getElementById('programName')) document.getElementById('programName').value = student.program_name || '';
                    
                    showMessage('success', 'Student details imported successfully!');
                    if (msgDiv) {
                        msgDiv.style.color = '#059669';
                        msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> Data loaded successfully!';
                        setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
                    }
                } else {
                    showMessage('error', data.error || 'Failed to fetch enrollment data.');
                    if (msgDiv) {
                        msgDiv.style.color = '#dc2626';
                        msgDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error}`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showMessage('error', 'Server connection error.');
                if (msgDiv) {
                    msgDiv.style.color = '#dc2626';
                    msgDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error contacting server.';
                }
            });
    }

    /**
     * Handle fetching details by reference number
     */
    function handleFetchReference() {
        const refInput = document.getElementById('referenceNumber');
        const msgDiv = document.getElementById('fetchMessage');
        const ref = refInput ? refInput.value.trim() : '';

        if (!ref) {
            showMessage('error', 'Please enter a Reference Number.');
            return;
        }

        if (msgDiv) {
            msgDiv.style.display = 'block';
            msgDiv.style.color = '#6b7280';
            msgDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Fetching data from Database...';
        }

        fetch(`../../api/fetch-reference.php?reference_number=${encodeURIComponent(ref)}`, {
            credentials: 'same-origin'
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const student = data.data || {};

                    if (document.getElementById('firstName')) document.getElementById('firstName').value = student.first_name || '';
                    if (document.getElementById('lastName')) document.getElementById('lastName').value = student.last_name || '';
                    if (document.getElementById('middleName')) document.getElementById('middleName').value = student.middle_name || '';
                    if (document.getElementById('birthdate')) document.getElementById('birthdate').value = student.birth_date || '';
                    if (document.getElementById('email')) document.getElementById('email').value = student.email || '';
                    if (document.getElementById('applicationId')) document.getElementById('applicationId').value = student.application_id || '';

                    if (document.getElementById('contactNumber')) {
                        let phone = student.contact_number || '';
                        if (phone.startsWith('+63')) {
                            phone = phone.substring(3).trim();
                        } else if (phone.length === 11 && phone.startsWith('09')) {
                            phone = phone.substring(1).trim();
                        }
                        document.getElementById('contactNumber').value = phone;
                    }

                    if (document.getElementById('gender')) {
                        document.getElementById('gender').value = student.sex || '';
                    }

                    if (document.getElementById('address')) {
                        let fullAddr = [];
                        if (student.barangay) fullAddr.push(student.barangay);
                        if (student.city_municipality) fullAddr.push(student.city_municipality);
                        if (student.region) fullAddr.push(student.region);
                        if (fullAddr.length > 0) {
                            document.getElementById('address').value = fullAddr.join(', ');
                        }
                    }

                    if (document.getElementById('city')) document.getElementById('city').value = student.city_municipality || '';
                    if (document.getElementById('province')) document.getElementById('province').value = student.region || '';
                    if (document.getElementById('programId')) document.getElementById('programId').value = student.program_id || '';
                    if (document.getElementById('programName')) document.getElementById('programName').value = student.program_name || '';

                    showMessage('success', 'Student details imported successfully!');
                    if (msgDiv) {
                        msgDiv.style.color = '#059669';
                        msgDiv.innerHTML = '<i class="fas fa-check-circle"></i> Data loaded successfully!';
                        setTimeout(() => { msgDiv.style.display = 'none'; }, 3000);
                    }
                } else {
                    showMessage('error', data.error || 'Failed to fetch reference data.');
                    if (msgDiv) {
                        msgDiv.style.color = '#dc2626';
                        msgDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.error || 'Lookup failed'}`;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                showMessage('error', 'Server connection error.');
                if (msgDiv) {
                    msgDiv.style.color = '#dc2626';
                    msgDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> Error contacting server.';
                }
            });
    }

    /**
     * Handle form submission
     */
    function handleFormSubmit(e) {
        e.preventDefault();

        // Get form data
        const formData = new FormData(studentForm);
        const firstName = (document.getElementById('firstName')?.value || '').trim();
        const lastName = (document.getElementById('lastName')?.value || '').trim();
        const email = document.getElementById('email').value.trim();

        // Validation
        if (!firstName || !lastName) {
            showMessage('error', 'First name and last name are required');
            return;
        }

        if (!email || !isValidEmail(email)) {
            showMessage('error', 'Valid email is required');
            return;
        }

        // Check if editing student profile (view mode)
        const isViewMode = studentForm.classList.contains('view-mode');
        
        if (isViewMode) {
            // Student profile - only photo and signature are updated
            handleProfileUpdate();
        } else {
            // Registration form - full form submission
            handleRegistration(formData);
        }
    }

    /**
     * Handle profile update (photo and signature only)
     */
    function handleProfileUpdate() {
        const photoFile = photoInput.files[0];
        const signatureData = signatureCanvas.toDataURL('image/png');

        const formData = new FormData();
        
        if (photoFile) {
            formData.append('photo', photoFile);
        }
        
        if (hasSignature) {
            formData.append('signature', signatureData);
        }

        // TODO: Send to API endpoint
        // POST /api/update-student-profile.php
        console.log('Profile update data:', {
            hasPhoto: photoFile ? true : false,
            hasSignature: hasSignature
        });

        showMessage('success', 'Profile updated successfully!');
    }

    /**
     * Handle student registration
     */
    function handleRegistration(formData) {
        const photoFile = photoInput.files[0];
        let signatureData = signatureCanvas ? signatureCanvas.toDataURL('image/png') : null;
        
        const sigFile = signatureInput ? signatureInput.files[0] : null;
        if (sigFile && signatureImage) {
            signatureData = signatureImage.src; // Data URL from preview
        }

        const firstName = document.getElementById('firstName').value.trim();
        const middleName = document.getElementById('middleName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const fullName = [firstName, middleName, lastName].filter(Boolean).join(' ');

        // Collect form data
        const studentData = {
            fullName: fullName,
            first_name: firstName,
            middle_name: middleName,
            last_name: lastName,
            application_id: parseInt((document.getElementById('applicationId')?.value || '').trim(), 10) || null,
            photo_data: (photoImage && photoImage.src && photoImage.src.startsWith('data:image/')) ? photoImage.src : null,
            email: document.getElementById('email').value.trim(),
            contact_number: (document.getElementById('countryCode').value || '+63') + document.getElementById('contactNumber').value.trim(),
            birthdate: document.getElementById('birthdate').value,
            gender: document.getElementById('gender').value,
            address: document.getElementById('address').value.trim(),
            city: document.getElementById('city').value.trim(),
            province: document.getElementById('province').value.trim(),
            program_id: parseInt(document.getElementById('programId').value, 10),
            admission_date: document.getElementById('admissionDate').value,
            signature_data: signatureData
        };

        // Validate required fields
        if (!studentData.application_id) {
            showMessage('error', 'Please fetch using Reference Number first.');
            return;
        }
        if (!studentData.first_name || !studentData.last_name || !studentData.email || !studentData.program_id) {
            showMessage('error', 'Please fill in all required fields');
            return;
        }

        // Show loading message
        showMessage('info', 'Registering student...');

        // Send to API
        fetch('../../api/students.php?action=create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify(studentData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success || data.ok) {
                const studentId = (data.data && data.data.student_number)
                    || (data.student && data.student.id)
                    || 'N/A';
                showMessage('success', 'Student registered successfully! Student Number: ' + studentId);
                
                // Reset form
                setTimeout(() => {
                    studentForm.reset();
                    clearSignature({ preventDefault: () => {} });
                    // Redirect to student tracking or list
                    window.location.href = 'student-list.php';
                }, 2000);
            } else {
                showMessage('error', data.message || data.error || 'Failed to register student');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'An error occurred while registering student');
        });
    }

    /**
     * Validate email format
     */
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Handle sidebar navigation
     */
    function handleNavigation(e) {
        e.preventDefault();

        // Update active state
        navItems.forEach(item => item.classList.remove('active'));
        this.classList.add('active');

        const section = this.dataset.section;
        console.log('Navigate to section:', section);

        // Load different pages based on section
        switch(section) {
            case 'profile':
                window.location.href = 'student-profile.php';
                break;
            case 'registration':
                window.location.href = 'student-profile-registration.php';
                break;
            case 'academic':
                window.location.href = 'academic-records.php';
                break;
            case 'enrollment':
                window.location.href = 'enrollment-status.php';
                break;
            case 'id':
                window.location.href = 'student-id-generation.php';
                break;
        }
    }

    /**
     * Show message toast
     */
    function showMessage(type, message) {
        const messageText = document.getElementById('messageText');
        messageText.textContent = message;
        messageToast.className = `message-toast show ${type}`;

        setTimeout(() => {
            messageToast.classList.remove('show');
        }, 3000);
    }

    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
