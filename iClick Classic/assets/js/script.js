// Navigation Toggle
const hamburger = document.querySelector('.hamburger');
const navMenu = document.querySelector('.nav-menu');

hamburger.addEventListener('click', () => {
    hamburger.classList.toggle('active');
    navMenu.classList.toggle('active');
});

// Close mobile menu when clicking on a link
document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
    hamburger.classList.remove('active');
    navMenu.classList.remove('active');
}));

// Scroll animations
const fadeElements = document.querySelectorAll('.fade-in');

const fadeInOnScroll = () => {
    fadeElements.forEach(element => {
        const elementTop = element.getBoundingClientRect().top;
        const windowHeight = window.innerHeight;
        
        if (elementTop < windowHeight - 100) {
            element.classList.add('visible');
        }
    });
};

window.addEventListener('scroll', fadeInOnScroll);
fadeInOnScroll(); // Initial check

// Course filtering
const filterButtons = document.querySelectorAll('.filter-btn');
const courseCards = document.querySelectorAll('.course-card');

filterButtons.forEach(button => {
    button.addEventListener('click', () => {
        // Remove active class from all buttons
        filterButtons.forEach(btn => btn.classList.remove('active'));
        // Add active class to clicked button
        button.classList.add('active');
        
        const filterValue = button.getAttribute('data-filter');
        
        courseCards.forEach(card => {
            if (filterValue === 'all' || card.getAttribute('data-category') === filterValue) {
                card.style.display = 'block';
                setTimeout(() => {
                    card.classList.add('animate');
                }, 100);
            } else {
                card.style.display = 'none';
                card.classList.remove('animate');
            }
        });
    });
});

// Image Carousel
const carouselTrack = document.querySelector('.carousel-track');
const carouselSlides = document.querySelectorAll('.carousel-slide');
const prevButton = document.querySelector('.carousel-btn.prev');
const nextButton = document.querySelector('.carousel-btn.next');
let currentIndex = 0;
const slideCount = carouselSlides.length;

function goToSlide(index) {
    if (index < 0) {
        index = slideCount - 1;
    } else if (index >= slideCount) {
        index = 0;
    }
    
    carouselTrack.style.transform = `translateX(-${index * 100}%)`;
    currentIndex = index;
}

prevButton.addEventListener('click', () => {
    goToSlide(currentIndex - 1);
});

nextButton.addEventListener('click', () => {
    goToSlide(currentIndex + 1);
});

// Auto slide
setInterval(() => {
    goToSlide(currentIndex + 1);
}, 5000);

// Lightbox functionality
const lightbox = document.querySelector('.lightbox');
const lightboxContent = document.querySelector('.lightbox-content');
const lightboxClose = document.querySelector('.lightbox-close');

// Open lightbox when clicking on gallery items
document.querySelectorAll('.gallery-item').forEach(item => {
    item.addEventListener('click', () => {
        const mediaSrc = item.getAttribute('data-src');
        const mediaType = item.getAttribute('data-type');
        
        if (mediaType === 'image') {
            lightboxContent.innerHTML = `<img src="${mediaSrc}" alt="Gallery Image">`;
        } else if (mediaType === 'video') {
            lightboxContent.innerHTML = `<video controls autoplay><source src="${mediaSrc}" type="video/mp4">Your browser does not support the video tag.</video>`;
        }
        
        lightbox.style.display = 'flex';
    });
});

// Close lightbox
lightboxClose.addEventListener('click', () => {
    lightbox.style.display = 'none';
});

lightbox.addEventListener('click', (e) => {
    if (e.target === lightbox) {
        lightbox.style.display = 'none';
    }
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input[required], textarea[required]');
    let isValid = true;
    
    inputs.forEach(input => {
        const errorElement = document.getElementById(`${input.id}-error`);
        
        if (!input.value.trim()) {
            if (errorElement) {
                errorElement.textContent = 'This field is required';
            }
            isValid = false;
        } else {
            if (errorElement) {
                errorElement.textContent = '';
            }
            
            // Email validation
            if (input.type === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(input.value)) {
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid email address';
                    }
                    isValid = false;
                }
            }
            
            // Phone validation
            if (input.type === 'tel') {
                const phoneRegex = /^[\+]?[1-9][\d]{0,15}$/;
                if (!phoneRegex.test(input.value.replace(/\D/g, ''))) {
                    if (errorElement) {
                        errorElement.textContent = 'Please enter a valid phone number';
                    }
                    isValid = false;
                }
            }
        }
    });
    
    return isValid;
}

// AJAX form submission
function submitForm(formId, successMessage) {
    const form = document.getElementById(formId);
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.textContent = successMessage;
            form.parentNode.insertBefore(successDiv, form);
            
            // Reset form
            form.reset();
            
            // Remove success message after 5 seconds
            setTimeout(() => {
                successDiv.remove();
            }, 5000);
        } else {
            // Show error message
            alert('There was an error submitting the form. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('There was an error submitting the form. Please try again.');
    });
}

// Initialize forms
document.addEventListener('DOMContentLoaded', () => {
    // Join form
    const joinForm = document.getElementById('join-form');
    if (joinForm) {
        joinForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateForm('join-form')) {
                submitForm('join-form', 'Thank you for your registration! We will contact you soon.');
            }
        });
    }
    
    // Contact form
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (validateForm('contact-form')) {
                submitForm('contact-form', 'Thank you for your message! We will get back to you soon.');
            }
        });
    }
});

// Gallery lightbox enhancement
function initGallery() {
    const galleryItems = document.querySelectorAll('.gallery-item');
    
    galleryItems.forEach(item => {
        item.addEventListener('click', function() {
            const mediaSrc = this.getAttribute('data-src');
            const mediaType = this.getAttribute('data-type');
            const title = this.querySelector('h3')?.textContent || 'Media';
            
            openLightbox(mediaSrc, mediaType, title);
        });
    });
}

function openLightbox(src, type, title) {
    const lightbox = document.querySelector('.lightbox');
    const lightboxContent = document.querySelector('.lightbox-content');
    
    if (type === 'image') {
        lightboxContent.innerHTML = `
            <img src="${src}" alt="${title}" style="max-width: 100%; max-height: 80vh;">
            <div style="text-align: center; margin-top: 15px; color: white;">${title}</div>
        `;
    } else if (type === 'video') {
        lightboxContent.innerHTML = `
            <video controls autoplay style="max-width: 100%; max-height: 80vh;">
                <source src="${src}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div style="text-align: center; margin-top: 15px; color: white;">${title}</div>
        `;
    }
    
    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Close lightbox with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLightbox();
    }
});

function closeLightbox() {
    const lightbox = document.querySelector('.lightbox');
    lightbox.style.display = 'none';
    document.body.style.overflow = 'auto';
    
    // Pause any playing videos
    const video = lightbox.querySelector('video');
    if (video) {
        video.pause();
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initGallery();
    
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add loading states to forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = 'Processing...';
                submitBtn.disabled = true;
            }
        });
    });
});