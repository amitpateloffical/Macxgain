# Template System Documentation

## Overview
The Template System allows administrators to change the entire website's color scheme with a single click. The system includes 5 predefined color themes that can be selected from the Admin Dashboard.

## Features
- **5 Predefined Templates**: Choose from Green Tech, Blue Ocean, Purple Royal, Orange Fire, or Cyan Modern
- **Instant Preview**: Preview templates before applying
- **Global Application**: Changes apply to all pages, components, buttons, cards, and UI elements
- **Persistent Storage**: Selected template is saved in localStorage
- **CSS Variables**: Uses CSS custom properties for dynamic theming

## Accessing Template Settings

1. Log in to the Admin Dashboard
2. Navigate to **Template Settings** from the dashboard cards
3. Or go directly to `/admin/template-settings`

## Available Templates

### 1. Green Tech (Default)
- **Primary Color**: #00ff88 (Bright Green)
- **Background**: Dark navy (#0d0d1a, #101022)
- **Theme**: Modern tech with vibrant green accents
- **Best For**: Tech companies, trading platforms, modern applications

### 2. Blue Ocean
- **Primary Color**: #3b82f6 (Blue)
- **Background**: Deep blue (#0f172a, #1e293b)
- **Theme**: Professional and calm
- **Best For**: Corporate websites, professional services

### 3. Purple Royal
- **Primary Color**: #8b5cf6 (Purple)
- **Background**: Deep purple (#1a0b2e, #2d1b3d)
- **Theme**: Elegant and luxurious
- **Best For**: Premium services, luxury brands

### 4. Orange Fire
- **Primary Color**: #f97316 (Orange)
- **Background**: Dark orange (#1c0a00, #2d1500)
- **Theme**: Energetic and vibrant
- **Best For**: Creative agencies, energetic brands

### 5. Cyan Modern
- **Primary Color**: #06b6d4 (Cyan)
- **Background**: Dark cyan (#0a1628, #1a2838)
- **Theme**: Fresh and modern
- **Best For**: Modern startups, fresh brands

## How to Use

### Selecting a Template
1. Click on any template card to select it
2. The selected template will be highlighted with a border
3. Click **Preview** to see the template without saving
4. Click **Apply Template** to save and apply permanently

### Preview vs Apply
- **Preview**: Temporarily applies the template without saving. Refresh the page to see the permanent template.
- **Apply Template**: Saves the template to localStorage and reloads the page to apply it everywhere.

## Technical Implementation

### File Structure
```
resources/
├── js/src/
│   ├── config/
│   │   └── templates.js          # Template definitions and functions
│   └── views/AdminSide/
│       └── TemplateSettings.vue  # Template selection UI
└── css/
    └── template-themes.css        # Global CSS variables and overrides
```

### CSS Variables
The system uses CSS custom properties (variables) that are dynamically updated:

```css
:root {
  --color-primary: #00ff88;
  --color-bg-primary: #0d0d1a;
  --color-bg-secondary: #101022;
  --color-text-primary: #ffffff;
  /* ... more variables */
}
```

### Component Integration
Components should use CSS variables instead of hardcoded colors:

```css
/* Good - Uses CSS variables */
.my-component {
  background-color: var(--color-bg-primary);
  color: var(--color-text-primary);
  border-color: var(--color-border-primary);
}

/* Bad - Hardcoded colors */
.my-component {
  background-color: #0d0d1a;
  color: white;
}
```

### JavaScript API

#### Get Current Template
```javascript
import { getCurrentTemplate } from '@/config/templates';

const current = getCurrentTemplate();
console.log(current.name); // "Green Tech (Default)"
```

#### Apply Template Programmatically
```javascript
import { applyTemplate } from '@/config/templates';

// Apply template
applyTemplate('template2'); // Applies Blue Ocean theme
```

#### Listen for Template Changes
```javascript
window.addEventListener('templateChanged', (event) => {
  const template = event.detail;
  console.log('Template changed to:', template.name);
});
```

## Customization

### Adding New Templates
To add a new template, edit `resources/js/src/config/templates.js`:

```javascript
export const templates = {
  // ... existing templates
  template6: {
    id: 'template6',
    name: 'My Custom Template',
    description: 'Custom theme description',
    colors: {
      primary: '#your-color',
      primaryDark: '#darker-color',
      // ... all required color properties
    }
  }
};
```

### Modifying Existing Templates
Edit the `colors` object in `resources/js/src/config/templates.js` for any template.

## Browser Compatibility
- Modern browsers (Chrome, Firefox, Safari, Edge)
- CSS Custom Properties (CSS Variables) support required
- localStorage support required

## Notes
- Template changes require a page reload to apply everywhere
- Brand settings (logo, company name) are independent of templates
- Templates only affect colors, not layout or structure
- Selected template persists across browser sessions

## Troubleshooting

### Template Not Applying
1. Clear browser cache and reload
2. Check browser console for errors
3. Verify localStorage is enabled
4. Check that `template-themes.css` is loaded

### Colors Not Updating
1. Ensure components use CSS variables, not hardcoded colors
2. Check for `!important` rules that might override variables
3. Verify template was saved to localStorage

### Preview Not Working
1. Check browser console for JavaScript errors
2. Verify template ID is valid
3. Ensure `applyTemplate` function is imported correctly

