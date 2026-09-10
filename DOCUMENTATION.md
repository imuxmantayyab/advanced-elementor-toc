# Advanced Table of Contents for Elementor — User & Developer Guide

A comprehensive guide for installing, configuring, designing, and extending the **Advanced Table of Contents for Elementor** WordPress plugin.

---

## Table of Contents

1. [System Requirements](#1-system-requirements)
2. [Installation & Setup](#2-installation--setup)
3. [Quick Start Guide](#3-quick-start-guide)
4. [Elementor Widget Controls](#4-elementor-widget-controls)
   - [Content Tab](#content-tab)
   - [Style Tab](#style-tab)
   - [Advanced Tab](#advanced-tab)
5. [Design Presets & Visual Customization](#5-design-presets--visual-customization)
6. [Mobile Experience & Drawer Modes](#6-mobile-experience--drawer-modes)
7. [Reading Progress & Heading Search](#7-reading-progress--heading-search)
8. [Performance, Caching & Admin Settings](#8-performance-caching--admin-settings)
9. [Developer API & Custom Scripting](#9-developer-api--custom-scripting)
10. [SEO & Accessibility Compliance](#10-seo--accessibility-compliance)
11. [Troubleshooting & FAQ](#11-troubleshooting--faq)
12. [Author & Repository Links](#12-author--repository-links)

---

## 1. System Requirements

* **WordPress:** Version 5.8 or higher
* **PHP:** Version 7.4 or higher (8.0, 8.1, 8.2, 8.3 fully supported)
* **Elementor:** Elementor Free 3.5.0+ or Elementor Pro 3.5.0+
* **Theme Compatibility:** Compatible with all standard WordPress themes (Hello Elementor, Astra, GeneratePress, OceanWP, Kadence, Divi, etc.)

---

## 2. Installation & Setup

### Method A: Upload via WordPress Admin
1. Download or locate `advanced-elementor-toc.zip`.
2. Log into your WordPress Dashboard (`/wp-admin`).
3. Navigate to **Plugins → Add New → Upload Plugin**.
4. Click **Choose File**, select `advanced-elementor-toc.zip`, and click **Install Now**.
5. Once the installation completes, click **Activate Plugin**.

### Method B: Manual FTP / SFTP Installation
1. Extract the `advanced-elementor-toc.zip` file on your local machine.
2. Upload the uncompressed `advanced-elementor-toc` folder to your server directory at `/wp-content/plugins/`.
3. In WordPress Admin, navigate to **Plugins → Installed Plugins** and click **Activate** under **Advanced Table of Contents for Elementor**.

---

## 3. Quick Start Guide

1. Open any Post, Page, or Elementor Template in the **Elementor Editor**.
2. In the left widget panel, scroll down to the **Advanced Elements** category or search for **"Advanced Table of Contents"**.
3. Drag and drop the widget into your desired column or sidebar.
4. The widget will automatically scan the content of your page and generate the live Table of Contents.
5. Publish or Update the page to see the live navigation with smooth scrolling and scroll spy active tracking.

---

## 4. Elementor Widget Controls

### Content Tab

#### 1. Headings Detection
* **Include Headings:** Multi-select control to choose which heading tags (`H1`, `H2`, `H3`, `H4`, `H5`, `H6`) appear in the TOC. (Default: `H2, H3, H4`).
* **Heading Source:** Choose where headings should be extracted from:
  - *Entire Page (Body):* Scans the whole document.
  - *Post / Entry Content:* Scans `.entry-content, .post-content, article`.
  - *Elementor Content:* Scans `.elementor` sections.
  - *Custom CSS Selector:* Targets custom classes/IDs (e.g., `.article-body, #main-content`).
* **Custom CSS Selector:** Enabled when *Custom CSS Selector* is selected. Supports Elementor Dynamic Tags.

#### 2. Heading Exclusion & Filtering
* **Exclude by Heading Text:** Enter exact phrases or words (one per line or comma-separated) to exclude specific headings (e.g., `Introduction`, `Conclusion`, `Leave a Reply`).
* **Exclude by CSS Class:** Exclude headings containing specific classes (e.g., `.no-toc, .ignore-heading`).
* **Exclude by CSS Selector:** Exclude headings matching complex selectors (e.g., `.sidebar h2, .footer h3`).
* **Exclude First N Headings:** Skips the first $N$ detected headings at the top of the content.
* **Exclude Last N Headings:** Skips the last $N$ detected headings at the bottom of the content.
* **Force Regenerate Heading IDs:** Overwrites existing HTML tag `id="..."` attributes with clean slugs.

#### 3. TOC Structure & Numbering
* **Layout Structure:**
  - *Nested Tree:* Preserves parent-child hierarchy with indented sub-levels.
  - *Flat List:* Displays all headings in a single level list.
* **Numbering Style:**
  - *None:* Clean text without numbers.
  - *Numeric:* `1.`, `2.`, `3.`
  - *Hierarchical:* `1.`, `1.1`, `1.2`, `1.2.1`
  - *Alphabetical:* `A.`, `B.`, `C.`
  - *Roman Numerals:* `I.`, `II.`, `III.`
  - *Custom Prefix:* Adds prefixes like `Section 1`, `Chapter 2`.
* **Maximum Heading Depth:** Restrict deep sub-levels (e.g., `Up to H3`, `Up to H4`).
* **Max Visible Items & View More:** Limits visible items (e.g., display first 10 items) and renders an expandable *"View More / View Less"* button for long documents.

#### 4. TOC Title & Header
* **Show Title:** Toggle the header box.
* **Title Text:** Custom header text with Dynamic Tag support (default: *"Table of Contents"*).
* **Title HTML Tag:** Semantic tag choice (`H2`, `H3`, `H4`, `div`, `p`, `span`).
* **Title Icon:** Select any icon from the Elementor Icons Library (FontAwesome / SVG) with position control (*Before* or *After*).
* **Item Count Badge:** Displays the count of detected sections (e.g., `12 Sections` / `1 Section`).

#### 5. Collapsible & Sub-Levels
* **Collapsible TOC Box:** Adds a toggle button to collapse/expand the entire TOC card.
* **Initially Collapsed:** Start page in collapsed state.
* **Collapsible Sub-Headings:** Allows users to expand/collapse individual nested sub-branches.
* **Toggle Icons:** Custom expand (`+` / chevron down) and collapse (`−` / chevron up) icons.
* **Animation Duration:** Smooth slide animation speed (100ms – 1000ms).

#### 6. Sticky, Floating & Sidebar Layouts
* **Sticky Behavior:** Set the TOC to remain fixed as the user scrolls (*Sticky Top* or *Sticky Bottom*).
* **Sticky Offset:** Responsive offset in pixels (`Desktop`, `Tablet`, `Mobile`) to clear fixed headers.
* **Floating Navigation Card:** Detaches the TOC into a fixed floating quick-nav widget pinned to screen corners (*Top Left/Right*, *Middle Left/Right*, *Bottom Left/Right*).
* **Floating Minimize Button:** Allows users to minimize the floating card to save screen space.

#### 7. Back to Top Button
* **Show Back to Top Link:** Inserts an anchor at the bottom of the TOC to return smoothly to the top of the page.

---

### Style Tab

* **Design Presets:** Quick selection of 8 handcrafted themes (Classic, Minimal, Modern, Sidebar, Floating, Compact, Documentation, Glassmorphism).
* **Container Box:** Full control over width, max width, height, background colors / gradients, borders, border radii, box shadows, padding, margins, and **multi-columns (1 to 4 columns)**.
* **Title & Header:** Typography, font size, weight, line-height, text color, icon spacing, separator lines, and toggle button colors.
* **Items & Links:** Typography, normal/hover colors, padding, item vertical gap, and individual indentations for `H2`, `H3`, `H4`, `H5`, and `H6`.
* **Active Item Highlight:** Complete customization of the currently active heading:
  - *Indicator Styles:* Left border bar, right border bar, dot indicator, background pill highlight, or underline accent.
  - *Colors & Sizing:* Custom indicator colors, background highlight colors, and bar thickness.
* **Search Box & Reading Progress:** Style search input backgrounds, text colors, progress bar track/fill colors, and circular SVG dimensions.

---

### Advanced Tab

* **Scroll Tracking Engine:** Choose between high-performance `IntersectionObserver` or traditional scroll event listeners.
* **Smooth Scroll Animation:** Enable/disable hardware-accelerated animated scrolling.
* **Scroll Offset (Clearance px):** Configurable offset (Desktop, Tablet, Mobile) to ensure fixed theme headers and the WordPress admin bar do not overlap target headings.
* **URL Hash Behavior:**
  - *Update URL Hash on Click:* Updates browser address bar (`/page/#my-heading`).
  - *Browser History Mode:* `Replace State` (prevents spamming browser back button history) or `Push State`.
  - *Scroll to Hash on Page Load:* Automatically navigates to the hash anchor if present in the URL when a visitor lands on the page.
* **Observer Root Margin:** Fine-tune the viewport detection trigger boundary (default: `-100px 0px -65% 0px`).
* **Mobile Experience & Drawer:** Choose mobile behavior (*Standard Inline*, *Accordion*, *Dropdown*, *Slide-up Bottom Drawer*, or *Floating Action Bar*), or toggle *Hide on Mobile*.

---

## 5. Design Presets & Visual Customization

The widget includes 8 built-in design presets:

| Preset Name | Description | Recommended Use Case |
| :--- | :--- | :--- |
| **Modern** | Rounded card with soft shadows, subtle borders, and vivid active indicator bars. | Blog posts, marketing pages, SaaS articles. |
| **Minimal** | Borderless, transparent background with clean typography. | Minimalist blogs, news editorial articles. |
| **Classic** | Solid bordered box with a structured header and distinct list styling. | Academic articles, technical whitepapers. |
| **Sidebar** | Accent vertical line along the side without bounding card boxes. | Left/right page sidebars, documentation hubs. |
| **Documentation** | GitBook / developer documentation layout with light slate backgrounds. | Knowledge bases, API docs, software handbooks. |
| **Glass** | Frosted glassmorphism with dynamic backdrop blur and translucent borders. | Modern dark/light hero sections, creative portfolios. |
| **Compact** | Streamlined low-profile card with tight padding and small typography. | Long recipes, quick reference guides. |
| **Floating** | Detached floating widget pinned to viewport corner with minimize toggle. | Long-form reading experiences, tutorial pages. |

---

## 6. Mobile Experience & Drawer Modes

To ensure optimal usability on mobile devices, the widget supports multiple mobile display options:

1. **Slide-up Bottom Drawer (`bottom_drawer`):**
   Pins the TOC to the bottom of the mobile viewport as a clean bottom sheet. Users can tap to expand or scroll through headings without losing their reading place.
2. **Floating Pill (`floating_bar`):**
   Transforms the TOC into a compact floating pill at the bottom of mobile screens.
3. **Collapsible Accordion (`accordion`):**
   Keeps the TOC in-line with the content but collapses it into a single touch-friendly bar to save vertical screen space.
4. **Hide on Mobile (`hide_on_mobile`):**
   Completely removes the widget on screens under `768px` if a table of contents is not desired for mobile readers.

---

## 7. Reading Progress & Heading Search

### Reading Progress Indicator
* **Horizontal Bar:** A sleek progress bar placed above or below the TOC title that tracks user scroll depth from `0%` to `100%`.
* **Circular Ring:** An SVG circular progress ring showing numeric percentage (`%`) inside the header.
* **Percentage Text:** A numeric readout (e.g., `45%`) that updates dynamically.

### Live In-TOC Search Filter
* Enables a search box at the top of the TOC card.
* Filters matching headings in real-time as the user types without requiring AJAX requests or server round-trips.
* Automatically expands parent branches when a matching child heading is found.

---

## 8. Performance, Caching & Admin Settings

Access the plugin settings at **WordPress Admin → Settings → Advanced TOC**.

### Settings Tabs:
1. **General:**
   - *Enable Plugin:* Master switch to enable or disable the widget across the site.
   - *Asset Loading:* Set to **Conditional** (default) to load CSS/JS only on pages where the widget is present, maximizing Google PageSpeed scores.
   - *Debug Mode:* Enables console diagnostic logging for scroll spy and heading parsing.
2. **Performance & Cache:**
   - *Enable Server Cache:* Caches parsed TOC HTML structures in WordPress transients.
   - *Cache Duration:* Choose cache lifetime (1 hour, 6 hours, 24 hours, or 7 days).
   - *Flush All TOC Caches:* Manual one-click button to purge all cached transients.
   - *Automatic Invalidation:* Automatically purges cache when posts are saved, edited, or when Elementor templates are updated.
3. **Defaults:**
   - Set global default heading levels (`H2`, `H3`, `H4`), numbering styles, and smooth scroll settings for newly created widgets.
4. **System & Compatibility:**
   - Displays real-time environment status: Elementor version, Elementor Pro status, PHP version, and WooCommerce compatibility.

---

## 9. Developer API & Custom Scripting

### Global JavaScript Controller
The plugin exposes a global API on `window.AdvancedElementorTOC`:

```javascript
// Initialize a TOC widget manually
AdvancedElementorTOC.init(document.querySelector('.aetoc-wrapper'));

// Refresh all TOC instances on the page (useful after AJAX page loads)
AdvancedElementorTOC.refresh();

// Destroy and teardown observers
AdvancedElementorTOC.destroy();
```

### CSS Custom Properties
Easily override colors and styling globally via CSS variables:

```css
:root {
  --aetoc-primary: #0066cc;          /* Main accent & active color */
  --aetoc-primary-light: #e6f0fa;    /* Background highlight tint */
  --aetoc-text-main: #1e293b;        /* Title and heading text color */
  --aetoc-text-muted: #64748b;       /* Inactive link text color */
  --aetoc-bg-card: #ffffff;          /* Container background */
  --aetoc-border-color: #e2e8f0;     /* Border lines */
  --aetoc-radius: 12px;              /* Border radius */
}
```

---

## 10. SEO & Accessibility Compliance

* **Semantic HTML:** Renders `<nav aria-label="Table of Contents">` with accessible list items (`role="list"`).
* **Keyboard Navigation:** Full Tab and Enter key support across all links, toggles, and search inputs.
* **Heading Hierarchy Integrity:** Does not inject fake heading tags that could confuse search engine web crawlers.
* **Duplicate Anchor Resolution:** Converts duplicate headings (e.g., multiple "Summary" sections) into unique anchors (`#summary`, `#summary-2`).
* **Motion Preferences:** Complies with `prefers-reduced-motion` media queries for visitors with vestibular motion sensitivities.
* **RTL Support:** Built entirely with CSS logical properties (`margin-inline-start`, `border-inline-start`, etc.) for seamless Arabic, Hebrew, and Urdu layouts.

---

## 11. Troubleshooting & FAQ

### Q: Why does the widget say "No headings found"?
**A:** Ensure your article contains headings matching the levels selected under **Content → Include Headings** (e.g., H2, H3). If your content is in a custom template, ensure the **Heading Source** is set to *Entire Page* or configure the *Custom CSS Selector*.

### Q: The heading is partially covered by my fixed sticky menu when clicked. How do I fix this?
**A:** Navigate to **Advanced → Scroll Offset (Header Clearance px)** in the widget settings and increase the offset (e.g., from `90px` to `120px`) to match the height of your sticky theme header.

### Q: Does this plugin slow down page speed scores?
**A:** No. The plugin uses **0% jQuery**, utilizes native browser `IntersectionObserver` APIs, loads assets conditionally only on pages containing the widget, and weighs under 35KB.

### Q: Can I use this in an Elementor Single Post Theme Builder template?
**A:** Yes! When placed inside a Single Post Template, the widget dynamically detects headings from the post content of whichever post is being rendered.

---

## 12. Author & Repository Links

* **GitHub Repository:** [https://github.com/imuxmantayyab/advanced-elementor-toc](https://github.com/imuxmantayyab/advanced-elementor-toc)
* **Author GitHub:** [https://github.com/imuxmantayyab](https://github.com/imuxmantayyab)
* **Author LinkedIn:** [https://www.linkedin.com/in/imuxmantayyab/](https://www.linkedin.com/in/imuxmantayyab/)
