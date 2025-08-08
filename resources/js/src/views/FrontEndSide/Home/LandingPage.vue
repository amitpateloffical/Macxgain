<template>
  <div class="bg-dark text-white">
    <!-- Header -->
    <header class="navbar navbar-expand-md px-4 py-3 border-bottom border-secondary">
      <div class="header-container">
  <img src="../logo.png" alt="Macxgain Logo" class="login-logo" />
  <h1 class="site-title">Macxgain</h1>
</div>

      <button
        class="navbar-toggler text-white border-0"
        type="button"
        data-bs-toggle="collapse"
        data-bs-target="#navbarNav"
        aria-controls="navbarNav"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <nav class="navbar-nav">
          <a href="#" class="nav-link text-white mx-2">Home</a>
          <a href="/markets" class="nav-link text-white mx-2">Markets</a>
          <a href="#" class="nav-link text-white mx-2">Sign In</a>
          <a href="/admin/login" class="nav-link text-white mx-2">Login</a>
          <a href="#" class="nav-link text-white mx-2">About Us</a>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="text-center py-5 px-3 bg-black">
      <h2 class="display-5 fw-bold mb-3">A Smarter Way to <span class="text-info">Macxgain</span></h2>
      <p class="lead mb-4">Improve your trading skills with our AI tools. Market for everyone, anywhere, anytime.</p>
      <button class="btn btn-primary px-4 py-2">Get Started</button>
    </section>

    <!-- Features Section -->
    <section class="py-5 text-center bg-dark">
      <h3 class="fs-2 mb-4">The best quality <span class="text-info">Market</span></h3>
      <div class="container">
        <div class="row g-4">
          <div class="col-md-4">
            <div class="p-4 border border-secondary rounded bg-black">
              <h5 class="fw-bold mb-2">Best service</h5>
              <p>24/7 customer support with world-class professionals.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-4 border border-info rounded bg-black">
              <h5 class="fw-bold mb-2">High security</h5>
              <p>Military-grade encryption keeps your assets secure.</p>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-4 border border-secondary rounded bg-black">
              <h5 class="fw-bold mb-2">Big community</h5>
              <p>Join thousands of traders and share insights globally.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Traders Section -->
    <section class="py-5 text-center bg-black">
      <h3 class="fs-3 mb-4">Get to know our traders</h3>
      <div class="container">
        <div class="row justify-content-center g-4">
          <div class="col-6 col-sm-4 col-md-3 col-lg-2 mb-3" v-for="(trader, index) in traders" :key="index">
            <div class="bg-dark rounded p-2 h-100">
              <img :src="trader.avatar" class="rounded-circle img-fluid mb-2" alt="trader" />
              <p class="mb-0">{{ trader.name }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-4 bg-dark text-center border-top border-secondary">
      <h4 class="mb-2">Users around all the <span class="text-info">world</span></h4>
      <p>43k+ daily trades · 19+ countries</p>
    </section>

    <!-- Footer -->
    <footer class="text-center py-3 border-top border-secondary">
      <small>&copy; 2025 Market Agency. All rights reserved.</small>
    </footer>
  </div>
</template>
<script>
export default {
  name: "MarketLandingPage",
  data() {
    return {
      traders: [
        { name: "Easter Howard", avatar: "https://i.pravatar.cc/100?img=1" },
        { name: "Theresa White", avatar: "https://i.pravatar.cc/100?img=2" },
        { name: "Adrian McCoy", avatar: "https://i.pravatar.cc/100?img=3" },
        { name: "Albert Flores", avatar: "https://i.pravatar.cc/100?img=4" },
        { name: "Annette Black", avatar: "https://i.pravatar.cc/100?img=5" },
        { name: "Amara McCoy", avatar: "https://i.pravatar.cc/100?img=6" },
        { name: "Eleanor Pena", avatar: "https://i.pravatar.cc/100?img=7" },
        { name: "Dianne Russell", avatar: "https://i.pravatar.cc/100?img=8" },
        { name: "Taylor Wade", avatar: "https://i.pravatar.cc/100?img=9" },
      ],
    };
  },
  mounted() {
    // Dynamically load Bootstrap JS
    const script = document.createElement("script");
    script.src = "https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js";
    script.async = true;
    document.body.appendChild(script);
  },
};
</script>
<style scoped>
body {
  background-color: #0e0e2c;
  overflow-x: hidden;
  min-height: 100%;
}

html {
  scroll-behavior: smooth;
}

        .header-container {
  display: flex;
  align-items: center;
  gap: 12px; /* space between logo and title */
  margin-bottom: 60px; /* adjust spacing below header if needed */
}

.login-logo {
  width: 50px; /* or 120px if large logo is preferred */
  height: auto;
}

.site-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

</style>