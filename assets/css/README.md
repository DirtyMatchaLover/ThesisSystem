# 🎨 CSS Organization Guide

## File Structure

```
assets/css/
├── base.css        # Reset, base styles, utilities
├── header.css      # Header, logo, search, navigation
├── dropdown.css    # Dropdown menus & animations
├── homepage.css    # Homepage layout & thesis cards
├── components.css  # Buttons, forms, cards, alerts
├── footer.css      # Footer & responsive styles
└── README.md       # This file
```

## Loading Order (Important!)

The CSS files are loaded in this specific order in `header.php`:

1. **Bootstrap CSS** (CDN)
2. **base.css** - Foundation styles
3. **header.css** - Header & navigation
4. **dropdown.css** - Dropdown functionality
5. **homepage.css** - Homepage layout
6. **components.css** - Reusable components
7. **footer.css** - Footer & responsive

## What Each File Contains

### 📄 base.css
- CSS Reset (`* { margin: 0; padding: 0; }`)
- Body & typography base styles
- Utility classes (margins, text-align, etc.)
- Common link styles

### 🏠 header.css
- Site header layout
- Logo styling
- Search container
- Main navigation bar
- Header responsive design

### 📋 dropdown.css
- Research dropdown menu
- Dropdown animations
- Hover effects
- Mobile dropdown behavior

### 🏡 homepage.css
- Homepage layout
- Welcome section
- Statistics display
- Thesis grid & cards
- Empty state styling

### 🔧 components.css
- Buttons (primary, secondary, etc.)
- Form controls
- Cards & alerts
- Status badges
- Reusable components

### 👣 footer.css
- Footer layout
- Responsive breakpoints
- Mobile optimizations
- Print styles

## Adding New Styles

### For new components:
Add to `components.css`

### For new pages:
Create a new CSS file (e.g., `research.css`) and add to header.php

### For responsive fixes:
Add to `footer.css` in the appropriate `@media` query

## Troubleshooting

### If styles don't load:
1. Check file paths in header.php
2. Ensure Docker is restarted: `docker-compose restart`
3. Check browser developer tools for 404 errors

### If dropdown doesn't work:
- Check `dropdown.css` is loaded after `header.css`
- Verify the HTML structure matches the CSS selectors

### For responsive issues:
- Check `footer.css` media queries
- Test on different screen sizes
- Use browser developer tools

## Best Practices

1. **Keep files focused** - Each file has a specific purpose
2. **Use consistent naming** - Follow BEM or similar methodology
3. **Add comments** - Explain complex styles
4. **Test responsive** - Check mobile, tablet, desktop
5. **Optimize loading** - Keep file sizes reasonable

## Quick Commands

```bash
# Restart Docker to reload CSS
docker-compose restart

# Check if CSS files exist
ls -la assets/css/

# View CSS file
cat assets/css/dropdown.css
```