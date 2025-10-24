<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrapify - Sell Your Scrap with Ease</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .slide-item {
            display: none;
            transition: opacity 1s ease-in-out;
            opacity: 0;
        }
        .slide-item.active {
            display: block;
            opacity: 1;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <nav class="container mx-auto flex items-center justify-between p-6 lg:px-8" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="#" class="-m-1.5 p-1.5 flex items-center">
                    <img class="h-8 w-auto mr-2" src="assets/WhatsApp_Image_2025-10-14_at_12.58.19_efe28561-removebg-preview.png" alt="Scrapify Logo">
                    <h1 class="text-3xl font-bold text-green-600">Scrapify</h1>
                </a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="#services" class="text-sm font-semibold leading-6 text-gray-900">Services</a>
                <a href="#rate-list" class="text-sm font-semibold leading-6 text-gray-900">Rate List</a>
                <a href="#about" class="text-sm font-semibold leading-6 text-gray-900">About Us</a>
                <a href="#contact" class="text-sm font-semibold leading-6 text-gray-900">Contact Us</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end">
                <a href="login.php" class="rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">Log in / Sign Up</a>
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero Slider -->
        <section id="hero-slider" class="relative">
            <div class="relative h-[550px] overflow-hidden">
                <div class="slide-item active"><img src="assets/image_371478.jpg" class="absolute block w-full h-full object-cover" alt="Scrap Value Optimized"></div>
                <div class="slide-item"><img src="assets/image_371473.jpg" class="absolute block w-full h-full object-cover" alt="Hassle-Free Logistics"></div>
                <div class="slide-item"><img src="assets/image_371459.jpg" class="absolute block w-full h-full object-cover" alt="Fast & Secure Transactions"></div>
            </div>
             <div class="absolute inset-0 bg-black bg-opacity-40"></div>
             <div class="absolute inset-0 container mx-auto flex items-center px-6 lg:px-8">
                <div class="text-white max-w-2xl">
                    <h2 class="text-4xl sm:text-6xl font-bold">Get the Best Value for Your Scrap, Instantly.</h2>
                    <p class="mt-4 text-lg sm:text-xl">On-time pickups, secure payments, and the highest market rates. Join thousands who are recycling the smart way.</p>
                    <a href="signup.php" class="mt-8 inline-block rounded-md bg-green-600 px-8 py-4 text-base font-semibold text-white shadow-sm hover:bg-green-500">SELL SCRAP NOW</a>
                </div>
            </div>
            <!-- Slider controls -->
            <button id="prev-button" type="button" class="absolute top-0 left-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"><span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50"><svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 1 1 5l4 4"/></svg><span class="sr-only">Previous</span></span></button>
            <button id="next-button" type="button" class="absolute top-0 right-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none"><span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-white/30 group-hover:bg-white/50"><svg class="w-4 h-4 text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/></svg><span class="sr-only">Next</span></span></button>
        </section>

        <!-- Services Section -->
        <section id="services" class="py-16 sm:py-24">
             <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-2xl font-semibold leading-7 text-green-600">Our Services</h2>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">Sell Your Scrap in 3 Easy Steps</p>
                </div>
                <div class="mx-auto mt-16 grid max-w-2xl grid-cols-1 gap-8 text-center sm:mt-20 lg:max-w-none lg:grid-cols-3">
                    <div class="p-8 border border-gray-200 rounded-xl shadow-sm"><div class="flex justify-center items-center h-16 w-16 bg-green-100 rounded-full mx-auto mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg></div><h3 class="text-lg font-semibold text-gray-900">Step 1: Choose Material</h3><p class="mt-4 text-gray-600">Choose the scrap you want to sell from our list of accepted materials.</p></div>
                    <div class="p-8 border border-gray-200 rounded-xl shadow-sm"><div class="flex justify-center items-center h-16 w-16 bg-green-100 rounded-full mx-auto mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg></div><h3 class="text-lg font-semibold text-gray-900">Step 2: Schedule Pickup</h3><p class="mt-4 text-gray-600">Select your preferred date and add the scrap pick-up location.</p></div>
                    <div class="p-8 border border-gray-200 rounded-xl shadow-sm"><div class="flex justify-center items-center h-16 w-16 bg-green-100 rounded-full mx-auto mb-4"><svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /></svg></div><h3 class="text-lg font-semibold text-gray-900">Step 3: Receive Payments</h3><p class="mt-4 text-gray-600">Receive payment in your preferred mode once pickup is complete.</p></div>
                </div>
             </div>
        </section>
        
        <!-- Rate List Section -->
        <section id="rate-list" class="bg-gray-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center"><h2 class="text-2xl font-semibold leading-7 text-green-600">Rate List</h2><p class="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">Transparent and Competitive Pricing</p></div>
                <div class="mt-16 grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4"><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Paper</p><p class="text-gray-500">Rs. 10/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Cardboard</p><p class="text-gray-500">Rs. 8/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Plastic</p><p class="text-gray-500">Rs. 12/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Iron</p><p class="text-gray-500">Rs. 25/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Steel</p><p class="text-gray-500">Rs. 35/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Aluminum</p><p class="text-gray-500">Rs. 90/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Copper</p><p class="text-gray-500">Rs. 400/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">E-Waste</p><p class="text-gray-500">Rs. 15/KG</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Glass Bottles</p><p class="text-gray-500">Rs. 1/Piece</p></div><div class="text-center p-4 bg-white rounded-lg shadow-sm"><p class="font-medium">Tires</p><p class="text-gray-500">Rs. 5/KG</p></div></div>
            </div>
        </section>

        <!-- About Us Section -->
        <section id="about" class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                 <div class="mx-auto max-w-2xl lg:text-center"><h2 class="text-2xl font-semibold leading-7 text-green-600">About Us</h2></div>
                <div class="mt-8 grid lg:grid-cols-2 gap-12 items-center">
                    <div><h3 class="text-3xl font-bold tracking-tight text-gray-900">Your trusted partner in sustainable scrap management.</h3><p class="mt-4 text-gray-600">We are dedicated to revolutionizing the waste management industry by providing an efficient, transparent, and eco-friendly solution for individuals and businesses to manage their scrap materials responsibly.</p></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8"><div><h4 class="font-semibold text-gray-900">Our Vision</h4><p class="mt-2 text-gray-600">To be a leader in the waste management industry by promoting a circular economy that reduces landfill waste and fosters a cleaner, more sustainable future.</p></div><div><h4 class="font-semibold text-gray-900">Our Mission</h4><p class="mt-2 text-gray-600">To provide a seamless digital platform that connects scrap generators with certified recyclers, ensuring fair value for all materials.</p></div><div><h4 class="font-semibold text-gray-900">Our Goal</h4><p class="mt-2 text-gray-600">To make responsible scrap disposal effortless, bridging the gap between waste generators and recyclers to ensure maximum resource recovery.</p></div><div><h4 class="font-semibold text-gray-900">Our Beliefs</h4><p class="mt-2 text-gray-600">We believe in diverting waste from landfills and providing exceptional service, ensuring a cleaner and more sustainable future for everyone.</p></div></div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer id="contact" class="bg-gray-900 text-white py-12">
        <div class="mx-auto max-w-7xl px-6 lg:px-8"><div class="flex flex-wrap justify-between gap-8"><div><h2 class="text-2xl font-bold">Scrapify</h2><p class="mt-2 text-gray-400">A smarter way to recycle.</p></div><div><h3 class="font-semibold">Contact Us</h3><p class="mt-2 text-gray-400">contact@scrapify.com</p><p class="text-gray-400">+91 12345 67890</p></div></div><div class="mt-8 border-t border-gray-700 pt-8 text-center text-sm text-gray-400">&copy; 2025 Scrapify. All rights reserved.</div></div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slides = document.querySelectorAll('.slide-item');
            const prevButton = document.getElementById('prev-button');
            const nextButton = document.getElementById('next-button');
            let currentSlide = 0;
            let slideInterval;
            function showSlide(n) { slides.forEach(slide => slide.classList.remove('active')); currentSlide = (n + slides.length) % slides.length; slides[currentSlide].classList.add('active'); }
            function nextSlide() { showSlide(currentSlide + 1); }
            function startSlideShow() { clearInterval(slideInterval); slideInterval = setInterval(nextSlide, 5000); }
            prevButton.addEventListener('click', () => { showSlide(currentSlide - 1); startSlideShow(); });
            nextButton.addEventListener('click', () => { nextSlide(); startSlideShow(); });
            startSlideShow();
        });
    </script>
</body>
</html>

