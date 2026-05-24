# Landing Page Plan: yourlink.app

## 1. Visual Direction & Aesthetic
*   **Style:** Modern SaaS "Dark/Light Mode" hybrid. Clean, high-contrast, and professional.
*   **Color Palette:**
    *   Primary: Electric Blue (`#3B82F6`) for actions.
    *   Secondary: Deep Indigo/Slate for depth.
    *   Accents: Vibrant Teal for success states, Soft Amber for warnings.
*   **Typography:** Modern Sans-serif (Inter or Geist) with bold headings and high readability.
*   **Animations:**
    *   **Entrance:** Fade-in and slide-up for hero elements using Tailwind 4 transitions.
    *   **Micro-interactions:** Magnetic buttons, hover scaling, and subtle "shimmer" effects on CTA buttons.
    *   **Scroll Reveal:** Elements emerge as the user scrolls down.

## 2. Dynamic Shortener Component (Livewire 4)
The core of the landing page is the "Shorten" form, which adapts to the user's session.

### A. Guest Mode (Minimalist)
*   **Input:** Single large "Paste your long URL" field.
*   **Action:** "Shorten" button.
*   **Validation:** Real-time URL format check.
*   **Post-Action:** Display shortened link + "Copy" button + "Sign up for custom aliases" nudge.
*   **Security:** Invisible reCaptcha integration.

### B. Authenticated Mode (High Customization)
*   **Main Input:** "Paste your long URL".
*   **Customization Drawer (Expandable):**
    *   **Custom Alias:** `yourlink.app/ [ custom-slug ]`
    *   **Password Protection:** Toggle + Password input.
    *   **Expiration:** Date/Time picker for link self-destruction.
    *   **Click Limit:** Numeric input.
*   **Action:** "Create Magic Link".
*   **Analytics Preview:** Small sparkline showing potential traffic (placeholder).

## 3. Page Sections (Order of Appearance)

### 1. Navigation Header
*   Glassmorphism effect on scroll.
*   Logo (yourlink.app).
*   Links: Features, Pricing, Business.
*   Dynamic CTA: "Dashboard" (Auth) vs "Login/Get Started" (Guest).

### 2. Hero Section (The "Hook")
*   **Headline:** "Links with Superpowers."
*   **Sub-headline:** "Shorten, track, and brand your links in seconds. 100% Free for individuals. 100% DSGVO-conform."
*   **Primary Tool:** The Livewire Dynamic Shortener Form (placed front-and-center).
*   **Background:** Subtle animated gradient mesh or SVG pattern.

### 3. Features "Bento Grid"
*   **Privacy First:** Animated shield icon showing IP masking.
*   **Real-time Analytics:** Interactive chart mockup.
*   **Custom Branding:** Preview of a branded link.
*   **Security:** Lock icon for password-protected links.

### 4. Interactive "Live" Stats (Mocked)
*   Counter showing "Links created today" and "Clicks tracked" (anonymously).

### 5. Pricing Section
*   **Individual Card:** "100% Free", "All features included", "No credit card needed".
*   **Enterprise Card:** "Custom Domains", "Team Collaboration", "API Access", "Contact business@ternis-edv.de".

### 6. FAQ Section
*   Clean accordion style focusing on DSGVO, safety, and pricing.

### 7. Footer
*   Links to Legal (Privacy, Terms), Socials, and "Built by ternis-edv.de & xpsystems.eu".

## 4. Technical implementation (Livewire 4 SFC)
*   Use `resources/views/pages/⚡landing.blade.php` (SFC).
*   State management for the "Shorten" form within the component.
*   Use `wire:navigate` for seamless transitions between landing and dashboard.
*   Alpine.js for non-backend interactions (dropdowns, mobile menu, local toggles).

## 5. Mobile Optimization
*   Sticky "Shorten" CTA for mobile users.
*   Simplified customization drawer for small screens.
*   Thumb-friendly touch targets.
