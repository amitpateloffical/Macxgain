<template>
  <div class="landing-page">
    <!-- Trading Background Animation -->
    <div class="trading-background">
      <div class="stock-chart chart-1">
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
      </div>
      <div class="stock-chart chart-2">
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
      </div>
      <div class="stock-chart chart-3">
        <div class="candlestick up"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
        <div class="candlestick up"></div>
        <div class="candlestick down"></div>
      </div>
      
      <div class="trading-indicators">
        <div class="indicator">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
        <div class="indicator">
          <div class="line"></div>
          <div class="dot"></div>
        </div>
      </div>
      
      <div class="price-tickers">
        <div class="ticker">
          <span class="symbol">AAPL</span>
          <span class="price up">$185.42</span>
          <span class="change up">+2.3%</span>
        </div>
        <div class="ticker">
          <span class="symbol">TSLA</span>
          <span class="price down">$248.50</span>
          <span class="change down">-1.2%</span>
        </div>
        <div class="ticker">
          <span class="symbol">NVDA</span>
          <span class="price up">$485.09</span>
          <span class="change up">+3.8%</span>
        </div>
      </div>
      
      <div class="floating-numbers">
        <div class="number">+15.2%</div>
        <div class="number">$2.4M</div>
        <div class="number">+8.7%</div>
        <div class="number">$1.8M</div>
      </div>
    </div>

    <!-- Header -->
    <header class="header">
      <div class="header-container">
        <div class="logo-section">
          <img src="../logo.png" alt="Macxgain Logo" class="logo" />
          <h1 class="brand-name">Macxgain</h1>
        </div>
        
        <nav class="nav-menu">
          <a href="#" class="nav-link">Home</a>
          <a href="#" class="nav-link">About Us</a>
          <a href="/markets" class="nav-link">Markets</a>
          <a href="#" class="nav-link">Tools</a>
          <a href="#" class="nav-link">Blogs</a>
          <a href="#" class="nav-link">Download APK</a>
        </nav>
        
        <div class="auth-buttons">
          <a href="/admin/login" class="btn btn-outline">Login</a>
          <a href="/admin/register" class="btn btn-primary">Register</a>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-container">
        <div class="hero-content">
          <div class="trust-badge">
            <span class="badge-icon">🎯</span>
            <span>20% Discount & Get 24/7 Free Assistance</span>
          </div>
          
          <h1 class="hero-title">
            Account that Suits Your 
            <span class="highlight">Trading Style</span>
          </h1>
          
          <p class="hero-subtitle">
            In a fast moving market, choose a stable broker. Macxgain provides 
            everything you need to trade Futures & Options in one place.
          </p>
          
          <div class="hero-actions">
            <a href="/admin/login" class="btn btn-primary btn-large">Start Trading Now</a>
            <a href="#" class="btn btn-outline btn-large">Download App</a>
          </div>
          
          <div class="hero-stats">
            <div class="stat">
              <span class="stat-number">4.7M+</span>
              <span class="stat-label">Installations</span>
            </div>
            <div class="stat">
              <span class="stat-number">24/7</span>
              <span class="stat-label">Support</span>
            </div>
            <div class="stat">
              <span class="stat-number">50+</span>
              <span class="stat-label">Markets</span>
            </div>
          </div>
        </div>
        
        <div class="hero-visual">
          <div class="trading-dashboard">
            <div class="chart-window">
              <div class="chart-header">
                <span class="chart-title">AAPL - Apple Inc.</span>
                <span class="chart-price up">$185.42 +2.3%</span>
              </div>
              <div class="chart-body">
                <div class="price-line"></div>
                <div class="volume-bars"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Account Types Section -->
    <section class="account-types">
      <div class="container">
        <div class="section-header">
          <h2>Futures & Options Trading Accounts</h2>
          <p>Choose the account that matches your trading goals and experience level</p>
        </div>
        
        <div class="account-cards">
          <div class="account-card">
            <div class="card-header">
              <h3>Standard Account</h3>
              <div class="card-badge">Popular</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Low minimum deposit</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Basic trading tools</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>24/7 support</span>
              </div>
            </div>
            <a href="/admin/login" class="btn btn-primary">Open Account</a>
          </div>
          
          <div class="account-card featured">
            <div class="card-header">
              <h3>Commission Account</h3>
              <div class="card-badge">Best Value</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Advanced trading tools</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Lower commission rates</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Priority support</span>
              </div>
            </div>
            <a href="/admin/login" class="btn btn-primary">Open Account</a>
          </div>
          
          <div class="account-card">
            <div class="card-header">
              <h3>STP Pro Account</h3>
              <div class="card-badge">Professional</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Professional tools</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Direct market access</span>
              </div>
              <div class="feature">
                <span class="feature-icon">✅</span>
                <span>Dedicated manager</span>
              </div>
            </div>
            <a href="/admin/login" class="btn btn-primary">Open Account</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Trading Tools Section -->
    <section class="trading-tools">
      <div class="container">
        <div class="section-header">
          <h2>Tools for Every Trader</h2>
          <p>Professional trading tools to enhance your trading experience</p>
        </div>
        
        <div class="tools-grid">
          <div class="tool-card">
            <div class="tool-icon">📊</div>
            <h3>Margin Calculator</h3>
            <p>Calculate your margin requirements and risk exposure for different trading instruments.</p>
            <a href="#" class="tool-link">Use Tool</a>
          </div>
          
          <div class="tool-card">
            <div class="tool-icon">💱</div>
            <h3>Pip Value Calculator</h3>
            <p>Calculate pip values and potential profits for forex trading positions.</p>
            <a href="#" class="tool-link">Use Tool</a>
          </div>
          
          <div class="tool-card">
            <div class="tool-icon">🔄</div>
            <h3>Currency Converter</h3>
            <p>Real-time currency conversion with live exchange rates from global markets.</p>
            <a href="#" class="tool-link">Use Tool</a>
          </div>
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-it-works">
      <div class="container">
        <div class="section-header">
          <h2>Start Trading on Your Terms</h2>
          <p>Simple steps to begin your trading journey</p>
        </div>
        
        <div class="steps-grid">
          <div class="step">
            <div class="step-number">01</div>
            <h3>Open Your Account</h3>
            <p>Complete registration and verify your identity in minutes</p>
          </div>
          
          <div class="step">
            <div class="step-number">02</div>
            <h3>Download Application</h3>
            <p>Get our mobile app for trading on the go</p>
          </div>
          
          <div class="step">
            <div class="step-number">03</div>
            <h3>Analyze the Market</h3>
            <p>Use our advanced tools and charts for market analysis</p>
          </div>
          
          <div class="step">
            <div class="step-number">04</div>
            <h3>Trade & Make Money</h3>
            <p>Execute trades and start building your portfolio</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Why Choose Us Section -->
    <section class="why-choose-us">
      <div class="container">
        <div class="section-header">
          <h2>Reason For Choose Us</h2>
          <p>Why traders trust Macxgain for their trading needs</p>
        </div>
        
        <div class="features-grid">
          <div class="feature-item">
            <div class="feature-icon">👥</div>
            <h3>Friendly & Expert</h3>
            <p>Our team of trading experts is here to help you succeed</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">🛟</div>
            <h3>24/7 Support</h3>
            <p>Round-the-clock customer support for all your trading needs</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">🎯</div>
            <h3>Demo Account</h3>
            <p>Practice trading with virtual money before going live</p>
          </div>
          
          <div class="feature-item">
            <div class="feature-icon">🏆</div>
            <h3>Award Winner</h3>
            <p>Recognized as one of the best trading platforms</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
      <div class="container">
        <div class="footer-content">
          <div class="footer-section">
            <div class="footer-logo">
              <img src="../logo.png" alt="Macxgain Logo" class="logo" />
              <h3>Macxgain</h3>
            </div>
            <p>Your trusted partner for futures and options trading. Start your trading journey with us today.</p>
          </div>
          
          <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
              <li><a href="#">About Us</a></li>
              <li><a href="#">Careers</a></li>
              <li><a href="#">Meet Our Team</a></li>
              <li><a href="#">Process</a></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Support</h4>
            <ul>
              <li><a href="#">Help Center</a></li>
              <li><a href="#">Live Chat</a></li>
              <li><a href="#">Contact Us</a></li>
              <li><a href="#">FAQ's</a></li>
            </ul>
          </div>
          
          <div class="footer-section">
            <h4>Contact Info</h4>
            <p>F11 19th Floor, GYS Vision Tower<br>Sainik Road, Gurugram Haryana 120015</p>
            <p>info@macxgain.com</p>
          </div>
        </div>
        
        <div class="footer-bottom">
          <p>&copy; 2025 Macxgain. All Rights Reserved.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script>
export default {
  name: "MacxgainLandingPage",
  data() {
    return {
      // Component data
    };
  },
  mounted() {
    // Add any initialization logic here
  },
};
</script>

<style scoped>
/* Landing Page Styles */
.landing-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 50%, #16213e 100%);
  color: white;
  position: relative;
  overflow-x: hidden;
}

/* Trading Background Animation */
.trading-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
}

.stock-chart {
  position: absolute;
  display: flex;
  gap: 2px;
  opacity: 0.1;
}

.chart-1 {
  top: 15%;
  left: 5%;
  animation: slideRight 20s linear infinite;
}

.chart-2 {
  top: 45%;
  right: 10%;
  animation: slideLeft 25s linear infinite;
}

.chart-3 {
  bottom: 20%;
  left: 15%;
  animation: slideRight 30s linear infinite;
}

.candlestick {
  width: 4px;
  background: #00ff88;
  border-radius: 1px;
}

.candlestick.up {
  height: 20px;
  background: #00ff88;
}

.candlestick.down {
  height: 15px;
  background: #ff4757;
}

.trading-indicators {
  position: absolute;
  top: 30%;
  right: 20%;
  opacity: 0.1;
}

.indicator {
  display: flex;
  align-items: center;
  margin-bottom: 10px;
}

.indicator .line {
  width: 40px;
  height: 2px;
  background: #00ff88;
  margin-right: 8px;
}

.indicator .dot {
  width: 6px;
  height: 6px;
  background: #00ff88;
  border-radius: 50%;
  animation: pulse 2s ease-in-out infinite;
}

.price-tickers {
  position: absolute;
  top: 10%;
  right: 5%;
  opacity: 0.2;
}

.ticker {
  background: rgba(0, 255, 136, 0.1);
  padding: 8px 12px;
  border-radius: 6px;
  margin-bottom: 8px;
  display: flex;
  gap: 8px;
  font-size: 12px;
  animation: slideLeft 15s linear infinite;
}

.ticker .symbol {
  font-weight: bold;
}

.ticker .price.up {
  color: #00ff88;
}

.ticker .price.down {
  color: #ff4757;
}

.ticker .change.up {
  color: #00ff88;
}

.ticker .change.down {
  color: #ff4757;
}

.floating-numbers {
  position: absolute;
  top: 60%;
  left: 10%;
  opacity: 0.15;
}

.number {
  font-size: 14px;
  color: #00ff88;
  margin-bottom: 10px;
  animation: floatUp 8s ease-in-out infinite;
}

/* Animations */
@keyframes slideRight {
  0% { transform: translateX(-100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(100vw + 100px)); opacity: 0; }
}

@keyframes slideLeft {
  0% { transform: translateX(100px); opacity: 0; }
  10% { opacity: 0.1; }
  90% { opacity: 0.1; }
  100% { transform: translateX(calc(-100vw - 100px)); opacity: 0; }
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

@keyframes floatUp {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-20px); }
}

/* Header Styles */
.header {
  position: relative;
  z-index: 10;
  background: rgba(10, 10, 26, 0.95);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  padding: 1rem 0;
}

.header-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.logo-section {
  display: flex;
  align-items: center;
  gap: 12px;
}

.logo {
  width: 40px;
  height: auto;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.nav-menu {
  display: flex;
  gap: 2rem;
}

.nav-link {
  color: white;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.3s ease;
}

.nav-link:hover {
  color: #00ff88;
}

.auth-buttons {
  display: flex;
  gap: 1rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  transition: all 0.3s ease;
  border: none;
  cursor: pointer;
}

.btn-outline {
  background: transparent;
  color: white;
  border: 2px solid rgba(255, 255, 255, 0.3);
}

.btn-outline:hover {
  border-color: #00ff88;
  color: #00ff88;
}

.btn-primary {
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0a0a1a;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 255, 136, 0.3);
}

.btn-large {
  padding: 1rem 2rem;
  font-size: 1.1rem;
}

/* Hero Section */
.hero {
  position: relative;
  z-index: 5;
  padding: 6rem 0;
  min-height: 80vh;
  display: flex;
  align-items: center;
}

.hero-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4rem;
  align-items: center;
}

.trust-badge {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  padding: 0.5rem 1rem;
  border-radius: 25px;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.hero-title {
  font-size: 3.5rem;
  font-weight: bold;
  line-height: 1.2;
  margin-bottom: 1.5rem;
}

.highlight {
  color: #00ff88;
}

.hero-subtitle {
  font-size: 1.2rem;
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 2rem;
}

.hero-actions {
  display: flex;
  gap: 1rem;
  margin-bottom: 3rem;
}

.hero-stats {
  display: flex;
  gap: 3rem;
}

.stat {
  text-align: center;
}

.stat-number {
  display: block;
  font-size: 2rem;
  font-weight: bold;
  color: #00ff88;
}

.stat-label {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.7);
}

/* Trading Dashboard Visual */
.hero-visual {
  display: flex;
  justify-content: center;
}

.trading-dashboard {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
}

.chart-window {
  width: 300px;
  height: 200px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-title {
  font-weight: 600;
}

.chart-price.up {
  color: #00ff88;
}

.chart-body {
  height: 150px;
  position: relative;
}

.price-line {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 1px;
}

.volume-bars {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: end;
  gap: 2px;
}

.volume-bars::before {
  content: '';
  width: 4px;
  height: 60%;
  background: rgba(0, 255, 136, 0.3);
  border-radius: 1px;
}

.volume-bars::after {
  content: '';
  width: 4px;
  height: 80%;
  background: rgba(0, 255, 136, 0.5);
  border-radius: 1px;
}

/* Section Styles */
.container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.section-header {
  text-align: center;
  margin-bottom: 4rem;
}

.section-header h2 {
  font-size: 2.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.section-header p {
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.8);
}

/* Account Types Section */
.account-types {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.account-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.account-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 2rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
  position: relative;
}

.account-card:hover {
  transform: translateY(-5px);
  border-color: rgba(0, 255, 136, 0.3);
}

.account-card.featured {
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.05);
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.card-header h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0;
}

.card-badge {
  background: #00ff88;
  color: #0a0a1a;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 600;
}

.card-features {
  margin-bottom: 2rem;
}

.feature {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 0.75rem;
}

.feature-icon {
  font-size: 1.2rem;
}

/* Trading Tools Section */
.trading-tools {
  padding: 6rem 0;
}

.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.tool-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s ease;
}

.tool-card:hover {
  transform: translateY(-5px);
  border-color: rgba(0, 255, 136, 0.3);
}

.tool-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.tool-card h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.tool-card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.tool-link {
  color: #00ff88;
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.tool-link:hover {
  color: #00d4aa;
}

/* How It Works Section */
.how-it-works {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.steps-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.step {
  text-align: center;
  padding: 2rem;
}

.step-number {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  color: #0a0a1a;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  font-weight: bold;
  margin: 0 auto 1.5rem;
}

.step h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.step p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

/* Why Choose Us Section */
.why-choose-us {
  padding: 6rem 0;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 2rem;
}

.feature-item {
  text-align: center;
  padding: 2rem;
}

.feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-item h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
}

.feature-item p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

/* Footer */
.footer {
  background: rgba(0, 0, 0, 0.3);
  padding: 4rem 0 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.footer-content {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 3rem;
  margin-bottom: 3rem;
}

.footer-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  margin-bottom: 1rem;
}

.footer-logo .logo {
  width: 40px;
  height: auto;
}

.footer-logo h3 {
  font-size: 1.5rem;
  font-weight: bold;
  color: #00ff88;
  margin: 0;
}

.footer-section h4 {
  font-size: 1.2rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: #00ff88;
}

.footer-section ul {
  list-style: none;
  padding: 0;
}

.footer-section ul li {
  margin-bottom: 0.5rem;
}

.footer-section ul li a {
  color: rgba(255, 255, 255, 0.8);
  text-decoration: none;
  transition: color 0.3s ease;
}

.footer-section ul li a:hover {
  color: #00ff88;
}

.footer-section p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
}

.footer-bottom {
  text-align: center;
  padding-top: 2rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.6);
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-container {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-actions {
    flex-direction: column;
  }
  
  .hero-stats {
    justify-content: center;
  }
  
  .nav-menu {
    display: none;
  }
  
  .auth-buttons {
    gap: 0.5rem;
  }
  
  .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  .section-header h2 {
    font-size: 2rem;
  }
  
  .account-cards,
  .tools-grid,
  .steps-grid,
  .features-grid {
    grid-template-columns: 1fr;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
}

@media (max-width: 480px) {
  .header-container {
    padding: 0 1rem;
  }
  
  .container {
    padding: 0 1rem;
  }
  
  .hero {
    padding: 4rem 0;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .btn-large {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
  }
  
  .section-header h2 {
    font-size: 1.8rem;
  }
  
  .account-card,
  .tool-card,
  .step,
  .feature-item {
    padding: 1.5rem;
  }
}
</style>