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

    <!-- Trading Features Section -->
    <section class="trading-features">
      <div class="container">
        <div class="section-header">
          <h2>Advanced Trading Features</h2>
          <p>Everything you need for successful futures and options trading</p>
        </div>
        
        <div class="features-grid">
          <div class="feature-card">
            <div class="feature-icon">⚡</div>
            <h3>High-Speed Execution</h3>
            <p>Execute trades in milliseconds with our ultra-fast order execution system</p>
            <div class="feature-stats">
              <div class="stat">
                <span class="stat-value">0.001s</span>
                <span class="stat-label">Execution Speed</span>
              </div>
              <div class="stat">
                <span class="stat-value">99.9%</span>
                <span class="stat-label">Success Rate</span>
              </div>
            </div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">📈</div>
            <h3>Advanced Charting</h3>
            <p>Professional charts with 100+ technical indicators and drawing tools</p>
            <div class="feature-stats">
              <div class="stat">
                <span class="stat-value">100+</span>
                <span class="stat-label">Indicators</span>
              </div>
              <div class="stat">
                <span class="stat-value">15</span>
                <span class="stat-label">Timeframes</span>
              </div>
            </div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">🛡️</div>
            <h3>Risk Management</h3>
            <p>Advanced risk management tools with stop-loss and take-profit orders</p>
            <div class="feature-stats">
              <div class="stat">
                <span class="stat-value">Auto</span>
                <span class="stat-label">Stop Loss</span>
              </div>
              <div class="stat">
                <span class="stat-value">Real-time</span>
                <span class="stat-label">Monitoring</span>
              </div>
            </div>
          </div>
          
          <div class="feature-card">
            <div class="feature-icon">📱</div>
            <h3>Mobile Trading</h3>
            <p>Trade anywhere with our powerful mobile app for iOS and Android</p>
            <div class="feature-stats">
              <div class="stat">
                <span class="stat-value">24/7</span>
                <span class="stat-label">Access</span>
              </div>
              <div class="stat">
                <span class="stat-value">Push</span>
                <span class="stat-label">Alerts</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Market Analysis Section -->
    <section class="market-analysis">
      <div class="container">
        <div class="section-header">
          <h2>Live Market Analysis</h2>
          <p>Get real-time insights and expert analysis</p>
        </div>
        
        <div class="analysis-grid">
          <div class="analysis-card">
            <div class="analysis-header">
              <h3>NIFTY 50</h3>
              <div class="market-status up">Bullish</div>
            </div>
            <div class="analysis-content">
              <div class="price-info">
                <div class="current-price">₹24,363.30</div>
                <div class="price-change up">+234.50 (+0.95%)</div>
              </div>
              <div class="technical-indicators">
                <div class="indicator">
                  <span class="indicator-name">RSI</span>
                  <span class="indicator-value bullish">65.2</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">MACD</span>
                  <span class="indicator-value bullish">Bullish</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Support</span>
                  <span class="indicator-value">24,200</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Resistance</span>
                  <span class="indicator-value">24,500</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="analysis-card">
            <div class="analysis-header">
              <h3>BANK NIFTY</h3>
              <div class="market-status down">Bearish</div>
            </div>
            <div class="analysis-content">
              <div class="price-info">
                <div class="current-price">₹44,570.89</div>
                <div class="price-change down">-705.30 (-1.56%)</div>
              </div>
              <div class="technical-indicators">
                <div class="indicator">
                  <span class="indicator-name">RSI</span>
                  <span class="indicator-value bearish">35.8</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">MACD</span>
                  <span class="indicator-value bearish">Bearish</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Support</span>
                  <span class="indicator-value">44,200</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Resistance</span>
                  <span class="indicator-value">45,000</span>
                </div>
              </div>
            </div>
          </div>
          
          <div class="analysis-card">
            <div class="analysis-header">
              <h3>FINNIFTY</h3>
              <div class="market-status neutral">Neutral</div>
            </div>
            <div class="analysis-content">
              <div class="price-info">
                <div class="current-price">₹20,150.75</div>
                <div class="price-change neutral">+12.50 (+0.06%)</div>
              </div>
              <div class="technical-indicators">
                <div class="indicator">
                  <span class="indicator-name">RSI</span>
                  <span class="indicator-value neutral">48.5</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">MACD</span>
                  <span class="indicator-value neutral">Neutral</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Support</span>
                  <span class="indicator-value">20,000</span>
                </div>
                <div class="indicator">
                  <span class="indicator-name">Resistance</span>
                  <span class="indicator-value">20,300</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Educational Resources Section -->
    <section class="educational-resources">
      <div class="container">
        <div class="section-header">
          <h2>Learn & Grow</h2>
          <p>Comprehensive educational resources for traders</p>
        </div>
        
        <div class="resources-grid">
          <div class="resource-card">
            <div class="resource-icon">📚</div>
            <h3>Trading Academy</h3>
            <p>Free courses on futures and options trading fundamentals</p>
            <div class="resource-features">
              <span class="feature-tag">Beginner</span>
              <span class="feature-tag">Intermediate</span>
              <span class="feature-tag">Advanced</span>
            </div>
            <a href="#" class="resource-link">Start Learning</a>
          </div>
          
          <div class="resource-card">
            <div class="resource-icon">📊</div>
            <h3>Market Analysis</h3>
            <p>Daily market updates and expert analysis from our team</p>
            <div class="resource-features">
              <span class="feature-tag">Daily Reports</span>
              <span class="feature-tag">Expert Insights</span>
              <span class="feature-tag">Live Webinars</span>
            </div>
            <a href="#" class="resource-link">Read Analysis</a>
          </div>
          
          <div class="resource-card">
            <div class="resource-icon">🎥</div>
            <h3>Video Tutorials</h3>
            <p>Step-by-step video guides for platform features</p>
            <div class="resource-features">
              <span class="feature-tag">Platform Guide</span>
              <span class="feature-tag">Strategy Videos</span>
              <span class="feature-tag">Tips & Tricks</span>
            </div>
            <a href="#" class="resource-link">Watch Videos</a>
          </div>
          
          <div class="resource-card">
            <div class="resource-icon">📈</div>
            <h3>Strategy Library</h3>
            <p>Proven trading strategies and risk management techniques</p>
            <div class="resource-features">
              <span class="feature-tag">Strategies</span>
              <span class="feature-tag">Risk Management</span>
              <span class="feature-tag">Case Studies</span>
            </div>
            <a href="#" class="resource-link">Explore Strategies</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Trading Instruments Section -->
    <section class="trading-instruments">
      <div class="container">
        <div class="section-header">
          <h2>Trading Instruments</h2>
          <p>Trade a wide range of instruments with competitive spreads</p>
        </div>
        
        <div class="instruments-grid">
          <div class="instrument-card">
            <div class="instrument-header">
              <div class="instrument-icon">📈</div>
              <div class="instrument-info">
                <h3>Equity Futures</h3>
                <p>Trade major Indian stocks with leverage</p>
              </div>
            </div>
            <div class="instrument-details">
              <div class="detail-item">
                <span class="detail-label">Min Lot Size</span>
                <span class="detail-value">50</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Leverage</span>
                <span class="detail-value">Up to 20x</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Trading Hours</span>
                <span class="detail-value">9:15 AM - 3:30 PM</span>
              </div>
            </div>
            <div class="popular-stocks">
              <span class="stock-tag">RELIANCE</span>
              <span class="stock-tag">TCS</span>
              <span class="stock-tag">INFY</span>
              <span class="stock-tag">HDFC</span>
            </div>
          </div>
          
          <div class="instrument-card">
            <div class="instrument-header">
              <div class="instrument-icon">🏦</div>
              <div class="instrument-info">
                <h3>Index Futures</h3>
                <p>Trade major market indices with high liquidity</p>
              </div>
            </div>
            <div class="instrument-details">
              <div class="detail-item">
                <span class="detail-label">Min Lot Size</span>
                <span class="detail-value">50</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Leverage</span>
                <span class="detail-value">Up to 20x</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Trading Hours</span>
                <span class="detail-value">9:15 AM - 3:30 PM</span>
              </div>
            </div>
            <div class="popular-stocks">
              <span class="stock-tag">NIFTY 50</span>
              <span class="stock-tag">BANK NIFTY</span>
              <span class="stock-tag">FINNIFTY</span>
              <span class="stock-tag">SENSEX</span>
            </div>
          </div>
          
          <div class="instrument-card">
            <div class="instrument-header">
              <div class="instrument-icon">⚡</div>
              <div class="instrument-info">
                <h3>Options Trading</h3>
                <p>Advanced options strategies with unlimited profit potential</p>
              </div>
            </div>
            <div class="instrument-details">
              <div class="detail-item">
                <span class="detail-label">Min Lot Size</span>
                <span class="detail-value">50</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Leverage</span>
                <span class="detail-value">Unlimited</span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Trading Hours</span>
                <span class="detail-value">9:15 AM - 3:30 PM</span>
              </div>
            </div>
            <div class="popular-stocks">
              <span class="stock-tag">CE/PE</span>
              <span class="stock-tag">Straddle</span>
              <span class="stock-tag">Strangle</span>
              <span class="stock-tag">Butterfly</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Platform Features Section -->
    <section class="platform-features">
      <div class="container">
        <div class="section-header">
          <h2>Platform Features</h2>
          <p>Advanced features designed for professional traders</p>
        </div>
        
        <div class="features-showcase">
          <div class="feature-showcase-item">
            <div class="feature-content">
              <h3>Real-Time Data</h3>
              <p>Get live market data with millisecond precision. Track prices, volumes, and market depth in real-time.</p>
              <div class="feature-highlights">
                <div class="highlight-item">
                  <span class="highlight-icon">⚡</span>
                  <span>Millisecond Updates</span>
                </div>
                <div class="highlight-item">
                  <span class="highlight-icon">📊</span>
                  <span>Market Depth</span>
                </div>
                <div class="highlight-item">
                  <span class="highlight-icon">📈</span>
                  <span>Live Charts</span>
                </div>
              </div>
            </div>
            <div class="feature-image">
              <div class="data-display">
                <div class="data-header">
                  <span class="data-title">Live Market Data</span>
                  <span class="data-time">15:30:45</span>
                </div>
                <div class="data-items">
                  <div class="data-item">
                    <span class="symbol">NIFTY</span>
                    <span class="price">24,363.30</span>
                    <span class="change up">+234.50</span>
                  </div>
                  <div class="data-item">
                    <span class="symbol">BANKNIFTY</span>
                    <span class="price">44,570.89</span>
                    <span class="change down">-705.30</span>
                  </div>
                  <div class="data-item">
                    <span class="symbol">FINNIFTY</span>
                    <span class="price">20,150.75</span>
                    <span class="change up">+12.50</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div class="feature-showcase-item reverse">
            <div class="feature-image">
              <div class="chart-display">
                <div class="chart-header">
                  <span class="chart-title">Advanced Charting</span>
                  <div class="chart-controls">
                    <span class="control active">1D</span>
                    <span class="control">1W</span>
                    <span class="control">1M</span>
                  </div>
                </div>
                <div class="chart-content">
                  <div class="price-line up"></div>
                  <div class="volume-bars">
                    <div class="volume-bar" v-for="i in 20" :key="i" :style="{ height: Math.random() * 100 + '%' }"></div>
                  </div>
                </div>
                <div class="chart-indicators">
                  <span class="indicator">RSI: 65.2</span>
                  <span class="indicator">MACD: Bullish</span>
                  <span class="indicator">MA: 24,200</span>
                </div>
              </div>
            </div>
            <div class="feature-content">
              <h3>Advanced Charting</h3>
              <p>Professional charting with 100+ technical indicators, drawing tools, and multiple timeframes.</p>
              <div class="feature-highlights">
                <div class="highlight-item">
                  <span class="highlight-icon">📊</span>
                  <span>100+ Indicators</span>
                </div>
                <div class="highlight-item">
                  <span class="highlight-icon">✏️</span>
                  <span>Drawing Tools</span>
                </div>
                <div class="highlight-item">
                  <span class="highlight-icon">⏰</span>
                  <span>15 Timeframes</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Success Stories Section -->
    <section class="success-stories">
      <div class="container">
        <div class="section-header">
          <h2>Success Stories</h2>
          <p>Real traders, real results with Macxgain</p>
        </div>
        
        <div class="stories-grid">
          <div class="story-card">
            <div class="story-header">
              <div class="trader-avatar">RK</div>
              <div class="trader-info">
                <h4>Rajesh Kumar</h4>
                <span class="trader-type">Day Trader</span>
              </div>
              <div class="profit-badge up">+₹2.5L</div>
            </div>
            <p class="story-text">"Started with ₹50,000 and now consistently earning ₹25,000-30,000 per month. The platform's real-time data and advanced tools have been game-changing."</p>
            <div class="story-stats">
              <div class="stat">
                <span class="stat-value">85%</span>
                <span class="stat-label">Win Rate</span>
              </div>
              <div class="stat">
                <span class="stat-value">₹2.5L</span>
                <span class="stat-label">Total Profit</span>
              </div>
              <div class="stat">
                <span class="stat-value">6</span>
                <span class="stat-label">Months</span>
              </div>
            </div>
          </div>
          
          <div class="story-card">
            <div class="story-header">
              <div class="trader-avatar">SP</div>
              <div class="trader-info">
                <h4>Sneha Patel</h4>
                <span class="trader-type">Swing Trader</span>
              </div>
              <div class="profit-badge up">+₹4.2L</div>
            </div>
            <p class="story-text">"The educational resources and market analysis helped me understand options trading. Now I'm making consistent profits with proper risk management."</p>
            <div class="story-stats">
              <div class="stat">
                <span class="stat-value">78%</span>
                <span class="stat-label">Win Rate</span>
              </div>
              <div class="stat">
                <span class="stat-value">₹4.2L</span>
                <span class="stat-label">Total Profit</span>
              </div>
              <div class="stat">
                <span class="stat-value">8</span>
                <span class="stat-label">Months</span>
              </div>
            </div>
          </div>
          
          <div class="story-card">
            <div class="story-header">
              <div class="trader-avatar">AS</div>
              <div class="trader-info">
                <h4>Amit Sharma</h4>
                <span class="trader-type">Options Trader</span>
              </div>
              <div class="profit-badge up">+₹6.8L</div>
            </div>
            <p class="story-text">"The platform's advanced charting and real-time data have transformed my trading. I can execute complex strategies with confidence."</p>
            <div class="story-stats">
              <div class="stat">
                <span class="stat-value">92%</span>
                <span class="stat-label">Win Rate</span>
              </div>
              <div class="stat">
                <span class="stat-value">₹6.8L</span>
                <span class="stat-label">Total Profit</span>
              </div>
              <div class="stat">
                <span class="stat-value">12</span>
                <span class="stat-label">Months</span>
              </div>
            </div>
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

    <!-- Trading Tools Section -->
    <section class="trading-tools">
      <div class="container">
        <div class="section-header">
          <h2>Advanced Trading Tools</h2>
          <p>Professional tools designed for serious traders</p>
        </div>
        
        <div class="tools-grid">
          <div class="tool-card">
            <div class="tool-icon">📊</div>
            <h3>Real-Time Charts</h3>
            <p>Advanced charting with 50+ technical indicators and drawing tools</p>
            <div class="tool-features">
              <span class="feature-tag">Multiple Timeframes</span>
              <span class="feature-tag">Candlestick Patterns</span>
              <span class="feature-tag">Volume Analysis</span>
            </div>
          </div>
          
          <div class="tool-card">
            <div class="tool-icon">⚡</div>
            <h3>One-Click Trading</h3>
            <p>Execute trades instantly with our lightning-fast order execution</p>
            <div class="tool-features">
              <span class="feature-tag">Instant Execution</span>
              <span class="feature-tag">Stop Loss</span>
              <span class="feature-tag">Take Profit</span>
            </div>
          </div>
          
          <div class="tool-card">
            <div class="tool-icon">📱</div>
            <h3>Mobile Trading</h3>
            <p>Trade anywhere with our powerful mobile app</p>
            <div class="tool-features">
              <span class="feature-tag">iOS & Android</span>
              <span class="feature-tag">Push Notifications</span>
              <span class="feature-tag">Biometric Login</span>
            </div>
          </div>
          
          <div class="tool-card">
            <div class="tool-icon">🤖</div>
            <h3>AI Trading Signals</h3>
            <p>Get intelligent trading signals powered by artificial intelligence</p>
            <div class="tool-features">
              <span class="feature-tag">AI Analysis</span>
              <span class="feature-tag">Market Sentiment</span>
              <span class="feature-tag">Risk Assessment</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- App Features Section -->
    <section class="app-features">
      <div class="container">
        <div class="section-header">
          <h2>App Features</h2>
          <p>Everything you need in one powerful trading app</p>
        </div>
        
        <div class="features-showcase">
          <div class="feature-showcase-item">
            <div class="feature-image">
              <div class="phone-mockup">
                <div class="screen">
                  <div class="app-header">
                    <div class="app-logo">M</div>
                    <div class="app-title">Macxgain</div>
                  </div>
                  <div class="app-content">
                    <div class="market-card">
                      <div class="market-name">NIFTY 50</div>
                      <div class="market-price">₹24,363.30</div>
                      <div class="market-change up">+234.50 (+0.95%)</div>
                    </div>
                    <div class="market-card">
                      <div class="market-name">SENSEX</div>
                      <div class="market-price">₹79,857.79</div>
                      <div class="market-change down">-765.20 (-0.95%)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="feature-content">
              <h3>Live Market Data</h3>
              <p>Get real-time prices for stocks, indices, commodities, and forex. Track your portfolio with live updates and detailed analytics.</p>
              <ul class="feature-list">
                <li>Real-time price updates</li>
                <li>Advanced charting tools</li>
                <li>Portfolio tracking</li>
                <li>Market news & analysis</li>
              </ul>
            </div>
          </div>
          
          <div class="feature-showcase-item reverse">
            <div class="feature-content">
              <h3>Smart Trading Tools</h3>
              <p>Execute trades with precision using our advanced trading tools. Set stop-loss, take-profit, and use one-click trading.</p>
              <ul class="feature-list">
                <li>One-click trading</li>
                <li>Advanced order types</li>
                <li>Risk management tools</li>
                <li>Trade history & analytics</li>
              </ul>
            </div>
            <div class="feature-image">
              <div class="phone-mockup">
                <div class="screen">
                  <div class="app-header">
                    <div class="app-logo">M</div>
                    <div class="app-title">Macxgain</div>
                  </div>
                  <div class="app-content">
                    <div class="trading-panel">
                      <div class="trade-button buy">BUY</div>
                      <div class="trade-button sell">SELL</div>
                      <div class="order-form">
                        <input type="number" placeholder="Quantity" class="order-input" />
                        <input type="number" placeholder="Price" class="order-input" />
                      </div>
                    </div>
                  </div>
                </div>
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
          <h2>Choose Your Account Type</h2>
          <p>Select the perfect account for your trading style</p>
        </div>
        
        <div class="account-cards">
          <div class="account-card">
            <div class="account-header">
              <h3>Basic Account</h3>
              <div class="account-price">₹0</div>
              <div class="account-period">No Setup Fee</div>
            </div>
            <div class="account-features">
              <div class="feature">✅ Minimum Deposit: ₹10,000</div>
              <div class="feature">✅ Spread: From 1.5 pips</div>
              <div class="feature">✅ Commission: ₹0</div>
              <div class="feature">✅ Leverage: Up to 1:100</div>
              <div class="feature">✅ Trading Instruments: 50+</div>
            </div>
            <button class="account-btn">Start Trading</button>
          </div>
          
          <div class="account-card featured">
            <div class="account-badge">Most Popular</div>
            <div class="account-header">
              <h3>Premium Account</h3>
              <div class="account-price">₹0</div>
              <div class="account-period">No Setup Fee</div>
            </div>
            <div class="account-features">
              <div class="feature">✅ Minimum Deposit: ₹50,000</div>
              <div class="feature">✅ Spread: From 0.8 pips</div>
              <div class="feature">✅ Commission: ₹0</div>
              <div class="feature">✅ Leverage: Up to 1:200</div>
              <div class="feature">✅ Trading Instruments: 100+</div>
              <div class="feature">✅ Priority Support</div>
              <div class="feature">✅ Advanced Analytics</div>
            </div>
            <button class="account-btn featured">Start Trading</button>
          </div>
          
          <div class="account-card">
            <div class="account-header">
              <h3>VIP Account</h3>
              <div class="account-price">₹0</div>
              <div class="account-period">No Setup Fee</div>
            </div>
            <div class="account-features">
              <div class="feature">✅ Minimum Deposit: ₹2,00,000</div>
              <div class="feature">✅ Spread: From 0.3 pips</div>
              <div class="feature">✅ Commission: ₹0</div>
              <div class="feature">✅ Leverage: Up to 1:500</div>
              <div class="feature">✅ Trading Instruments: 200+</div>
              <div class="feature">✅ Dedicated Manager</div>
              <div class="feature">✅ Custom Solutions</div>
            </div>
            <button class="account-btn">Start Trading</button>
          </div>
        </div>
      </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
      <div class="container">
        <div class="section-header">
          <h2>What Our Traders Say</h2>
          <p>Join thousands of satisfied traders worldwide</p>
        </div>
        
        <div class="testimonials-grid">
          <div class="testimonial-card">
            <div class="testimonial-rating">
              ⭐⭐⭐⭐⭐
            </div>
            <p class="testimonial-text">"Macxgain has transformed my trading experience. The real-time data and advanced tools have helped me make better decisions."</p>
            <div class="testimonial-author">
              <div class="author-avatar">RK</div>
              <div class="author-info">
                <div class="author-name">Rajesh Kumar</div>
                <div class="author-title">Professional Trader</div>
              </div>
            </div>
          </div>
          
          <div class="testimonial-card">
            <div class="testimonial-rating">
              ⭐⭐⭐⭐⭐
            </div>
            <p class="testimonial-text">"The mobile app is incredible! I can trade from anywhere and the interface is so intuitive. Highly recommended!"</p>
            <div class="testimonial-author">
              <div class="author-avatar">SP</div>
              <div class="author-info">
                <div class="author-name">Sneha Patel</div>
                <div class="author-title">Day Trader</div>
              </div>
            </div>
          </div>
          
          <div class="testimonial-card">
            <div class="testimonial-rating">
              ⭐⭐⭐⭐⭐
            </div>
            <p class="testimonial-text">"Best trading platform I've used. The customer support is excellent and the platform is very reliable."</p>
            <div class="testimonial-author">
              <div class="author-avatar">AS</div>
              <div class="author-info">
                <div class="author-name">Amit Sharma</div>
                <div class="author-title">Swing Trader</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Statistics Section -->
    <section class="statistics">
      <div class="container">
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number">50,000+</div>
            <div class="stat-label">Active Traders</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">₹500Cr+</div>
            <div class="stat-label">Trading Volume</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">99.9%</div>
            <div class="stat-label">Uptime</div>
          </div>
          <div class="stat-item">
            <div class="stat-number">24/7</div>
            <div class="stat-label">Support</div>
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

/* Trading Features Section */
.trading-features {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.feature-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
}

.feature-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-5px);
}

.feature-card .feature-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.feature-card h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: white;
}

.feature-card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.feature-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.feature-stats .stat {
  text-align: center;
}

.feature-stats .stat-value {
  display: block;
  font-size: 1.2rem;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 0.25rem;
}

.feature-stats .stat-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

/* Market Analysis Section */
.market-analysis {
  padding: 6rem 0;
}

.analysis-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.analysis-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.analysis-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.analysis-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.analysis-header h3 {
  font-size: 1.3rem;
  font-weight: bold;
  color: white;
  margin: 0;
}

.market-status {
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: bold;
}

.market-status.up {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.market-status.down {
  background: rgba(255, 71, 87, 0.1);
  color: #ff4757;
  border: 1px solid rgba(255, 71, 87, 0.3);
}

.market-status.neutral {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.price-info {
  margin-bottom: 1.5rem;
}

.current-price {
  font-size: 1.5rem;
  font-weight: bold;
  color: white;
  margin-bottom: 0.5rem;
}

.price-change {
  font-size: 1rem;
  font-weight: 500;
}

.price-change.up {
  color: #00ff88;
}

.price-change.down {
  color: #ff4757;
}

.price-change.neutral {
  color: rgba(255, 255, 255, 0.7);
}

.technical-indicators {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.indicator {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 6px;
}

.indicator-name {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.indicator-value {
  font-weight: bold;
  font-size: 0.9rem;
}

.indicator-value.bullish {
  color: #00ff88;
}

.indicator-value.bearish {
  color: #ff4757;
}

.indicator-value.neutral {
  color: rgba(255, 255, 255, 0.7);
}

/* Educational Resources Section */
.educational-resources {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.resources-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.resource-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
}

.resource-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-5px);
}

.resource-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.resource-card h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: white;
}

.resource-card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.resource-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.resource-link {
  display: inline-block;
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 500;
  border: 1px solid rgba(0, 255, 136, 0.3);
  transition: all 0.3s ease;
}

.resource-link:hover {
  background: rgba(0, 255, 136, 0.2);
  border-color: rgba(0, 255, 136, 0.5);
}

/* Trading Instruments Section */
.trading-instruments {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.instruments-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.instrument-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.instrument-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-5px);
}

.instrument-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.instrument-icon {
  font-size: 2.5rem;
}

.instrument-info h3 {
  font-size: 1.3rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.5rem 0;
}

.instrument-info p {
  color: rgba(255, 255, 255, 0.7);
  margin: 0;
  font-size: 0.9rem;
}

.instrument-details {
  margin-bottom: 1.5rem;
}

.detail-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.detail-item:last-child {
  border-bottom: none;
}

.detail-label {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

.detail-value {
  color: white;
  font-weight: 500;
  font-size: 0.9rem;
}

.popular-stocks {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.stock-tag {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  padding: 0.25rem 0.75rem;
  border-radius: 15px;
  font-size: 0.8rem;
  font-weight: 500;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

/* Platform Features Section */
.platform-features {
  padding: 6rem 0;
}

.feature-highlights {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
}

.highlight-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.highlight-icon {
  font-size: 1.2rem;
}

.highlight-item span:last-child {
  color: rgba(255, 255, 255, 0.8);
  font-size: 0.9rem;
}

.data-display {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  min-width: 300px;
}

.data-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.data-title {
  font-weight: bold;
  color: white;
  font-size: 1rem;
}

.data-time {
  color: #00ff88;
  font-size: 0.8rem;
  font-weight: 500;
}

.data-items {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.data-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem;
  background: rgba(255, 255, 255, 0.02);
  border-radius: 6px;
}

.data-item .symbol {
  font-weight: bold;
  color: white;
  font-size: 0.9rem;
}

.data-item .price {
  color: white;
  font-weight: 500;
  font-size: 0.9rem;
}

.data-item .change {
  font-weight: bold;
  font-size: 0.8rem;
}

.data-item .change.up {
  color: #00ff88;
}

.data-item .change.down {
  color: #ff4757;
}

.chart-display {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 1.5rem;
  min-width: 300px;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.chart-title {
  font-weight: bold;
  color: white;
  font-size: 1rem;
}

.chart-controls {
  display: flex;
  gap: 0.5rem;
}

.control {
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.7);
}

.control.active {
  background: #00ff88;
  color: #0a0a1a;
}

.chart-content {
  height: 150px;
  position: relative;
  margin-bottom: 1rem;
}

.price-line {
  position: absolute;
  top: 50%;
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
  gap: 1px;
}

.volume-bar {
  flex: 1;
  background: rgba(0, 255, 136, 0.3);
  border-radius: 1px;
  min-height: 10px;
}

.chart-indicators {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.indicator {
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.8);
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
}

/* Success Stories Section */
.success-stories {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.stories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
  gap: 2rem;
}

.story-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.story-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.story-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.trader-avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 1rem;
}

.trader-info h4 {
  font-size: 1.1rem;
  font-weight: bold;
  color: white;
  margin: 0 0 0.25rem 0;
}

.trader-type {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.8rem;
}

.profit-badge {
  margin-left: auto;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.9rem;
}

.profit-badge.up {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

.story-text {
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
  margin-bottom: 1.5rem;
  font-style: italic;
}

.story-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
}

.story-stats .stat {
  text-align: center;
}

.story-stats .stat-value {
  display: block;
  font-size: 1.2rem;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 0.25rem;
}

.story-stats .stat-label {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
}

/* Trading Tools Section */
.trading-tools {
  padding: 6rem 0;
  background: rgba(255, 255, 255, 0.02);
}

.tools-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 2rem;
}

.tool-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  transition: all 0.3s ease;
}

.tool-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-5px);
}

.tool-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.tool-card h3 {
  font-size: 1.3rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: white;
}

.tool-card p {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.tool-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  justify-content: center;
}

.feature-tag {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
  font-weight: 500;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

/* App Features Section */
.app-features {
  padding: 6rem 0;
}

.features-showcase {
  display: flex;
  flex-direction: column;
  gap: 4rem;
}

.feature-showcase-item {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
  align-items: center;
}

.feature-showcase-item.reverse {
  direction: rtl;
}

.feature-showcase-item.reverse > * {
  direction: ltr;
}

.feature-image {
  display: flex;
  justify-content: center;
}

.phone-mockup {
  width: 280px;
  height: 500px;
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
  border-radius: 25px;
  padding: 10px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
  border: 2px solid rgba(255, 255, 255, 0.1);
}

.screen {
  width: 100%;
  height: 100%;
  background: linear-gradient(135deg, #0a0a1a 0%, #1a1a2e 100%);
  border-radius: 20px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
}

.app-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.app-logo {
  width: 30px;
  height: 30px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 0.9rem;
}

.app-title {
  font-weight: bold;
  color: white;
  font-size: 1rem;
}

.app-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.market-card {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.market-name {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 0.25rem;
}

.market-price {
  font-weight: bold;
  color: white;
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.market-change {
  font-size: 0.7rem;
  font-weight: 500;
}

.market-change.up {
  color: #00ff88;
}

.market-change.down {
  color: #ff4757;
}

.trading-panel {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  padding: 1rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.trade-button {
  padding: 0.5rem 1rem;
  border-radius: 6px;
  font-weight: bold;
  text-align: center;
  margin-bottom: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.trade-button.buy {
  background: #00ff88;
  color: #0a0a1a;
}

.trade-button.sell {
  background: #ff4757;
  color: white;
}

.order-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.order-input {
  padding: 0.5rem;
  border-radius: 4px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.05);
  color: white;
  font-size: 0.8rem;
}

.order-input::placeholder {
  color: rgba(255, 255, 255, 0.5);
}

.feature-content h3 {
  font-size: 1.8rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: white;
}

.feature-content p {
  color: rgba(255, 255, 255, 0.8);
  line-height: 1.6;
  margin-bottom: 1.5rem;
}

.feature-list {
  list-style: none;
  padding: 0;
}

.feature-list li {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.5rem;
  padding-left: 1.5rem;
  position: relative;
}

.feature-list li::before {
  content: "✓";
  position: absolute;
  left: 0;
  color: #00ff88;
  font-weight: bold;
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
  margin-top: 3rem;
}

.account-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  text-align: center;
  position: relative;
  transition: all 0.3s ease;
}

.account-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-5px);
}

.account-card.featured {
  border-color: #00ff88;
  background: rgba(0, 255, 136, 0.05);
}

.account-badge {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  background: #00ff88;
  color: #0a0a1a;
  padding: 0.5rem 1rem;
  border-radius: 20px;
  font-weight: bold;
  font-size: 0.8rem;
}

.account-header h3 {
  font-size: 1.5rem;
  font-weight: bold;
  margin-bottom: 1rem;
  color: white;
}

.account-price {
  font-size: 2.5rem;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 0.5rem;
}

.account-period {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

.account-features {
  text-align: left;
  margin-bottom: 2rem;
}

.feature {
  color: rgba(255, 255, 255, 0.8);
  margin-bottom: 0.75rem;
  font-size: 0.9rem;
}

.account-btn {
  background: rgba(255, 255, 255, 0.1);
  color: white;
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 1rem 2rem;
  border-radius: 8px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  width: 100%;
}

.account-btn:hover {
  background: rgba(255, 255, 255, 0.15);
  border-color: rgba(255, 255, 255, 0.3);
}

.account-btn.featured {
  background: #00ff88;
  color: #0a0a1a;
  border-color: #00ff88;
}

.account-btn.featured:hover {
  background: #00d4aa;
  border-color: #00d4aa;
}

/* Testimonials Section */
.testimonials {
  padding: 6rem 0;
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
}

.testimonial-card {
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  padding: 2rem;
  transition: all 0.3s ease;
}

.testimonial-card:hover {
  border-color: rgba(0, 255, 136, 0.3);
  transform: translateY(-2px);
}

.testimonial-rating {
  font-size: 1.2rem;
  margin-bottom: 1rem;
}

.testimonial-text {
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.6;
  margin-bottom: 1.5rem;
  font-style: italic;
}

.testimonial-author {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.author-avatar {
  width: 50px;
  height: 50px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  color: #0a0a1a;
  font-size: 1rem;
}

.author-name {
  font-weight: bold;
  color: white;
  margin-bottom: 0.25rem;
}

.author-title {
  color: rgba(255, 255, 255, 0.7);
  font-size: 0.9rem;
}

/* Statistics Section */
.statistics {
  padding: 4rem 0;
  background: rgba(0, 255, 136, 0.05);
  border-top: 1px solid rgba(0, 255, 136, 0.1);
  border-bottom: 1px solid rgba(0, 255, 136, 0.1);
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 2rem;
}

.stat-item {
  text-align: center;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: bold;
  color: #00ff88;
  margin-bottom: 0.5rem;
}

.stat-label {
  color: rgba(255, 255, 255, 0.8);
  font-size: 1rem;
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
  .features-grid,
  .testimonials-grid,
  .analysis-grid,
  .resources-grid,
  .instruments-grid,
  .stories-grid {
    grid-template-columns: 1fr;
  }
  
  .footer-content {
    grid-template-columns: 1fr;
    text-align: center;
  }
  
  .feature-showcase-item {
    grid-template-columns: 1fr;
    gap: 2rem;
  }
  
  .feature-showcase-item.reverse {
    direction: ltr;
  }
  
  .phone-mockup {
    width: 250px;
    height: 450px;
  }
  
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .stat-number {
    font-size: 2rem;
  }
  
  .tool-features {
    flex-direction: column;
    align-items: center;
  }
  
  .feature-tag {
    width: 100%;
    text-align: center;
  }
  
  .data-display,
  .chart-display {
    min-width: auto;
    width: 100%;
  }
  
  .story-stats {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .story-stats .stat:last-child {
    grid-column: span 2;
  }
  
  .popular-stocks {
    justify-content: center;
  }
  
  .chart-indicators {
    justify-content: center;
  }
  
  .technical-indicators {
    grid-template-columns: 1fr;
  }
  
  .feature-highlights {
    align-items: center;
  }
  
  .highlight-item {
    justify-content: center;
  }
}

@media (max-width: 480px) {
  .container {
    padding: 0 1rem;
  }
  
  .header-container {
    padding: 0 1rem;
  }
  
  .hero-title {
    font-size: 2rem;
  }
  
  .hero-subtitle {
    font-size: 1rem;
  }
  
  .section-header h2 {
    font-size: 1.8rem;
  }
  
  .account-card,
  .tool-card,
  .feature-card,
  .analysis-card,
  .resource-card,
  .instrument-card,
  .story-card {
    padding: 1.5rem;
  }
  
  .data-display,
  .chart-display {
    padding: 1rem;
  }
  
  .story-header {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }
  
  .profit-badge {
    margin-left: 0;
  }
  
  .instrument-header {
    flex-direction: column;
    text-align: center;
    gap: 0.5rem;
  }
  
  .analysis-header {
    flex-direction: column;
    gap: 0.5rem;
    text-align: center;
  }
  
  .price-info {
    text-align: center;
  }
  
  .feature-showcase-item {
    gap: 1rem;
  }
  
  .feature-content h3 {
    font-size: 1.5rem;
  }
  
  .feature-content p {
    font-size: 0.9rem;
  }
  
  .data-items,
  .chart-content {
    font-size: 0.8rem;
  }
  
  .story-text {
    font-size: 0.9rem;
  }
  
  .story-stats .stat-value {
    font-size: 1rem;
  }
  
  .story-stats .stat-label {
    font-size: 0.7rem;
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