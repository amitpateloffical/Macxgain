<template>
  <div class="landing-page">
    <!-- AI Neural Network Background -->
    <div class="ai-background">
      <!-- Neural Network Visualization -->
      <div class="neural-network">
        <div class="neural-layer layer-1">
          <div class="neuron" v-for="i in 8" :key="i" :style="{ animationDelay: i * 0.1 + 's' }"></div>
        </div>
        <div class="neural-layer layer-2">
          <div class="neuron" v-for="i in 12" :key="i" :style="{ animationDelay: i * 0.1 + 's' }"></div>
        </div>
        <div class="neural-layer layer-3">
          <div class="neuron" v-for="i in 6" :key="i" :style="{ animationDelay: i * 0.1 + 's' }"></div>
        </div>
        <div class="neural-connections">
          <div class="connection" v-for="i in 20" :key="i" :style="{ animationDelay: i * 0.2 + 's' }"></div>
        </div>
      </div>
      
      <!-- AI Particles System -->
      <div class="ai-particles">
        <div class="particle" v-for="i in 50" :key="i" :style="{ 
          left: Math.random() * 100 + '%', 
          animationDelay: Math.random() * 5 + 's',
          animationDuration: (Math.random() * 10 + 5) + 's'
        }"></div>
      </div>
      
      <!-- AI Data Streams -->
      <div class="data-streams">
        <div class="stream stream-1">
          <div class="data-bit" v-for="i in 15" :key="i" :style="{ animationDelay: i * 0.3 + 's' }">01</div>
        </div>
        <div class="stream stream-2">
          <div class="data-bit" v-for="i in 12" :key="i" :style="{ animationDelay: i * 0.4 + 's' }">10</div>
        </div>
        <div class="stream stream-3">
          <div class="data-bit" v-for="i in 18" :key="i" :style="{ animationDelay: i * 0.2 + 's' }">11</div>
        </div>
      </div>
      
      <!-- AI Brain Visualization -->
      <div class="ai-brain">
        <div class="brain-core">
          <div class="brain-pulse"></div>
          <div class="brain-synapses">
            <div class="synapse" v-for="i in 8" :key="i" :style="{ 
              transform: `rotate(${i * 45}deg)`,
              animationDelay: i * 0.5 + 's'
            }"></div>
          </div>
        </div>
      </div>
      
      <!-- AI Trading Signals -->
      <div class="ai-signals">
        <div class="signal-wave" v-for="i in 5" :key="i" :style="{ 
          animationDelay: i * 0.8 + 's',
          left: (i * 20) + '%'
        }"></div>
      </div>
      
      <!-- Floating AI Elements -->
      <div class="floating-ai-elements">
        <div class="ai-element element-1">🤖</div>
        <div class="ai-element element-2">🧠</div>
        <div class="ai-element element-3">⚡</div>
        <div class="ai-element element-4">📊</div>
        <div class="ai-element element-5">🎯</div>
        <div class="ai-element element-6">🔮</div>
      </div>
    </div>

    <!-- Header -->
    <header class="header">
      <div class="header-container">
        <div class="logo-section">
          <img :src="brandConfig.logoPathFrontend || '../logo.png'" :alt="brandConfig.companyName + ' Logo'" class="logo" />
          <h1 class="brand-name">{{ brandConfig.companyName }}</h1>
        </div>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" @click="toggleMobileMenu" :class="{ active: mobileMenuOpen }">
          <span></span>
          <span></span>
          <span></span>
        </button>
        
        <nav class="nav-menu" :class="{ 'mobile-open': mobileMenuOpen }">
          <a href="#" class="nav-link">Home</a>
          <a href="/markets" class="nav-link">Markets</a>
          <a href="#" class="nav-link">Download APK</a>
        </nav>
        
        <div class="auth-buttons">
          <a href="/login" class="btn btn-outline">Login</a>
          <a href="/register" class="btn btn-primary">Register</a>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
      <div class="hero-container">
        <div class="hero-content">
          <div class="trust-badge fade-in-left">
            <span class="badge-icon">🚀</span>
            <span>Limited Time: 50% Off on Premium Plans + Free Trading Course</span>
          </div>
          
          <h1 class="hero-title fade-in-left">
            <span class="ai-typing-text">{{ currentTypingText || 'Master the Markets with AI-Powered Trading' }}</span>
            <span class="typing-cursor" v-if="currentTypingText">|</span>
          </h1>
          
          <p class="hero-subtitle fade-in-left">
            Experience next-generation trading with {{ brandConfig.companyName }}'s revolutionary AI algorithms. 
            Our machine learning models analyze 10,000+ data points per second to deliver 
            <span class="ai-highlight">95.8% accuracy</span> in market predictions.
          </p>
          
          <div class="hero-actions fade-in-left">
            <a href="/login" class="btn btn-primary btn-large">Start Trading Now</a>
            <a href="#" class="btn btn-outline btn-large">Download App</a>
          </div>
          
          <!-- AI Voice Assistant Simulation -->
          <div class="ai-voice-assistant fade-in-up">
            <div class="voice-waves">
              <div class="wave wave-1"></div>
              <div class="wave wave-2"></div>
              <div class="wave wave-3"></div>
              <div class="wave wave-4"></div>
            </div>
            <div class="voice-text">
              <span class="voice-label">AI Assistant:</span>
              <span class="voice-message">"Analyzing market conditions... 94.7% confidence in bullish trend"</span>
            </div>
          </div>
          
          <div class="hero-stats fade-in-up">
            <div class="stat">
              <span class="stat-number">₹2.5Cr+</span>
              <span class="stat-label">Daily Volume</span>
            </div>
            <div class="stat">
              <span class="stat-number">95.8%</span>
              <span class="stat-label">Win Rate</span>
            </div>
            <div class="stat">
              <span class="stat-number">150+</span>
              <span class="stat-label">AI Signals</span>
            </div>
          </div>
        </div>
        
        <div class="hero-visual">
          <div class="ai-trading-dashboard">
            <!-- AI Trading Bot Visualization -->
            <div class="ai-bot-container">
              <div class="ai-bot">
                <div class="bot-head">
                  <div class="bot-eyes">
                    <div class="eye left-eye"></div>
                    <div class="eye right-eye"></div>
                  </div>
                  <div class="bot-antenna">
                    <div class="antenna-signal"></div>
                  </div>
                </div>
                <div class="bot-body">
                  <div class="ai-status">
                    <span class="status-text">AI ACTIVE</span>
                    <div class="status-indicator"></div>
                  </div>
                  <div class="processing-lines">
                    <div class="line" v-for="i in 5" :key="i" :style="{ animationDelay: i * 0.2 + 's' }"></div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- AI Prediction Chart -->
            <div class="ai-prediction-chart">
              <div class="chart-header">
                <div class="chart-info">
                  <span class="chart-title">AI Market Prediction</span>
                  <span class="chart-subtitle">Neural Network Analysis</span>
                </div>
                <div class="ai-confidence">
                  <span class="confidence-label">AI Confidence</span>
                  <span class="confidence-value">94.7%</span>
                </div>
              </div>
              <div class="prediction-chart">
                <div class="prediction-line"></div>
                <div class="prediction-points">
                  <div class="prediction-point" v-for="i in 8" :key="i" :style="{ 
                    left: (i * 12.5) + '%',
                    animationDelay: i * 0.3 + 's'
                  }"></div>
                </div>
                <div class="ai-signals">
                  <div class="signal buy-signal">BUY</div>
                  <div class="signal hold-signal">HOLD</div>
                  <div class="signal sell-signal">SELL</div>
                </div>
              </div>
            </div>
            
            <!-- Mini Charts -->
            <div class="mini-charts">
              <div class="mini-chart">
                <div class="mini-header">
                  <span class="mini-title">BANKNIFTY</span>
                  <span class="mini-price up">₹44,570 +2.1%</span>
                </div>
                <div class="mini-line up"></div>
              </div>
              
              <div class="mini-chart">
                <div class="mini-header">
                  <span class="mini-title">RELIANCE</span>
                  <span class="mini-price up">₹2,845 +0.8%</span>
                </div>
                <div class="mini-line up"></div>
              </div>
              
              <div class="mini-chart">
                <div class="mini-header">
                  <span class="mini-title">TCS</span>
                  <span class="mini-price up">₹4,050 +2.5%</span>
                </div>
                <div class="mini-line up"></div>
              </div>
            </div>
            
            <!-- Live Ticker -->
            <div class="live-ticker">
              <div class="ticker-item">
                <span class="ticker-symbol">HDFC</span>
                <span class="ticker-price up">₹1,650 +1.2%</span>
              </div>
              <div class="ticker-item">
                <span class="ticker-symbol">INFY</span>
                <span class="ticker-price up">₹1,480 +0.9%</span>
              </div>
              <div class="ticker-item">
                <span class="ticker-symbol">WIPRO</span>
                <span class="ticker-price down">₹420 -0.3%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Account Types Section -->
    <section class="account-types">
      <div class="container">
        <div class="section-header fade-in-up">
          <h2>Next-Gen AI Trading Plans</h2>
          <p>Experience the future of trading with our revolutionary AI-powered plans</p>
        </div>
        
        <div class="account-cards">
          <div class="account-card">
            <div class="card-header">
              <h3>Smart Trader</h3>
              <div class="card-badge">Free Trial</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">🚀</span>
                <span>Smart Trading Signals</span>
              </div>
              <div class="feature">
                <span class="feature-icon">🛡️</span>
                <span>Auto Stop Loss</span>
              </div>
              <div class="feature">
                <span class="feature-icon">📱</span>
                <span>Mobile Trading</span>
              </div>
            </div>
            <a href="/login" class="btn btn-primary">Start Free</a>
          </div>
          
          <div class="account-card featured">
            <div class="card-header">
              <h3>Pro Trader</h3>
              <div class="card-badge">Most Popular</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">🤖</span>
                <span>AI Trading Bots</span>
              </div>
              <div class="feature">
                <span class="feature-icon">📊</span>
                <span>Real-time Analytics</span>
              </div>
              <div class="feature">
                <span class="feature-icon">⚡</span>
                <span>High-Speed Execution</span>
              </div>
            </div>
            <a href="/login" class="btn btn-primary">Get Pro</a>
          </div>
          
          <div class="account-card">
            <div class="card-header">
              <h3>VIP Trader</h3>
              <div class="card-badge">Premium</div>
            </div>
            <div class="card-features">
              <div class="feature">
                <span class="feature-icon">🎯</span>
                <span>Custom Strategies</span>
              </div>
              <div class="feature">
                <span class="feature-icon">💎</span>
                <span>VIP Support</span>
              </div>
              <div class="feature">
                <span class="feature-icon">👨‍💼</span>
                <span>Personal Manager</span>
              </div>
            </div>
            <a href="/login" class="btn btn-primary">Go VIP</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Trading Features Section -->
    <section class="trading-features">
      <div class="container">
        <div class="section-header fade-in-up">
          <h2>AI-Powered Trading Features</h2>
          <p>Experience the future of trading with cutting-edge AI technology</p>
        </div>
        
        <div class="features-grid">
          <div class="feature-card fade-in-left">
            <div class="feature-icon">🤖</div>
            <h3>AI Trading Bots</h3>
            <p>Automated trading bots that execute strategies 24/7 with 95% accuracy</p>
                          <div class="feature-stats">
                <div class="stat">
                  <span class="stat-value">95%</span>
                  <span class="stat-label">Accuracy Rate</span>
                </div>
                <div class="stat">
                  <span class="stat-value">24/7</span>
                  <span class="stat-label">Active Trading</span>
                </div>
              </div>
          </div>
          
          <div class="feature-card fade-in-up">
            <div class="feature-icon">🧠</div>
            <h3>AI Market Predictions</h3>
            <p>Machine learning algorithms predict market movements with 87% accuracy</p>
                          <div class="feature-stats">
                <div class="stat">
                  <span class="stat-value">87%</span>
                  <span class="stat-label">Prediction Rate</span>
                </div>
                <div class="stat">
                  <span class="stat-value">Real-time</span>
                  <span class="stat-label">Analysis</span>
                </div>
              </div>
          </div>
          
          <div class="feature-card fade-in-right">
            <div class="feature-icon">🎯</div>
            <h3>Smart Portfolio</h3>
            <p>AI-driven portfolio optimization with automatic rebalancing and diversification</p>
                          <div class="feature-stats">
                <div class="stat">
                  <span class="stat-value">Auto</span>
                  <span class="stat-label">Rebalancing</span>
                </div>
                <div class="stat">
                  <span class="stat-value">Smart</span>
                  <span class="stat-label">Diversification</span>
                </div>
              </div>
          </div>
          
          <div class="feature-card fade-in-up">
            <div class="feature-icon">📊</div>
            <h3>Real-time Analytics</h3>
            <p>Advanced analytics dashboard with live market insights and performance metrics</p>
                          <div class="feature-stats">
                <div class="stat">
                  <span class="stat-value">Live</span>
                  <span class="stat-label">Insights</span>
                </div>
                <div class="stat">
                  <span class="stat-value">Advanced</span>
                  <span class="stat-label">Metrics</span>
                </div>
              </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Market Analysis Section -->
    <section class="market-analysis">
      <div class="container">
        <div class="section-header fade-in-up">
          <h2>AI Market Intelligence</h2>
          <p>Get real-time AI-powered market insights and predictions</p>
        </div>
        
        <div class="analysis-grid">
          <div class="analysis-card fade-in-left">
            <div class="analysis-header">
              <h3>NIFTY 50</h3>
              <div class="market-status up">AI Bullish</div>
            </div>
            <div class="analysis-content">
              <div class="price-info">
                <div class="current-price">₹24,563.30</div>
                <div class="price-change up">+434.50 (+1.80%)</div>
              </div>
                              <div class="technical-indicators">
                  <div class="indicator">
                    <span class="indicator-name">AI Score</span>
                    <span class="indicator-value bullish">87.5</span>
                  </div>
                  <div class="indicator">
                    <span class="indicator-name">Prediction</span>
                    <span class="indicator-value bullish">Strong Buy</span>
                  </div>
                  <div class="indicator">
                    <span class="indicator-name">Target</span>
                    <span class="indicator-value">24,800</span>
                  </div>
                  <div class="indicator">
                    <span class="indicator-name">Confidence</span>
                    <span class="indicator-value">92%</span>
                  </div>
                </div>
            </div>
          </div>
          
          <div class="analysis-card fade-in-up">
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
          
          <div class="analysis-card fade-in-right">
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
        <div class="section-header fade-in-up">
          <h2>Learn & Grow</h2>
          <p>Comprehensive educational resources for traders</p>
        </div>
        
        <div class="resources-grid">
          <div class="resource-card fade-in-left">
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
          
          <div class="resource-card fade-in-right">
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
          
          <div class="resource-card fade-in-left">
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
          
          <div class="resource-card fade-in-right">
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
        <div class="section-header fade-in-up">
          <h2>Trading Instruments</h2>
          <p>Trade a wide range of instruments with competitive spreads</p>
        </div>
        
        <div class="instruments-grid">
          <div class="instrument-card fade-in-left">
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
          
          <div class="instrument-card fade-in-right">
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
        <div class="section-header fade-in-up">
          <h2>Platform Features</h2>
          <p>Advanced features designed for professional traders</p>
        </div>
        
        <div class="features-showcase">
          <div class="feature-showcase-item">
            <div class="feature-content fade-in-left">
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
            <div class="feature-image fade-in-right">
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
        <div class="section-header fade-in-up">
          <h2>Success Stories</h2>
          <p>Real traders, real results with {{ brandConfig.companyName }}</p>
        </div>
        
        <div class="stories-grid">
          <div class="story-card fade-in-left">
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
          
          <div class="story-card fade-in-up">
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
          
          <div class="story-card fade-in-right">
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
                    <div class="app-title">{{ brandConfig.companyName }}</div>
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
                    <div class="app-title">{{ brandConfig.companyName }}</div>
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
        <div class="section-header fade-in-up">
          <h2>What Our Traders Say</h2>
          <p>Join thousands of satisfied traders worldwide</p>
        </div>
        
        <div class="testimonials-grid">
          <div class="testimonial-card fade-in-left">
            <div class="testimonial-rating">
              ⭐⭐⭐⭐⭐
            </div>
            <p class="testimonial-text">"{{ brandConfig.companyName }} has transformed my trading experience. The real-time data and advanced tools have helped me make better decisions."</p>
            <div class="testimonial-author">
              <div class="author-avatar">RK</div>
              <div class="author-info">
                <div class="author-name">Rajesh Kumar</div>
                <div class="author-title">Professional Trader</div>
              </div>
            </div>
          </div>
          
          <div class="testimonial-card fade-in-up">
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
          
          <div class="testimonial-card fade-in-right">
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
          <p>Why traders trust {{ brandConfig.companyName }} for their trading needs</p>
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
              <img :src="brandConfig.logoPathFrontend || '../logo.png'" :alt="brandConfig.companyName + ' Logo'" class="logo" />
              <h3>Macxgain</h3>
            </div>
            <p>Your trusted partner for futures and options trading. Start your trading journey with us today.</p>
          </div>
          
          <div class="footer-section">
            <h4>Quick Links</h4>
            <ul>
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
import { getBrandConfig } from '@/config/brand';

export default {
  name: "MacxgainLandingPage",
  data() {
    return {
      // Component data
      mobileMenuOpen: false,
      currentTypingText: '',
      typingIndex: 0,
      typingSpeed: 100,
      typingPause: 2000,
      typingTexts: [
        'Master the Markets with AI-Powered Trading',
        'Revolutionary Machine Learning Algorithms',
        '95.8% Accuracy in Market Predictions',
        'Next-Generation Neural Network Analysis',
        'Automated Trading with AI Intelligence'
      ],
      currentTextIndex: 0,
      isTyping: true,
      typingTimeout: null,
      brandConfig: getBrandConfig()
    };
  },
  computed: {
    // Brand config is reactive through data property
  },
  mounted() {
    // Listen for brand config updates
    const handleUpdate = () => {
      this.brandConfig = getBrandConfig();
    };
    window.addEventListener('brandConfigUpdated', handleUpdate);
    
    this.$options._brandUpdateHandler = handleUpdate;
    
    // Call original mounted logic
    this.initScrollAnimations();
    
    // Start typing effect after a short delay to ensure DOM is ready
    this.$nextTick(() => {
      setTimeout(() => {
        this.startTypingEffect();
      }, 2000);
    });
    
    // Add click outside handler for mobile menu
    document.addEventListener('click', this.handleClickOutside);
  },
  beforeUnmount() {
    // Remove brand config update listener
    if (this.$options._brandUpdateHandler) {
      window.removeEventListener('brandConfigUpdated', this.$options._brandUpdateHandler);
    }
    
    // Remove click outside handler
    document.removeEventListener('click', this.handleClickOutside);
    
    // Clear any pending timeouts
    if (this.typingTimeout) {
      clearTimeout(this.typingTimeout);
    }
  },
  methods: {
    startTypingEffect() {
      // Reset typing state
      this.currentTypingText = '';
      this.typingIndex = 0;
      this.currentTextIndex = 0;
      console.log('Starting AI typing effect...');
      this.typeText();
    },
    
    typeText() {
      const currentText = this.typingTexts[this.currentTextIndex];
      
      if (this.typingIndex < currentText.length) {
        this.currentTypingText += currentText.charAt(this.typingIndex);
        this.typingIndex++;
        this.typingTimeout = setTimeout(() => this.typeText(), this.typingSpeed);
      } else {
        // Pause before erasing
        this.typingTimeout = setTimeout(() => this.eraseText(), this.typingPause);
      }
    },
    
    eraseText() {
      if (this.currentTypingText.length > 0) {
        this.currentTypingText = this.currentTypingText.slice(0, -1);
        this.typingTimeout = setTimeout(() => this.eraseText(), this.typingSpeed / 2);
      } else {
        // Move to next text
        this.currentTextIndex = (this.currentTextIndex + 1) % this.typingTexts.length;
        this.typingIndex = 0;
        this.typingTimeout = setTimeout(() => this.typeText(), 500);
      }
    },
    
    initScrollAnimations() {
      const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -100px 0px'
      };

      const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add('animate-in');
            // Add a small delay for staggered effect
            setTimeout(() => {
              entry.target.style.opacity = '1';
              entry.target.style.transform = 'translateX(0) translateY(0) scale(1)';
            }, 100);
          }
        });
      }, observerOptions);

      // Observe all elements with animation classes
      const animatedElements = document.querySelectorAll('.fade-in-left, .fade-in-right, .fade-in-up, .fade-in-down, .scale-in, .slide-in');
      animatedElements.forEach(el => {
        observer.observe(el);
        // Reset animation state
        el.style.opacity = '0';
        el.style.transform = 'translateX(-100px) translateY(50px) scale(0.8)';
      });
    },
    
    toggleMobileMenu() {
      this.mobileMenuOpen = !this.mobileMenuOpen;
    },
    
    handleClickOutside(event) {
      const mobileMenu = document.querySelector('.nav-menu');
      const mobileToggle = document.querySelector('.mobile-menu-toggle');
      
      if (this.mobileMenuOpen && 
          !mobileMenu?.contains(event.target) && 
          !mobileToggle?.contains(event.target)) {
        this.mobileMenuOpen = false;
      }
    }
  }
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
  scroll-behavior: smooth;
}

/* AI Background Animation */
.ai-background {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  pointer-events: none;
  z-index: 1;
  overflow: hidden;
}

/* Neural Network Visualization */
.neural-network {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.1;
}

.neural-layer {
  position: absolute;
  display: flex;
  justify-content: space-around;
  align-items: center;
  width: 100%;
}

.layer-1 {
  top: 20%;
  height: 60px;
}

.layer-2 {
  top: 50%;
  height: 80px;
}

.layer-3 {
  bottom: 20%;
  height: 60px;
}

.neuron {
  width: 12px;
  height: 12px;
  background: #00ff88;
  border-radius: 50%;
  animation: neuronPulse 3s ease-in-out infinite;
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
}

.neural-connections {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.connection {
  position: absolute;
  height: 1px;
  background: linear-gradient(90deg, transparent, #00ff88, transparent);
  animation: connectionFlow 4s linear infinite;
}

/* AI Particles System */
.ai-particles {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.particle {
  position: absolute;
  width: 4px;
  height: 4px;
  background: #00ff88;
  border-radius: 50%;
  animation: particleFloat 15s linear infinite;
  opacity: 0.6;
}

/* AI Data Streams */
.data-streams {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.2;
}

.stream {
  position: absolute;
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.stream-1 {
  top: 10%;
  left: 5%;
}

.stream-2 {
  top: 30%;
  right: 10%;
}

.stream-3 {
  bottom: 20%;
  left: 15%;
}

.data-bit {
  background: rgba(0, 255, 136, 0.1);
  color: #00ff88;
  padding: 4px 8px;
  border-radius: 4px;
  font-size: 10px;
  font-family: 'Courier New', monospace;
  animation: dataFlow 8s linear infinite;
  border: 1px solid rgba(0, 255, 136, 0.3);
}

/* AI Brain Visualization */
.ai-brain {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  opacity: 0.15;
}

.brain-core {
  width: 100px;
  height: 100px;
  position: relative;
}

.brain-pulse {
  width: 100%;
  height: 100%;
  background: radial-gradient(circle, rgba(0, 255, 136, 0.3) 0%, transparent 70%);
  border-radius: 50%;
  animation: brainPulse 2s ease-in-out infinite;
}

.brain-synapses {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 120px;
  height: 120px;
}

.synapse {
  position: absolute;
  width: 2px;
  height: 20px;
  background: #00ff88;
  top: 50%;
  left: 50%;
  transform-origin: 0 0;
  animation: synapsePulse 1.5s ease-in-out infinite;
}

/* AI Trading Signals */
.ai-signals {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0.1;
}

.signal-wave {
  position: absolute;
  width: 200px;
  height: 2px;
  background: linear-gradient(90deg, transparent, #00ff88, transparent);
  top: 30%;
  animation: signalWave 3s ease-in-out infinite;
}

/* Floating AI Elements */
.floating-ai-elements {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.ai-element {
  position: absolute;
  font-size: 24px;
  animation: aiFloat 6s ease-in-out infinite;
  opacity: 0.3;
}

.element-1 {
  top: 15%;
  left: 10%;
  animation-delay: 0s;
}

.element-2 {
  top: 25%;
  right: 15%;
  animation-delay: 1s;
}

.element-3 {
  top: 60%;
  left: 5%;
  animation-delay: 2s;
}

.element-4 {
  top: 70%;
  right: 20%;
  animation-delay: 3s;
}

.element-5 {
  bottom: 15%;
  left: 25%;
  animation-delay: 4s;
}

.element-6 {
  bottom: 25%;
  right: 10%;
  animation-delay: 5s;
}

/* AI Animations */
@keyframes neuronPulse {
  0%, 100% { 
    transform: scale(1);
    opacity: 0.6;
    box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
  }
  50% { 
    transform: scale(1.2);
    opacity: 1;
    box-shadow: 0 0 30px rgba(0, 255, 136, 0.6);
  }
}

@keyframes connectionFlow {
  0% { 
    opacity: 0;
    transform: scaleX(0);
  }
  50% { 
    opacity: 1;
    transform: scaleX(1);
  }
  100% { 
    opacity: 0;
    transform: scaleX(0);
  }
}

@keyframes particleFloat {
  0% { 
    transform: translateY(100vh) translateX(0);
    opacity: 0;
  }
  10% { 
    opacity: 0.6;
  }
  90% { 
    opacity: 0.6;
  }
  100% { 
    transform: translateY(-100px) translateX(50px);
    opacity: 0;
  }
}

@keyframes dataFlow {
  0% { 
    transform: translateY(100vh);
    opacity: 0;
  }
  10% { 
    opacity: 1;
  }
  90% { 
    opacity: 1;
  }
  100% { 
    transform: translateY(-100px);
    opacity: 0;
  }
}

@keyframes brainPulse {
  0%, 100% { 
    transform: scale(1);
    opacity: 0.3;
  }
  50% { 
    transform: scale(1.1);
    opacity: 0.6;
  }
}

@keyframes synapsePulse {
  0%, 100% { 
    opacity: 0.3;
    transform: scaleY(1);
  }
  50% { 
    opacity: 1;
    transform: scaleY(1.5);
  }
}

@keyframes signalWave {
  0% { 
    transform: translateX(-200px);
    opacity: 0;
  }
  50% { 
    opacity: 1;
  }
  100% { 
    transform: translateX(100vw);
    opacity: 0;
  }
}

@keyframes aiFloat {
  0%, 100% { 
    transform: translateY(0) rotate(0deg);
    opacity: 0.3;
  }
  25% { 
    transform: translateY(-20px) rotate(5deg);
    opacity: 0.6;
  }
  50% { 
    transform: translateY(-10px) rotate(-5deg);
    opacity: 0.4;
  }
  75% { 
    transform: translateY(-30px) rotate(3deg);
    opacity: 0.7;
  }
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
  transition: all 0.3s ease;
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

/* Mobile Menu Toggle */
.mobile-menu-toggle {
  display: none;
  flex-direction: column;
  justify-content: space-between;
  width: 30px;
  height: 20px;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 20;
}

.mobile-menu-toggle span {
  width: 100%;
  height: 3px;
  background-color: white;
  border-radius: 2px;
  transition: all 0.3s ease;
  transform-origin: center;
}

.mobile-menu-toggle.active span:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}

.mobile-menu-toggle.active span:nth-child(2) {
  opacity: 0;
  transform: scaleX(0);
}

.mobile-menu-toggle.active span:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
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
  min-height: 4.2rem;
  display: flex;
  align-items: center;
}

.ai-typing-text {
  color: white;
}

.typing-cursor {
  color: #00ff88;
  animation: cursorBlink 1s infinite;
  margin-left: 4px;
}

.ai-highlight {
  color: #00ff88;
  font-weight: bold;
  text-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
}

@keyframes cursorBlink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0; }
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

/* AI Trading Dashboard Visual */
.hero-visual {
  display: flex;
  justify-content: center;
}

.ai-trading-dashboard {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 16px;
  padding: 1.5rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(10px);
  width: 400px;
  position: relative;
  overflow: hidden;
}

.ai-trading-dashboard::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(0, 255, 136, 0.1), transparent);
  animation: dashboardScan 3s ease-in-out infinite;
}

@keyframes dashboardScan {
  0% { left: -100%; }
  50% { left: 100%; }
  100% { left: 100%; }
}

/* AI Bot Container */
.ai-bot-container {
  display: flex;
  justify-content: center;
  margin-bottom: 1.5rem;
}

.ai-bot {
  width: 80px;
  height: 100px;
  position: relative;
  animation: botFloat 2s ease-in-out infinite;
}

.bot-head {
  width: 60px;
  height: 60px;
  background: linear-gradient(135deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 50%;
  position: relative;
  margin: 0 auto;
  box-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
}

.bot-eyes {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  display: flex;
  gap: 8px;
}

.eye {
  width: 8px;
  height: 8px;
  background: #0a0a1a;
  border-radius: 50%;
  animation: eyeBlink 3s ease-in-out infinite;
}

@keyframes eyeBlink {
  0%, 90%, 100% { transform: scaleY(1); }
  95% { transform: scaleY(0.1); }
}

.bot-antenna {
  position: absolute;
  top: -10px;
  left: 50%;
  transform: translateX(-50%);
  width: 2px;
  height: 15px;
  background: #00ff88;
}

.antenna-signal {
  position: absolute;
  top: -5px;
  left: -2px;
  width: 6px;
  height: 6px;
  background: #00ff88;
  border-radius: 50%;
  animation: signalPulse 1s ease-in-out infinite;
}

@keyframes signalPulse {
  0%, 100% { opacity: 0.3; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.2); }
}

.bot-body {
  width: 70px;
  height: 40px;
  background: rgba(0, 255, 136, 0.1);
  border: 1px solid rgba(0, 255, 136, 0.3);
  border-radius: 8px;
  margin: 0 auto;
  margin-top: 5px;
  position: relative;
  overflow: hidden;
}

.ai-status {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 5px;
  margin-top: 5px;
}

.status-text {
  font-size: 8px;
  color: #00ff88;
  font-weight: bold;
  font-family: 'Courier New', monospace;
}

.status-indicator {
  width: 6px;
  height: 6px;
  background: #00ff88;
  border-radius: 50%;
  animation: statusBlink 1s ease-in-out infinite;
}

@keyframes statusBlink {
  0%, 50% { opacity: 1; }
  51%, 100% { opacity: 0.3; }
}

.processing-lines {
  position: absolute;
  bottom: 5px;
  left: 5px;
  right: 5px;
  display: flex;
  gap: 2px;
}

.line {
  flex: 1;
  height: 2px;
  background: #00ff88;
  border-radius: 1px;
  animation: processingWave 1.5s ease-in-out infinite;
}

@keyframes processingWave {
  0%, 100% { opacity: 0.3; transform: scaleY(1); }
  50% { opacity: 1; transform: scaleY(1.5); }
}

@keyframes botFloat {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-5px); }
}

/* AI Prediction Chart */
.ai-prediction-chart {
  background: rgba(0, 255, 136, 0.05);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid rgba(0, 255, 136, 0.2);
  position: relative;
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-info {
  display: flex;
  flex-direction: column;
}

.chart-title {
  font-weight: 700;
  font-size: 1rem;
  color: #fff;
}

.chart-subtitle {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 2px;
}

.ai-confidence {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.confidence-label {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.6);
}

.confidence-value {
  font-weight: bold;
  font-size: 0.9rem;
  color: #00ff88;
}

.prediction-chart {
  height: 80px;
  position: relative;
  margin-bottom: 1rem;
}

.prediction-line {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 1px;
  animation: predictionFlow 2s ease-in-out infinite;
}

@keyframes predictionFlow {
  0%, 100% { opacity: 0.6; }
  50% { opacity: 1; }
}

.prediction-points {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.prediction-point {
  position: absolute;
  width: 4px;
  height: 4px;
  background: #00ff88;
  border-radius: 50%;
  bottom: 0;
  animation: pointPulse 1.5s ease-in-out infinite;
}

@keyframes pointPulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.5); opacity: 1; }
}

.ai-signals {
  display: flex;
  justify-content: space-between;
  gap: 0.5rem;
}

.signal {
  flex: 1;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: bold;
  text-align: center;
  font-family: 'Courier New', monospace;
}

.buy-signal {
  background: rgba(0, 255, 136, 0.2);
  color: #00ff88;
  border: 1px solid rgba(0, 255, 136, 0.4);
  animation: signalGlow 2s ease-in-out infinite;
}

.hold-signal {
  background: rgba(255, 255, 255, 0.1);
  color: rgba(255, 255, 255, 0.8);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.sell-signal {
  background: rgba(255, 71, 87, 0.2);
  color: #ff4757;
  border: 1px solid rgba(255, 71, 87, 0.4);
}

@keyframes signalGlow {
  0%, 100% { box-shadow: 0 0 5px rgba(0, 255, 136, 0.3); }
  50% { box-shadow: 0 0 15px rgba(0, 255, 136, 0.6); }
}

/* AI Voice Assistant */
.ai-voice-assistant {
  margin-top: 2rem;
  padding: 1rem;
  background: rgba(0, 255, 136, 0.05);
  border: 1px solid rgba(0, 255, 136, 0.2);
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.voice-waves {
  position: absolute;
  top: 50%;
  left: 20px;
  transform: translateY(-50%);
  display: flex;
  align-items: center;
  gap: 3px;
}

.wave {
  width: 3px;
  background: #00ff88;
  border-radius: 2px;
  animation: voiceWave 1.5s ease-in-out infinite;
}

.wave-1 {
  height: 8px;
  animation-delay: 0s;
}

.wave-2 {
  height: 12px;
  animation-delay: 0.2s;
}

.wave-3 {
  height: 16px;
  animation-delay: 0.4s;
}

.wave-4 {
  height: 10px;
  animation-delay: 0.6s;
}

@keyframes voiceWave {
  0%, 100% { 
    transform: scaleY(0.5);
    opacity: 0.6;
  }
  50% { 
    transform: scaleY(1);
    opacity: 1;
  }
}

.voice-text {
  margin-left: 60px;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.voice-label {
  font-size: 0.8rem;
  color: #00ff88;
  font-weight: bold;
  font-family: 'Courier New', monospace;
}

.voice-message {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.9);
  font-style: italic;
  animation: voiceTyping 3s ease-in-out infinite;
}

@keyframes voiceTyping {
  0%, 80%, 100% { opacity: 1; }
  85%, 95% { opacity: 0.5; }
}

.main-chart {
  background: rgba(0, 255, 136, 0.05);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1rem;
  border: 1px solid rgba(0, 255, 136, 0.2);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.chart-info {
  display: flex;
  flex-direction: column;
}

.chart-title {
  font-weight: 700;
  font-size: 1.2rem;
  color: #fff;
}

.chart-subtitle {
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 2px;
}

.chart-price-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
}

.chart-price {
  font-weight: 700;
  font-size: 1.1rem;
  color: #fff;
}

.chart-change.up {
  color: #00ff88;
  font-weight: 600;
}

.chart-change.down {
  color: #ff4757;
  font-weight: 600;
}

.chart-body {
  height: 120px;
  position: relative;
}

.price-chart {
  height: 80px;
  position: relative;
  margin-bottom: 1rem;
}

.chart-line {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 2px;
  background: linear-gradient(90deg, #00ff88 0%, #00d4aa 100%);
  border-radius: 1px;
}

.chart-points {
  position: absolute;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: end;
  gap: 8px;
}

.point {
  width: 3px;
  border-radius: 1px;
}

.point.up {
  height: 60%;
  background: #00ff88;
}

.point.down {
  height: 40%;
  background: #ff4757;
}

.chart-stats {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
}

.chart-stats .stat {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.chart-stats .stat-label {
  color: rgba(255, 255, 255, 0.6);
  margin-bottom: 2px;
}

.chart-stats .stat-value {
  color: #fff;
  font-weight: 600;
}

/* Mini Charts */
.mini-charts {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.mini-chart {
  flex: 1;
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  padding: 0.8rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.mini-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
}

.mini-title {
  font-size: 0.7rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.mini-price {
  font-size: 0.7rem;
  font-weight: 600;
}

.mini-price.up {
  color: #00ff88;
}

.mini-price.down {
  color: #ff4757;
}

.mini-line {
  height: 2px;
  border-radius: 1px;
}

.mini-line.up {
  background: linear-gradient(90deg, #00ff88 0%, #00d4aa 100%);
}

.mini-line.down {
  background: linear-gradient(90deg, #ff4757 0%, #ff6b6b 100%);
}

/* Live Ticker */
.live-ticker {
  background: rgba(255, 255, 255, 0.03);
  border-radius: 8px;
  padding: 0.8rem;
  border: 1px solid rgba(255, 255, 255, 0.05);
}

.ticker-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.5rem;
  font-size: 0.8rem;
}

.ticker-item:last-child {
  margin-bottom: 0;
}

.ticker-symbol {
  font-weight: 600;
  color: rgba(255, 255, 255, 0.8);
}

.ticker-price {
  font-weight: 600;
}

.ticker-price.up {
  color: #00ff88;
}

.ticker-price.down {
  color: #ff4757;
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

/* Enhanced Scroll Animations - Vidyagxp Style */
.fade-in-left {
  opacity: 0;
  transform: translateX(-100px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-right {
  opacity: 0;
  transform: translateX(100px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-up {
  opacity: 0;
  transform: translateY(80px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.fade-in-down {
  opacity: 0;
  transform: translateY(-80px);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scale-in {
  opacity: 0;
  transform: scale(0.6);
  transition: all 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.slide-in {
  opacity: 0;
  transform: translateX(-150px) translateY(30px);
  transition: all 1.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.animate-in {
  opacity: 1 !important;
  transform: translateX(0) translateY(0) scale(1) !important;
}

/* Smooth entrance animations */
@keyframes slideInFromLeft {
  from {
    opacity: 0;
    transform: translateX(-100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInFromRight {
  from {
    opacity: 0;
    transform: translateX(100px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes slideInFromBottom {
  from {
    opacity: 0;
    transform: translateY(80px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInScale {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Enhanced Stagger animations for grid items - Vidyagxp Style */
.features-grid .feature-card:nth-child(1) { transition-delay: 0.2s; }
.features-grid .feature-card:nth-child(2) { transition-delay: 0.4s; }
.features-grid .feature-card:nth-child(3) { transition-delay: 0.6s; }
.features-grid .feature-card:nth-child(4) { transition-delay: 0.8s; }

.analysis-grid .analysis-card:nth-child(1) { transition-delay: 0.2s; }
.analysis-grid .analysis-card:nth-child(2) { transition-delay: 0.4s; }
.analysis-grid .analysis-card:nth-child(3) { transition-delay: 0.6s; }

.resources-grid .resource-card:nth-child(1) { transition-delay: 0.2s; }
.resources-grid .resource-card:nth-child(2) { transition-delay: 0.4s; }
.resources-grid .resource-card:nth-child(3) { transition-delay: 0.6s; }
.resources-grid .resource-card:nth-child(4) { transition-delay: 0.8s; }

.instruments-grid .instrument-card:nth-child(1) { transition-delay: 0.2s; }
.instruments-grid .instrument-card:nth-child(2) { transition-delay: 0.4s; }
.instruments-grid .instrument-card:nth-child(3) { transition-delay: 0.6s; }

.stories-grid .story-card:nth-child(1) { transition-delay: 0.2s; }
.stories-grid .story-card:nth-child(2) { transition-delay: 0.4s; }
.stories-grid .story-card:nth-child(3) { transition-delay: 0.6s; }

.account-cards .account-card:nth-child(1) { transition-delay: 0.2s; }
.account-cards .account-card:nth-child(2) { transition-delay: 0.4s; }
.account-cards .account-card:nth-child(3) { transition-delay: 0.6s; }

.testimonials-grid .testimonial-card:nth-child(1) { transition-delay: 0.2s; }
.testimonials-grid .testimonial-card:nth-child(2) { transition-delay: 0.4s; }
.testimonials-grid .testimonial-card:nth-child(3) { transition-delay: 0.6s; }

.stats-grid .stat-item:nth-child(1) { transition-delay: 0.2s; }
.stats-grid .stat-item:nth-child(2) { transition-delay: 0.4s; }
.stats-grid .stat-item:nth-child(3) { transition-delay: 0.6s; }
.stats-grid .stat-item:nth-child(4) { transition-delay: 0.8s; }

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

/* Enhanced Responsive Design */
@media (max-width: 768px) {
  .container {
    padding: 0 1rem;
  }
  
  .hero-container {
    grid-template-columns: 1fr;
    gap: 2rem;
    text-align: center;
  }
  
  .hero-title {
    font-size: 2.2rem;
    line-height: 1.2;
  }
  
  .hero-subtitle {
    font-size: 1rem;
    line-height: 1.6;
  }
  
  .hero-actions {
    flex-direction: column;
    gap: 1rem;
    align-items: center;
  }
  
  .hero-stats {
    justify-content: center;
    gap: 2rem;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
  
  .stat-label {
    font-size: 0.9rem;
  }
  
  /* AI Trading Dashboard Mobile */
  .ai-trading-dashboard {
    width: 100%;
    max-width: 350px;
    margin: 0 auto;
  }
  
  .ai-prediction-chart {
    padding: 1rem;
  }
  
  .chart-title {
    font-size: 0.9rem;
  }
  
  .chart-subtitle {
    font-size: 0.6rem;
  }
  
  .confidence-value {
    font-size: 0.8rem;
  }
  
  .ai-bot {
    width: 60px;
    height: 80px;
  }
  
  .bot-head {
    width: 50px;
    height: 50px;
  }
  
  .bot-body {
    width: 60px;
    height: 35px;
  }
  
  .status-text {
    font-size: 7px;
  }
  
  .signal {
    font-size: 0.6rem;
    padding: 0.2rem 0.4rem;
  }
  
  /* AI Voice Assistant Mobile */
  .ai-voice-assistant {
    margin-top: 1.5rem;
    padding: 0.8rem;
  }
  
  .voice-waves {
    left: 15px;
  }
  
  .voice-text {
    margin-left: 50px;
  }
  
  .voice-label {
    font-size: 0.7rem;
  }
  
  .voice-message {
    font-size: 0.8rem;
  }
  
  .mini-charts {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .mini-chart {
    padding: 0.6rem;
  }
  
  .mini-title {
    font-size: 0.6rem;
  }
  
  .mini-price {
    font-size: 0.6rem;
  }
  
  .live-ticker {
    padding: 0.6rem;
  }
  
  .ticker-item {
    font-size: 0.7rem;
  }
  
  /* Header Mobile Responsive */
  .header-container {
    flex-direction: row;
    gap: 1rem;
    text-align: center;
    position: relative;
  }
  
  .mobile-menu-toggle {
    display: flex;
  }
  
  /* Navigation */
  .nav-menu {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: rgba(10, 10, 26, 0.98);
    backdrop-filter: blur(15px);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    padding: 1rem 0;
    z-index: 1000;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
  }
  
  .nav-menu.mobile-open {
    display: flex !important;
    flex-direction: column;
    gap: 0;
  }
  
  .nav-menu .nav-link {
    display: block;
    padding: 1rem 2rem;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
  }
  
  .nav-menu .nav-link:last-child {
    border-bottom: none;
  }
  
  .nav-menu .nav-link:hover {
    background: rgba(0, 255, 136, 0.1);
    color: #00ff88;
  }
  
  .auth-buttons {
    gap: 0.5rem;
  }
  
  .btn {
    padding: 0.5rem 1rem;
    font-size: 0.9rem;
  }
  
  /* Sections */
  .section-header h2 {
    font-size: 1.8rem;
  }
  
  .section-header p {
    font-size: 1rem;
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
    gap: 1.5rem;
  }
  
  .account-card,
  .feature-card,
  .analysis-card,
  .resource-card,
  .instrument-card,
  .story-card,
  .testimonial-card {
    padding: 1.5rem;
  }
  
  .feature-icon {
    font-size: 2rem;
  }
  
  .feature-card h3 {
    font-size: 1.2rem;
  }
  
  .feature-card p {
    font-size: 0.9rem;
  }
  
  /* Showcase Items */
  .feature-showcase-item {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }
  
  .feature-showcase-item.reverse {
    direction: ltr;
  }
  
  .phone-mockup {
    width: 200px;
    height: 350px;
  }
  
  .data-display,
  .chart-display {
    min-width: auto;
    width: 100%;
    padding: 1rem;
  }
  
  .data-items,
  .chart-content {
    font-size: 0.8rem;
  }
  
  /* Statistics */
  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
  }
  
  .stat-number {
    font-size: 1.8rem;
  }
  
  .stat-label {
    font-size: 0.9rem;
  }
  
  /* Story Stats */
  .story-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
  }
  
  .story-stats .stat:last-child {
    grid-column: span 2;
  }
  
  .story-stats .stat-value {
    font-size: 1rem;
  }
  
  .story-stats .stat-label {
    font-size: 0.7rem;
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
    padding: 0 0.8rem;
  }
  
  /* Header Mobile Portrait */
  .header-container {
    padding: 0 1rem;
    flex-wrap: wrap;
    gap: 0.75rem;
  }
  
  .logo-section {
    flex: 1;
    min-width: 0;
  }
  
  .brand-name {
    font-size: 1.2rem;
  }
  
  .mobile-menu-toggle {
    order: 2;
    margin-left: auto;
  }
  
  .auth-buttons {
    order: 3;
    width: 100%;
    flex-direction: row;
    gap: 0.5rem;
    justify-content: center;
  }
  
  .btn {
    flex: 1;
    max-width: 120px;
    text-align: center;
    padding: 0.5rem 0.75rem;
    font-size: 0.85rem;
  }
  
  .nav-menu {
    order: 4;
    width: 100%;
    margin-top: 0.5rem;
  }
  
  .hero-container {
    gap: 1.5rem;
  }
  
  .hero-title {
    font-size: 1.8rem;
  }
  
  .hero-subtitle {
    font-size: 0.9rem;
  }
  
  .trust-badge {
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
  }
  
  .hero-actions {
    gap: 0.8rem;
  }
  
  .btn {
    padding: 0.8rem 1.2rem;
    font-size: 0.85rem;
  }
  
  .hero-stats {
    gap: 1.5rem;
  }
  
  .stat-number {
    font-size: 1.5rem;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
  
  /* AI Trading Dashboard Small Mobile */
  .ai-trading-dashboard {
    max-width: 300px;
    padding: 1rem;
  }
  
  .ai-prediction-chart {
    padding: 0.8rem;
  }
  
  .chart-title {
    font-size: 0.8rem;
  }
  
  .chart-subtitle {
    font-size: 0.6rem;
  }
  
  .confidence-value {
    font-size: 0.7rem;
  }
  
  .ai-bot {
    width: 50px;
    height: 70px;
  }
  
  .bot-head {
    width: 40px;
    height: 40px;
  }
  
  .bot-body {
    width: 50px;
    height: 30px;
  }
  
  .status-text {
    font-size: 6px;
  }
  
  .signal {
    font-size: 0.5rem;
    padding: 0.15rem 0.3rem;
  }
  
  .prediction-chart {
    height: 60px;
  }
  
  /* AI Voice Assistant Small Mobile */
  .ai-voice-assistant {
    margin-top: 1rem;
    padding: 0.6rem;
  }
  
  .voice-waves {
    left: 10px;
  }
  
  .voice-text {
    margin-left: 40px;
  }
  
  .voice-label {
    font-size: 0.6rem;
  }
  
  .voice-message {
    font-size: 0.7rem;
  }
  
  .wave {
    width: 2px;
  }
  
  .wave-1 { height: 6px; }
  .wave-2 { height: 8px; }
  .wave-3 { height: 10px; }
  .wave-4 { height: 7px; }
  
  .mini-charts {
    gap: 0.3rem;
  }
  
  .mini-chart {
    padding: 0.5rem;
  }
  
  .mini-title {
    font-size: 0.5rem;
  }
  
  .mini-price {
    font-size: 0.5rem;
  }
  
  .live-ticker {
    padding: 0.5rem;
  }
  
  .ticker-item {
    font-size: 0.6rem;
    margin-bottom: 0.3rem;
  }
  
  /* Sections */
  .section-header h2 {
    font-size: 1.5rem;
  }
  
  .section-header p {
    font-size: 0.9rem;
  }
  
  .account-card,
  .tool-card,
  .feature-card,
  .analysis-card,
  .resource-card,
  .instrument-card,
  .story-card {
    padding: 1.2rem;
  }
  
  .feature-icon {
    font-size: 1.8rem;
  }
  
  .feature-card h3 {
    font-size: 1.1rem;
  }
  
  .feature-card p {
    font-size: 0.8rem;
  }
  
  .feature-stats .stat-value {
    font-size: 0.9rem;
  }
  
  .feature-stats .stat-label {
    font-size: 0.7rem;
  }
  
  /* Phone Mockup */
  .phone-mockup {
    width: 150px;
    height: 280px;
  }
  
  .data-display,
  .chart-display {
    padding: 0.8rem;
  }
  
  .data-items,
  .chart-content {
    font-size: 0.7rem;
  }
  
  /* Story Elements */
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
    font-size: 1.3rem;
  }
  
  .feature-content p {
    font-size: 0.8rem;
  }
  
  .story-text {
    font-size: 0.9rem;
  }
  
  .story-stats .stat-value {
    font-size: 0.9rem;
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