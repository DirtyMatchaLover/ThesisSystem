# How the Bubble Animation Works

## Overview
The floating bubbles/particles on the role select page are created using **pure CSS animations** and **vanilla JavaScript** - no libraries needed!

---

## Step 1: HTML Structure

```html
<!-- Container for particles -->
<div class="particles" id="particles"></div>
```

This empty div holds all the animated particles.

---

## Step 2: CSS Styling

### Particle Container
```css
.particles {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    z-index: 0;          /* Behind content */
    pointer-events: none; /* Can't click them */
}
```

### Individual Particle Style
```css
.particle {
    position: absolute;
    background: var(--accent-primary);  /* Color from theme */
    border-radius: 50%;                 /* Makes it circular */
    animation: float 15s infinite;      /* Apply animation */
    opacity: 0.1;                       /* Subtle transparency */
}
```

### Animation Keyframes
```css
@keyframes float {
    0%, 100% {
        transform: translateY(0) translateX(0) rotate(0deg);
        opacity: 0;  /* Start invisible */
    }
    10% {
        opacity: 0.1;  /* Fade in */
    }
    90% {
        opacity: 0.1;  /* Stay visible */
    }
    100% {
        transform: translateY(-100vh) translateX(100px) rotate(360deg);
        opacity: 0;  /* Fade out at top */
    }
}
```

**What this does:**
- Particles start at bottom (invisible)
- Fade in as they rise
- Move upward (`-100vh` = full screen height)
- Drift sideways (`translateX(100px)`)
- Rotate 360 degrees while floating
- Fade out when reaching top
- Loop infinitely

---

## Step 3: JavaScript - Creating the Particles

```javascript
// Get the container
const particlesContainer = document.getElementById('particles');

// How many bubbles to create
const particleCount = 30;

// Create each particle
for (let i = 0; i < particleCount; i++) {
    // 1. Create a div element
    const particle = document.createElement('div');

    // 2. Give it the 'particle' class (applies CSS styling)
    particle.className = 'particle';

    // 3. Random size between 20px and 80px
    const size = Math.random() * 60 + 20;
    particle.style.width = size + 'px';
    particle.style.height = size + 'px';

    // 4. Random horizontal position (0% to 100% of screen width)
    particle.style.left = Math.random() * 100 + '%';

    // 5. Start below the visible area
    particle.style.bottom = '-' + size + 'px';

    // 6. Random start delay (0 to 15 seconds)
    //    Makes bubbles appear at different times
    particle.style.animationDelay = Math.random() * 15 + 's';

    // 7. Random animation duration (10 to 20 seconds)
    //    Makes some bubbles float faster than others
    particle.style.animationDuration = (Math.random() * 10 + 10) + 's';

    // 8. Add the particle to the container
    particlesContainer.appendChild(particle);
}
```

---

## How It All Works Together

1. **Page Loads** → JavaScript creates 30 div elements
2. **Each div** gets:
   - Random size
   - Random horizontal position
   - Random start delay
   - Random speed
3. **CSS Animation** makes them:
   - Float upward
   - Drift sideways
   - Rotate slowly
   - Fade in and out
4. **Infinite loop** - when they reach the top, they reset and start again

---

## Dark Mode Support

The bubbles change color automatically based on theme:

```css
/* Light mode - brown/gold */
.particle {
    background: var(--accent-primary);  /* #7b3f00 */
    opacity: 0.1;
}

/* Dark mode - bright gold */
body.dark-theme .particle {
    background: var(--accent-primary);  /* #ffa726 */
    opacity: 0.15;  /* Slightly brighter */
}
```

---

## Customization Options

### Want More/Fewer Bubbles?
```javascript
const particleCount = 50;  // Change this number
```

### Want Faster Animation?
```javascript
particle.style.animationDuration = (Math.random() * 5 + 5) + 's';  // 5-10 seconds
```

### Want Different Colors?
```css
.particle {
    background: #ff6b6b;  /* Red bubbles */
}
```

### Want Bigger Bubbles?
```javascript
const size = Math.random() * 100 + 50;  // 50-150px
```

### Want to Change Direction?
```css
@keyframes float {
    100% {
        transform: translateY(-100vh) translateX(-100px);  /* Drift left instead */
    }
}
```

---

## Performance Notes

- **30 particles** = smooth on most devices
- **No external libraries** = Fast loading
- **CSS animations** = Hardware accelerated
- **No JavaScript during animation** = Efficient

---

## Browser Support

✅ Works in all modern browsers:
- Chrome
- Firefox
- Safari
- Edge
- Mobile browsers

---

## Summary

**3 Simple Parts:**
1. CSS defines how particles look and animate
2. JavaScript creates 30 particle divs with random properties
3. Browser handles the animation automatically

That's it! No complex physics or libraries needed. 🎈✨
