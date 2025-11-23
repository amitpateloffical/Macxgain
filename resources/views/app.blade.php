<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <!-- Favicon - will be updated dynamically via JavaScript -->
    <!-- Favicon - prioritize PNG, fallback to ICO -->
    <link rel="icon" type="image/png" sizes="32x32" href="/logo.png" id="favicon-link" />
    <link rel="icon" type="image/png" sizes="16x16" href="/logo.png" />
    <link rel="shortcut icon" type="image/png" href="/logo.png" id="shortcut-icon" />
    <link rel="apple-touch-icon" href="/logo.png" id="apple-icon" />
    <!-- Fallback for browsers that require .ico -->
    <link rel="icon" type="image/x-icon" href="/logo.png" id="favicon-ico" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title id="page-title">Macxgain - Best Trading App – Trade Smarter, Faster, Safer</title>
    <meta name="description" content="Macxgain - The best trading platform for futures and options. Trade smarter, faster, and safer with our advanced tools and 24/7 support." />
    <meta name="keywords" content="trading, futures, options, forex, stocks, cryptocurrency, trading platform, Macxgain" />
    <meta name="application-name" content="Macxgain" id="app-name" />
    <meta name="apple-mobile-web-app-title" content="Macxgain" id="apple-app-title" />
    <meta name="theme-color" content="#00ff88" />
    <meta property="og:title" content="Macxgain - Best Trading App – Trade Smarter, Faster, Safer" id="og-title" />
    <meta property="og:description" content="The best trading platform for futures and options. Trade smarter, faster, and safer with our advanced tools and 24/7 support." />
    <meta property="og:type" content="website" />
    <meta property="og:image" content="/logo.png" id="og-image" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="Macxgain - Best Trading App – Trade Smarter, Faster, Safer" id="twitter-title" />
    <meta name="twitter:description" content="The best trading platform for futures and options. Trade smarter, faster, and safer with our advanced tools and 24/7 support." />
    <meta name="twitter:image" content="/logo.png" id="twitter-image" />
    <script>
      // Update favicon and meta tags from localStorage on page load
      (function() {
        try {
          const saved = localStorage.getItem('brandSettings');
          if (saved) {
            const config = JSON.parse(saved);
            const faviconLink = document.getElementById('favicon-link');
            const pageTitle = document.getElementById('page-title');
            const appName = document.getElementById('app-name');
            const appleAppTitle = document.getElementById('apple-app-title');
            const ogTitle = document.getElementById('og-title');
            const ogImage = document.getElementById('og-image');
            const twitterTitle = document.getElementById('twitter-title');
            const twitterImage = document.getElementById('twitter-image');
            
            // Update favicon if logo is base64
            if (config.logoPath && config.logoPath.startsWith('data:image')) {
              if (faviconLink) {
                faviconLink.href = config.logoPath;
                faviconLink.type = 'image/png';
              }
              const shortcutIcon = document.getElementById('shortcut-icon');
              if (shortcutIcon) {
                shortcutIcon.href = config.logoPath;
                shortcutIcon.type = 'image/png';
              }
              const appleIcon = document.getElementById('apple-icon');
              if (appleIcon) appleIcon.href = config.logoPath;
              const faviconIco = document.getElementById('favicon-ico');
              if (faviconIco) faviconIco.href = config.logoPath;
            } else if (config.logoPathPublic) {
              // Use public logo path if available
              if (faviconLink) faviconLink.href = config.logoPathPublic;
              const shortcutIcon = document.getElementById('shortcut-icon');
              if (shortcutIcon) shortcutIcon.href = config.logoPathPublic;
              const appleIcon = document.getElementById('apple-icon');
              if (appleIcon) appleIcon.href = config.logoPathPublic;
            }
            
            // Update page title
            if (config.pageTitle && pageTitle) {
              pageTitle.textContent = config.pageTitle;
            }
            
            // Update meta tags
            if (config.companyName) {
              if (appName) appName.content = config.companyName;
              if (appleAppTitle) appleAppTitle.content = config.companyName;
              if (ogTitle) ogTitle.content = config.companyName + ' - Best Trading App';
              if (twitterTitle) twitterTitle.content = config.companyName + ' - Best Trading App';
            }
            
            if (config.logoPath && ogImage) {
              ogImage.content = config.logoPath.startsWith('data:') ? config.logoPath : '/logo.png';
            }
            if (config.logoPath && twitterImage) {
              twitterImage.content = config.logoPath.startsWith('data:') ? config.logoPath : '/logo.png';
            }
          }
        } catch (e) {
          console.warn('Failed to load brand settings for favicon:', e);
        }
      })();
    </script>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Fira+Sans:ital,wght@0,400;0,700;0,900;1,400;1,700;1,900&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Material+Icons" rel="stylesheet">
  </head>
  
<div id="app"></div>

@vite('resources/js/app.js')
    
</body>
</html>
<!-- <style scoped>
#app {
  background-color: #c3bff6; 
  min-height: 100vh;
}
</style> -->