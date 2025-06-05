<html class="scroll-smooth" lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Student Dormitory Management System
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <script>
   tailwind.config = {
    darkMode: 'class',
    theme: {
      extend: {
        fontFamily: {
          inter: ['Inter', 'sans-serif'],
        },
      },
    },
  }
  </script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&amp;display=swap" rel="stylesheet"/>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
  <style>
   body {
      font-family: 'Inter', sans-serif;
    }
  </style>
 </head>
 <body class="bg-gray-50 text-gray-800 dark:bg-gray-900 dark:text-gray-300 transition-colors duration-500">
  <!-- Navbar -->
  <nav class="bg-white dark:bg-gray-800 shadow-md fixed w-full z-30 transition-colors duration-500">
   <div class="max-w-7xl mx-auto px-6 lg:px-8">
    <div class="flex justify-between items-center h-16">
     <a class="flex items-center space-x-2" href="#">
      <img alt="Logo of Student Dormitory Management System, stylized letters SD in blue and white" class="h-10 w-10 rounded-md" height="40" src="https://storage.googleapis.com/a1aa/image/2ead4dae-531d-47f1-80f6-28396c9205a2.jpg" width="40"/>
      <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
       DormManage
      </span>
     </a>
     <div class="hidden md:flex space-x-8 font-semibold text-gray-700 dark:text-gray-300">
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#features">
       Features
      </a>
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#rooms">
       Rooms
      </a>
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#testimonials">
       Testimonials
      </a>
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#about">
       About
      </a>
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#location">
       Location
      </a>
      <a class="hover:text-blue-600 dark:hover:text-blue-400 transition" href="#contact">
       Contact
      </a>
     </div>
     <div class="hidden md:flex space-x-4">
      <a class="px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-600 hover:text-white transition" href="login.php">
       Login
      </a>
      <a class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition" href="signup.php">
       Sign Up
      </a>
      <button aria-label="Toggle dark mode" class="ml-4 text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600 rounded" id="dark-mode-toggle" title="Toggle Dark Mode" type="button">
       <i class="fas fa-moon fa-lg" id="dark-mode-icon">
       </i>
      </button>
     </div>
     <button aria-label="Toggle menu" class="md:hidden text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-600 rounded" id="mobile-menu-button">
      <i class="fas fa-bars fa-lg">
      </i>
     </button>
    </div>
   </div>
   <div class="md:hidden bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 hidden" id="mobile-menu">
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="#features">
     Features
    </a>
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="#rooms">
     Rooms
    </a>
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="#testimonials">
     Testimonials
    </a>
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="#about">
     About
    </a>
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="#location">
     Location
    </a>
    <a class="block px-6 py-3 border-b border-gray-200 dark:border-gray-700 text-blue-600 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900" href="login.php">
     Login
    </a>
    <a class="block px-6 py-3 text-white bg-blue-600 font-semibold hover:bg-blue-700" href="signup.php">
     Sign Up
    </a>
    <button aria-label="Toggle dark mode" class="w-full text-left px-6 py-3 border-t border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold hover:bg-blue-50 dark:hover:bg-blue-900 focus:outline-none" id="mobile-dark-mode-toggle" type="button">
     <i class="fas fa-moon mr-2">
     </i>
     Toggle Dark Mode
    </button>
   </div>
  </nav>
  <!-- Hero Section -->
  <header class="pt-24 bg-gradient-to-r from-blue-600 to-blue-400 dark:from-blue-900 dark:to-blue-700 text-white" id="home">
   <div class="max-w-7xl mx-auto px-6 lg:px-8 flex flex-col-reverse md:flex-row items-center md:space-x-12 py-20">
    <div class="w-full md:w-1/2 text-center md:text-left">
     <h1 class="text-4xl sm:text-5xl font-extrabold leading-tight mb-6">
      Simplify Your Student Dormitory Management
     </h1>
     <p class="text-lg sm:text-xl mb-8 opacity-90">
      Manage rooms, residents, maintenance, and payments all in one intuitive platform.
     </p>
     <div class="space-x-4">
      <a class="inline-block px-8 py-3 bg-white text-blue-600 font-semibold rounded-md shadow-md hover:bg-gray-100 transition" href="#signup">
       Get Started
      </a>
      <a class="inline-block px-8 py-3 border border-white text-white font-semibold rounded-md hover:bg-white hover:text-blue-600 transition" href="#features">
       Learn More
      </a>
     </div>
    </div>
    <div class="w-full md:w-1/2 mb-12 md:mb-0">
     <img alt="Illustration of a student dormitory with students interacting, rooms, and digital management icons" class="mx-auto rounded-lg shadow-lg" height="400" loading="lazy" src="https://storage.googleapis.com/a1aa/image/1bb8849d-c1a0-4f45-4c54-db6474b69174.jpg" width="600"/>
    </div>
   </div>
  </header>
  <!-- Features Section -->
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-20 bg-white dark:bg-gray-800" id="features">
   <h2 class="text-3xl font-extrabold text-center text-gray-900 dark:text-gray-100 mb-12">
    Features That Make Dorm Life Easier
   </h2>
   <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10 max-w-6xl mx-auto">
    <div class="flex flex-col items-center text-center space-y-4 px-4">
     <div aria-hidden="true" class="p-5 bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400 rounded-full shadow-md text-4xl">
      <i class="fas fa-door-open">
      </i>
     </div>
     <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
      Room Allocation
     </h3>
     <p class="text-gray-600 dark:text-gray-300">
      Easily assign and manage rooms for students with real-time availability.
     </p>
    </div>
    <div class="flex flex-col items-center text-center space-y-4 px-4">
     <div aria-hidden="true" class="p-5 bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400 rounded-full shadow-md text-4xl">
      <i class="fas fa-users">
      </i>
     </div>
     <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
      Resident Profiles
     </h3>
     <p class="text-gray-600 dark:text-gray-300">
      Maintain detailed profiles for each resident including contact and emergency info.
     </p>
    </div>
    <div class="flex flex-col items-center text-center space-y-4 px-4">
     <div aria-hidden="true" class="p-5 bg-purple-100 text-purple-600 dark:bg-purple-900 dark:text-purple-400 rounded-full shadow-md text-4xl">
      <i class="fas fa-file-invoice-dollar">
      </i>
     </div>
     <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
      Payment Tracking
     </h3>
     <p class="text-gray-600 dark:text-gray-300">
      Monitor rent payments and generate invoices with ease.
     </p>
    </div>
   </div>
  </section>
  <!-- Rooms Showcase Section -->
  <section class="bg-gray-50 dark:bg-gray-900 py-20 px-6 lg:px-8 max-w-7xl mx-auto" id="rooms">
   <h2 class="text-3xl font-extrabold text-center text-gray-900 dark:text-gray-100 mb-12">
    Explore Our Dorm Rooms
   </h2>
   <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-10 max-w-6xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
     <img alt="Single dorm room with a bed, desk, chair, and window letting in natural light" class="w-full h-56 object-cover" height="250" loading="lazy" src="https://storage.googleapis.com/a1aa/image/788ef91a-53dd-40a5-a318-6097d2e82d2a.jpg" width="400"/>
     <div class="p-6">
      <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
       Single Room
      </h3>
      <p class="text-gray-600 dark:text-gray-300 mb-4">
       Cozy private room with study desk and ample storage.
      </p>
      <span class="inline-block bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-400 px-3 py-1 rounded-full text-sm font-semibold">
       Available
      </span>
     </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
     <img alt="Double dorm room with two beds, shared desk, and wardrobe" class="w-full h-56 object-cover" height="250" loading="lazy" src="https://storage.googleapis.com/a1aa/image/05cb8872-7d4b-49d4-53f2-f249850c3734.jpg" width="400"/>
     <div class="p-6">
      <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
       Double Room
      </h3>
      <p class="text-gray-600 dark:text-gray-300 mb-4">
       Spacious room shared by two students with separate beds.
      </p>
      <span class="inline-block bg-green-100 text-green-600 dark:bg-green-900 dark:text-green-400 px-3 py-1 rounded-full text-sm font-semibold">
       Limited
      </span>
     </div>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden hover:shadow-2xl transition-shadow duration-300">
     <img alt="Suite dorm room with private bathroom, living area, and modern furniture" class="w-full h-56 object-cover" height="250" loading="lazy" src="https://storage.googleapis.com/a1aa/image/56823748-e73f-487e-d859-d66b03d96006.jpg" width="400"/>
     <div class="p-6">
      <h3 class="text-xl font-semibold mb-2 text-gray-900 dark:text-gray-100">
       Suite Room
      </h3>
      <p class="text-gray-600 dark:text-gray-300 mb-4">
       Premium suite with private bathroom and lounge space.
      </p>
      <span class="inline-block bg-yellow-100 text-yellow-600 dark:bg-yellow-900 dark:text-yellow-400 px-3 py-1 rounded-full text-sm font-semibold">
       Coming Soon
      </span>
     </div>
    </div>
   </div>
  </section>
  <!-- Testimonials Section -->
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-20 bg-white dark:bg-gray-800" id="testimonials">
   <h2 class="text-3xl font-extrabold text-center text-gray-900 dark:text-gray-100 mb-12">
    What Our Students Say
   </h2>
   <div class="max-w-5xl mx-auto grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-8 shadow-md flex flex-col justify-between">
     <div>
      <p class="text-gray-700 dark:text-gray-300 mb-6 italic">
       "DormManage made moving into the dorm so easy! The room allocation was quick and the maintenance requests get resolved fast."
      </p>
     </div>
     <div class="flex items-center space-x-4">
      <img alt="Portrait of a smiling female student with short black hair wearing a blue shirt" class="h-14 w-14 rounded-full object-cover" height="56" loading="lazy" src="https://storage.googleapis.com/a1aa/image/a0b3b4f0-53bf-4dc2-4f3c-8f571fd4f11a.jpg" width="56"/>
      <div>
       <p class="font-semibold text-gray-900 dark:text-gray-100">
        Maria Santos
       </p>
       <p class="text-sm text-gray-600 dark:text-gray-400">
        Computer Science Student
       </p>
      </div>
     </div>
    </div>
    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-8 shadow-md flex flex-col justify-between">
     <div>
      <p class="text-gray-700 dark:text-gray-300 mb-6 italic">
       "I love how I can track my payments and submit maintenance requests online. It saves me so much time!"
      </p>
     </div>
     <div class="flex items-center space-x-4">
      <img alt="Portrait of a smiling male student with curly hair wearing glasses and a red hoodie" class="h-14 w-14 rounded-full object-cover" height="56" loading="lazy" src="https://storage.googleapis.com/a1aa/image/e286fbad-0286-456c-fe31-f7b9e09ee4bc.jpg" width="56"/>
      <div>
       <p class="font-semibold text-gray-900 dark:text-gray-100">
        John Reyes
       </p>
       <p class="text-sm text-gray-600 dark:text-gray-400">
        Business Administration Student
       </p>
      </div>
     </div>
    </div>
    <div class="bg-gray-100 dark:bg-gray-700 rounded-lg p-8 shadow-md flex flex-col justify-between">
     <div>
      <p class="text-gray-700 dark:text-gray-300 mb-6 italic">
       "The platform is very user-friendly and the staff is very responsive. Highly recommend DormManage!"
      </p>
     </div>
     <div class="flex items-center space-x-4">
      <img alt="Portrait of a smiling female student with long brown hair wearing a white blouse" class="h-14 w-14 rounded-full object-cover" height="56" loading="lazy" src="https://storage.googleapis.com/a1aa/image/f86b9d1f-67ab-4c5c-8cf5-e3f28f389e6c.jpg" width="56"/>
      <div>
       <p class="font-semibold text-gray-900 dark:text-gray-100">
        Angela Cruz
       </p>
       <p class="text-sm text-gray-600 dark:text-gray-400">
        Engineering Student
       </p>
      </div>
     </div>
    </div>
   </div>
  </section>
  <!-- About Section -->
  <section class="max-w-7xl mx-auto px-6 lg:px-8 py-20 bg-white dark:bg-gray-800" id="about">
   <div class="flex flex-col md:flex-row items-center md:space-x-16 max-w-6xl mx-auto">
    <div class="md:w-1/2 mb-12 md:mb-0">
     <img alt="Team of dormitory managers and students collaborating in a modern office environment" class="rounded-lg shadow-lg w-full object-cover" height="400" loading="lazy" src="https://storage.googleapis.com/a1aa/image/072eca32-0ba7-474f-7d87-11e97fdb20aa.jpg" width="600"/>
    </div>
    <div class="md:w-1/2">
     <h2 class="text-3xl font-extrabold text-gray-900 dark:text-gray-100 mb-6">
      About DormManage
     </h2>
     <p class="text-gray-700 dark:text-gray-300 mb-6 leading-relaxed">
      DormManage is a comprehensive student dormitory management system designed to streamline the daily operations of dormitories. From room assignments to maintenance tracking, our platform helps administrators and students stay connected and organized.
     </p>
     <p class="text-gray-700 dark:text-gray-300 leading-relaxed">
      Our mission is to create a seamless living experience for students while reducing administrative overhead for dormitory staff.
     </p>
    </div>
   </div>
  </section>
  <!-- Location Section -->
  <section class="bg-gray-50 dark:bg-gray-900 py-20 px-6 lg:px-8 max-w-7xl mx-auto" id="location">
   <h2 class="text-3xl font-extrabold text-center text-gray-900 dark:text-gray-100 mb-12">
    Our Dormitory Location
   </h2>
   <div class="max-w-5xl mx-auto rounded-lg overflow-hidden shadow-lg">
    <iframe width="100%" height="400" frameborder="0" scrolling="no" 
  marginheight="0" marginwidth="0" 
  src="https://www.openstreetmap.org/export/embed.html?bbox=123.3500%2C13.4000%2C123.3800%2C13.4200&layer=mapnik&marker=13.41%2C123.36">
</iframe>
<br/>
<small>
  <a href="https://www.openstreetmap.org/?mlat=13.41&amp;mlon=123.36#map=16/13.41/123.36">
    View Larger Map
  </a>
</small>
   </div>
   <p class="mt-6 text-center text-gray-700 dark:text-gray-300 max-w-3xl mx-auto">
    Our dormitory is conveniently located in Nabua, Camarines Sur, close to major landmarks and public transportation, making it easy for students to commute and enjoy their stay.
   </p>
  </section>
  <!-- Contact Section -->
  <section class="bg-blue-600 dark:bg-blue-900 py-20 px-6 lg:px-8 text-white" id="contact">
   <div class="max-w-4xl mx-auto">
    <h2 class="text-3xl font-extrabold mb-8 text-center">
     Contact Us
    </h2>
    <form class="bg-blue-500 bg-opacity-20 rounded-lg p-8 max-w-2xl mx-auto space-y-6" id="contact-form" novalidate="">
     <div>
      <label class="block mb-2 font-semibold" for="name">
       Name
      </label>
      <input class="w-full rounded-md px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-white" id="name" name="name" placeholder="Your full name" required="" type="text"/>
      <p class="mt-1 text-sm text-red-300 hidden" id="name-error">
       Please enter your name.
      </p>
     </div>
     <div>
      <label class="block mb-2 font-semibold" for="email">
       Email
      </label>
      <input class="w-full rounded-md px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-white" id="email" name="email" placeholder="you@example.com" required="" type="email"/>
      <p class="mt-1 text-sm text-red-300 hidden" id="email-error">
       Please enter a valid email address.
      </p>
     </div>
     <div>
      <label class="block mb-2 font-semibold" for="message">
       Message
      </label>
      <textarea class="w-full rounded-md px-4 py-3 text-gray-900 focus:outline-none focus:ring-2 focus:ring-white" id="message" name="message" placeholder="Your message" required="" rows="4"></textarea>
      <p class="mt-1 text-sm text-red-300 hidden" id="message-error">
       Please enter a message.
      </p>
     </div>
     <button class="w-full bg-white text-blue-600 font-semibold rounded-md py-3 hover:bg-gray-100 transition" type="submit">
      Send Message
     </button>
     <p class="mt-4 text-green-300 font-semibold text-center hidden" id="form-success">
      Thank you for contacting us! We will get back to you soon.
     </p>
    </form>
   </div>
  </section>
  <!-- Footer -->
  <footer class="bg-gray-900 dark:bg-gray-800 text-gray-400 dark:text-gray-400 py-10 px-6 lg:px-8 transition-colors duration-500">
   <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-6 md:space-y-0">
    <div class="flex items-center space-x-3">
     <img alt="Logo of Student Dormitory Management System, stylized letters SD in white on dark background" class="h-10 w-10 rounded-md" height="40" src="https://storage.googleapis.com/a1aa/image/3b5bf76a-99eb-4d99-4a10-35dcb3945671.jpg" width="40"/>
     <span class="text-white font-bold text-lg">
      DormManage
     </span>
    </div>
    <p class="text-sm">
     © 2024 DormManage. All rights reserved.
    </p>
    <div class="flex space-x-6 text-gray-400 dark:text-gray-400">
     <a aria-label="Facebook" class="hover:text-white transition" href="https://facebook.com" rel="noopener noreferrer" target="_blank">
      <i class="fab fa-facebook fa-lg">
      </i>
     </a>
     <a aria-label="Twitter" class="hover:text-white transition" href="https://twitter.com" rel="noopener noreferrer" target="_blank">
      <i class="fab fa-twitter fa-lg">
      </i>
     </a>
     <a aria-label="Instagram" class="hover:text-white transition" href="https://instagram.com" rel="noopener noreferrer" target="_blank">
      <i class="fab fa-instagram fa-lg">
      </i>
     </a>
     <a aria-label="LinkedIn" class="hover:text-white transition" href="https://linkedin.com" rel="noopener noreferrer" target="_blank">
      <i class="fab fa-linkedin fa-lg">
      </i>
     </a>
    </div>
   </div>
  </footer>
  <script>
   // Mobile menu toggle
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    menuButton.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      menuButton.querySelector('i').classList.toggle('fa-bars');
      menuButton.querySelector('i').classList.toggle('fa-times');
    });

    // Contact form validation and submission simulation
    const form = document.getElementById('contact-form');
    const nameInput = form.querySelector('#name');
    const emailInput = form.querySelector('#email');
    const messageInput = form.querySelector('#message');

    const nameError = form.querySelector('#name-error');
    const emailError = form.querySelector('#email-error');
    const messageError = form.querySelector('#message-error');
    const formSuccess = form.querySelector('#form-success');

    function validateEmail(email) {
      // Simple email regex
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    form.addEventListener('submit', (e) => {
      e.preventDefault();

      let valid = true;

      if (!nameInput.value.trim()) {
        nameError.classList.remove('hidden');
        valid = false;
      } else {
        nameError.classList.add('hidden');
      }

      if (!validateEmail(emailInput.value.trim())) {
        emailError.classList.remove('hidden');
        valid = false;
      } else {
        emailError.classList.add('hidden');
      }

      if (!messageInput.value.trim()) {
        messageError.classList.remove('hidden');
        valid = false;
      } else {
        messageError.classList.add('hidden');
      }

      if (valid) {
        // Simulate form submission
        formSuccess.classList.remove('hidden');
        form.reset();
        setTimeout(() => {
          formSuccess.classList.add('hidden');
        }, 5000);
      }
    });

    // Dark mode toggle
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const mobileDarkModeToggle = document.getElementById('mobile-dark-mode-toggle');
    const darkModeIcon = document.getElementById('dark-mode-icon');

    function setDarkMode(enabled) {
      if (enabled) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('darkMode', 'true');
        darkModeIcon.classList.remove('fa-moon');
        darkModeIcon.classList.add('fa-sun');
      } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('darkMode', 'false');
        darkModeIcon.classList.remove('fa-sun');
        darkModeIcon.classList.add('fa-moon');
      }
    }

    // Initialize dark mode based on preference or localStorage
    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const storedDarkMode = localStorage.getItem('darkMode');
    if (storedDarkMode === 'true' || (storedDarkMode === null && prefersDark)) {
      setDarkMode(true);
    } else {
      setDarkMode(false);
    }

    darkModeToggle.addEventListener('click', () => {
      setDarkMode(!document.documentElement.classList.contains('dark'));
    });

    mobileDarkModeToggle.addEventListener('click', () => {
      setDarkMode(!document.documentElement.classList.contains('dark'));
      // Close mobile menu after toggling dark mode
      mobileMenu.classList.add('hidden');
      menuButton.querySelector('i').classList.remove('fa-times');
      menuButton.querySelector('i').classList.add('fa-bars');
    });
  </script>
 </body>
</html>
