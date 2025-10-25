<?php include 'header.php'; ?>

<!-- Hero Section with Carousel -->
<main class="hero-section">
    <div class="carousel">
        <div class="slides">
            <!-- === UPDATED THESE LINES === -->
            <img src="assets/scrapvalue1.jpg" class="slide" alt="Scrap Image 1">
            <img src="assets/scrapvalue2.jpg" class="slide" alt="Scrap Image 2">
            <img src="assets/scrapvalue3.jpg" class="slide" alt="Scrap Image 3">
        </div>
        <button class="arrow left" id="prev">&#10094;</button>
        <button class="arrow right" id="next">&#10095;</button>
    </div>
</main>

<!-- Services Section -->
<section id="services" class="services-section">
    <div class="container">
        <div class="section-header">
            <h2 class="subtitle">Our Services</h2>
            <h1 class="title">Sell Your Scrap in 3 Easy Steps</h1>
        </div>

        <div class="steps-container">
            <div class="step-card">
                <div class="icon-circle">
                    <!-- Icon: Document Check -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.125 2.25h-4.5c-1.125 0-2.062.938-2.062 2.063v15.375c0 1.125.938 2.063 2.063 2.063h12.75c1.125 0 2.063-.938 2.063-2.063V8.625c0-.621-.25-1.172-.659-1.581l-5.625-5.625A2.063 2.063 0 0 0 10.125 2.25Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 2.25v5.625h5.625" />
                    </svg>
                </div>
                <h3>Step 1: Choose Material</h3>
                <p>Choose the scrap you want to sell from our list of accepted materials.</p>
            </div>

            <div class="step-card">
                <div class="icon-circle">
                    <!-- Icon: Truck -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path d="M9 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                        <path d="M19 17a2 2 0 1 1-4 0 2 2 0 0 1 4 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 17H6V6h10v4l4 1v4H13Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6h4v4h-4V6Z" />
                    </svg>
                </div>
                <h3>Step 2: Schedule Pickup</h3>
                <p>Select your preferred date and add the scrap pick-up location.</p>
            </div>

            <div class="step-card">
                <div class="icon-circle">
                    <!-- Icon: Banknotes -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.75A.75.75 0 0 1 3 4.5h.75m0 0H21m-12 12.75h.008v.008H9v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v.008h.008v-.008H12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm1.125 4.5h.008v.008h-.008V19.5Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 12v-1.5a.75.75 0 0 0-.75-.75h-3.75a.75.75 0 0 0-.75.75v1.5m0 0H3m18 0v-1.5a.75.75 0 0 0-.75-.75h-3.75a.75.75 0 0 0-.75.75v1.5m0 0v.75A.75.75 0 0 1 18 13.5h.75m0 0h-.75a.75.75 0 0 1-.75-.75V12m0 0v.75a.75.75 0 0 1-.75.75h-3.75a.75.75 0 0 1-.75-.75V12m0 0h-3.75a.75.75 0 0 0-.75.75v.75A.75.75 0 0 1 9 13.5h.75m0 0h-.75a.75.75 0 0 1-.75-.75V12m0 0H3.75m0 0v.75A.75.75 0 0 1 3 13.5h.75m0 0H3" />
                    </svg>
                </div>
                <h3>Step 3: Receive Payments</h3>
                <p>Receive payment in your preferred mode once pickup is complete.</p>
            </div>
        </div>
        
        <div class="services-cta">
            <a href="signup.php" class="btn btn-solid" style="padding: 0.8rem 2.5rem; font-size: 1.1rem;">Sell</a>
        </div>
        
    </div>
    <hr>
</section>

<!-- About Section -->
<section id="about" class="about-section">
    <div class="container">
        <div class="section-header">
            <h2 class="subtitle">About Us</h2>
            <h1 class="title">Who We Are</h1>
        </div>
        <div class="about-container">
            <div class="about-left">
                <h3>Your trusted partner in sustainable scrap management.</h3>
                <p>
                    We are dedicated to revolutionizing the waste management industry by providing an 
                    efficient, transparent, and eco-friendly solution for individuals and businesses 
                    to manage their scrap materials responsibly.
                </p>
            </div>
            <div class="about-right">
                <div class="about-item">
                    <h4>Our Vision</h4>
                    <p>
                        To be a leader in the waste management industry by promoting a circular economy 
                        that reduces landfill waste and fosters a cleaner, more sustainable future.
                    </p>
                </div>
                <div class="about-item">
                    <h4>Our Mission</h4>
                    <p>
                        To provide a seamless digital platform that connects scrap generators with certified 
                        recyclers, ensuring fair value for all materials.
                    </p>
                </div>
                <div class="about-item">
                    <h4>Our Goal</h4>
                    <p>
                        To make responsible scrap disposal effortless, bridging the gap between waste generators 
                        and recyclers to ensure maximum resource recovery.
                    </p>
                </div>
                <div class="about-item">
                    <h4>Our Beliefs</h4>
                    <p>
                        We believe in diverting waste from landfills and providing exceptional service, ensuring 
                        a cleaner and more sustainable future for everyone.
                    </p>
                </div>
            </div>
        </div>
    </div>
    <hr>
</section>

<!-- Carousel JavaScript -->
<script>
    const slidesContainer = document.querySelector(".slides");
    // Only run carousel script if the carousel exists on the page
    if (slidesContainer) {
        const slides = document.querySelectorAll(".slide");
        const totalSlides = slides.length;
        const nextBtn = document.getElementById("next");
        const prevBtn = document.getElementById("prev");
        let currentIndex = 0;

        function showSlide(index) {
            if (index < 0) {
                currentIndex = totalSlides - 1;
            } else if (index >= totalSlides) {
                currentIndex = 0;
            } else {
                currentIndex = index;
            }
            slidesContainer.style.transform = `translateX(-${currentIndex * 100}%)`;
        }

        nextBtn.addEventListener("click", () => {
            showSlide(currentIndex + 1);
        });

        prevBtn.addEventListener("click", () => {
            showSlide(currentIndex - 1);
        });

        let slideInterval = setInterval(() => {
            showSlide(currentIndex + 1);
        }, 4000);

        const carousel = document.querySelector('.carousel');
        carousel.addEventListener('mouseenter', () => clearInterval(slideInterval));
        carousel.addEventListener('mouseleave', () => {
            slideInterval = setInterval(() => {
                showSlide(currentIndex + 1);
            }, 4000);
        });

        document.addEventListener("keydown", (e) => {
            if (e.key === "ArrowRight") showSlide(currentIndex + 1);
            if (e.key === "ArrowLeft") showSlide(currentIndex - 1);
        });
    }
</script>

<?php include 'footer.php'; ?>