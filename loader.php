<div id="kickfit-preloader">
    <div class="loader-content">
        <div class="logo-wrapper">
            <img src="<?php echo (strpos($_SERVER['PHP_SELF'], 'admin') !== false) ? '../image/logoo.png' : 'image/logoo.png'; ?>" 
                 alt="KickFit" 
                 class="premium-logo">
        </div>
        
        <div class="loader-line-container">
            <div class="loader-line-fill"></div>
        </div>
        
        <span class="loading-tagline">AUTHENTIC STREETWEAR</span>
    </div>
</div>

<style>
/* --- Main Overlay --- */
#kickfit-preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    background-color: #ffffff; /* Clean white background */
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 999999;
    /* This creates the smooth "Curtain Lifting" effect */
    transition: transform 0.6s cubic-bezier(0.77, 0, 0.175, 1);
}

/* --- Logo Animation --- */
.logo-wrapper {
    margin-bottom: 25px;
    animation: logoPop 0.8s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.premium-logo {
    height: 65px; /* Adjust based on your logo shape */
    width: auto;
    display: block;
}

@keyframes logoPop {
    0% { opacity: 0; transform: scale(0.8); }
    100% { opacity: 1; transform: scale(1); }
}

/* --- Minimalist Line Loader --- */
.loader-line-container {
    width: 150px;
    height: 3px;
    background: #f2f2f2;
    margin: 0 auto;
    overflow: hidden;
    position: relative;
    border-radius: 10px;
}

.loader-line-fill {
    position: absolute;
    width: 40%;
    height: 100%;
    background: #c0392b; /* Matching your Crimson Red theme */
    animation: slideAcross 1.2s infinite ease-in-out;
}

@keyframes slideAcross {
    0% { left: -50%; }
    100% { left: 110%; }
}

/* --- Tagline Styling --- */
.loading-tagline {
    display: block;
    margin-top: 15px;
    font-family: 'Inter', sans-serif;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 5px; /* Premium spacing */
    color: #2b2d42;
    text-transform: uppercase;
    opacity: 0.6;
}

/* --- Exit Transition --- */
.loader-finish {
    transform: translateY(-100%); /* Slides the white curtain up */
}
</style>

<script>
    // Handles the removal of the loader once the page is ready
    window.addEventListener("load", function() {
        const preloader = document.getElementById("kickfit-preloader");
        
        // 400ms delay ensures the user actually sees the logo 
        // but doesn't feel like the site is slow.
        setTimeout(() => {
            preloader.classList.add("loader-finish");
        }, 400);
    });
</script>