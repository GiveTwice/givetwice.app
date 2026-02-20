## Codebase Patterns
- Mobile menu in app layout uses Alpine.js `x-data` on the `<nav>` element with `@click.outside` for close behavior
- Icon components are at `resources/views/components/icons/*.blade.php` and accept class props via `$attributes->merge()`
- Alpine.js transitions follow the pattern: `x-transition:enter`, `x-transition:enter-start`, `x-transition:enter-end`, `x-transition:leave`, `x-transition:leave-start`, `x-transition:leave-end`
- Use `x-cloak` to prevent flash of content on Alpine.js toggled elements
- Both app and guest layouts now share the same mobile menu pattern (Alpine.js `x-data` on `<nav>`)
- Profile dropdown and language switcher both use Alpine.js `x-data="{ open: false }"` with `@click.outside`
- The impersonation banner is `sticky top-0 z-[60]` — any sticky header should account for this
- Z-index layering: `z-40` (sticky header) < `z-50` (modals, dropdowns, toasts) < `z-[60]` (impersonation banner)
- `sticky` doesn't remove elements from flow — no offset/padding needed for content below sticky elements
- Use `md:static md:z-auto` to reset sticky behavior on desktop
- For responsive buttons: use `hidden sm:inline` on text labels + always-visible icon for icon-only mobile buttons
- Add `title` attribute to icon-only buttons for accessibility/tooltip
- Use `flex-wrap` on button rows as a safety net — buttons will wrap gracefully rather than overflow
- For form action buttons: use `flex-col-reverse sm:flex-row sm:justify-end` to stack on mobile (CTA first) and inline right-aligned on desktop
- Add `justify-center` to `btn-primary` and `text-center` to `btn-cancel` when they need to be full-width on mobile
- `public/build/` is gitignored — don't try to commit build artifacts
- Must run `yarn build` after CSS class changes for Tailwind v4 to generate the new utility classes
- For modal close buttons: use `-m-1 p-2` (negative margin + padding) to enlarge touch target without shifting visual position — gives 40px+ tappable area with a 24px icon
- Use `max-h-[100dvh]` instead of `max-h-screen` or `max-h-[90vh]` on modal containers — dvh accounts for mobile browser chrome (URL bar, toolbar)
- For input+button rows that overflow on mobile: stack with `space-y-2` (input full-width above, buttons below) instead of `flex gap-2` inline

---

## 2026-02-20 - US-001
- Replaced vanilla JS `classList.toggle('hidden')` mobile menu with Alpine.js `x-data="{ mobileOpen: false }"` on the `<nav>` element
- Added slide-down animation (`opacity + translate-y`) via Alpine.js `x-transition` directives
- Hamburger icon now animates to X using CSS `transition-all duration-200` with `opacity`, `rotate`, and `scale` toggling
- Added `@click.outside="mobileOpen = false"` on `<nav>` to close menu on outside tap
- Added `:aria-expanded="mobileOpen.toString()"` for accessibility
- Files changed: `resources/views/layouts/app.blade.php`
- **Learnings for future iterations:**
  - The SVG icon components merge attributes, so `x-bind:class` works on them directly
  - Using two absolute-positioned icons (menu + close) inside a relative button with CSS transitions is cleaner than Alpine.js `x-show` for icon swap — avoids FOUC
  - The mobile menu's user info section already has its own nested `x-data` for profile image reactivity — Alpine.js scoping handles this correctly
---

## 2026-02-20 - US-002
- Added hamburger mobile menu to the guest layout (`guest.blade.php`), matching the app layout pattern from US-001
- Desktop nav now hidden on mobile (`hidden md:flex`), with hamburger button visible via `md:hidden`
- Mobile menu contains: How it works, About, Login, Sign up (coral CTA), language switcher
- Added How it works and About links to the desktop nav (were previously missing from guest layout)
- Files changed: `resources/views/layouts/guest.blade.php`
- **Learnings for future iterations:**
  - The guest layout previously had no responsive breakpoints on nav at all — items would overflow on mobile
  - Guest layout uses `route('faq')` and `route('about')` named routes for How it works/About links (same as app layout)
  - The guest mobile menu is simpler than app layout — no auth conditional block, just guest links + language switcher
---

## 2026-02-20 - US-003
- Added `sticky top-0 z-40 md:static md:z-auto` to `<header>` in both app and guest layouts
- On mobile (below `md`): header sticks to top of viewport as user scrolls
- On desktop (`md`+): header remains static (unchanged behavior)
- Z-index `z-40` places header above page content but below modals (`z-50`) and impersonation banner (`z-[60]`)
- Files changed: `resources/views/layouts/app.blade.php`, `resources/views/layouts/guest.blade.php`
- **Learnings for future iterations:**
  - `sticky` is preferred over `fixed` for headers because it doesn't remove elements from document flow — no padding/offset hack needed
  - Both the impersonation banner and header are `sticky top-0` — during impersonation on mobile, the header sits behind the banner (lower z-index), but the hamburger remains tappable since the banner is thin (~36px) and the header is 64px tall
  - The `md:static md:z-auto` pattern cleanly resets sticky behavior at a breakpoint without affecting other styles
---

## 2026-02-20 - US-004
- Made dashboard action buttons (Edit, Collaborators, Share, Add Gift) mobile-friendly
- Edit button: added `<x-icons.edit>` icon with `hidden sm:inline` text label — icon-only on mobile, full label at `sm:`+
- Share button: added `hidden sm:inline` to text label — icon-only on mobile, full label at `sm:`+
- Collaborators button: already icon-only by design — no change needed
- "Add a Gift" button: kept full text label at all sizes as it's the primary CTA
- Added `flex-wrap` to button container as safety net for edge cases
- Added `title` attributes to Edit and Share buttons for accessibility when labels are hidden
- Files changed: `resources/views/dashboard.blade.php`, `resources/views/components/share-modal.blade.php`
- **Learnings for future iterations:**
  - At 320px viewport with `px-4` padding, usable content width is only ~288px — four labeled buttons (~376px total) absolutely cannot fit
  - Icon-only buttons at mobile + labeled at `sm:` is the cleanest pattern — uses `hidden sm:inline` on the `<span>` wrapping the text
  - The `btn-secondary` class already has `inline-flex items-center gap-2` so adding an icon alongside text works seamlessly
  - The share-modal component's button is shared across all pages — changing it here affects everywhere the share button appears (dashboard, list detail)
---

## 2026-02-20 - US-005
- Verified gift create/edit forms are already mobile-friendly for most criteria (grid-cols-1, sidebar stacking, full-width inputs, touch-friendly image upload)
- Fixed form action buttons: changed from `flex justify-end` to `flex flex-col-reverse sm:flex-row sm:justify-end` — buttons stack vertically on mobile (primary CTA on top via `flex-col-reverse`) and remain inline right-aligned on desktop
- Added `justify-center` to primary buttons and `text-center` to cancel links for proper centering when full-width
- Verified no horizontal overflow at 320px on both create and edit forms
- Files changed: `resources/views/gifts/create.blade.php`, `resources/views/gifts/edit.blade.php`
- **Learnings for future iterations:**
  - The `form-input`, `form-textarea`, `form-select` CSS classes already include `w-full` — inputs are inherently full-width
  - The `grid grid-cols-1 lg:grid-cols-5` pattern already handles sidebar stacking on mobile — no changes needed
  - The image upload area on edit form (`w-full h-48`) is already a generous 256x192px touch target at 320px viewport — well above 44x44px minimum
  - `flex-col-reverse` is the key trick for mobile button stacking: DOM order stays Cancel→Primary, but visual order on mobile becomes Primary→Cancel (CTA on top)
  - The `danger-zone` component already handles responsive layout with `flex-col sm:flex-row` — no changes needed there
---

## 2026-02-20 - US-006
- Share modal: refactored Public Link section from inline `flex gap-2` (input + View + Copy) to stacked layout — input full-width on its own row, View and Copy buttons side-by-side below via `space-y-2`
- Share modal: refactored Ready-to-send message section similarly — input full-width, Copy button full-width below
- Share modal: added `max-h-[100dvh] overflow-y-auto` to modal panel for scrollability on small screens
- Share modal: enlarged close button touch target with `-m-1 p-2` + `rounded-full hover:bg-gray-100` + `aria-label`
- Gift detail modal: changed `max-h-[90vh]` → `max-h-[100dvh]` on outer container
- Gift detail modal: changed `max-h-[50vh]` → `max-h-[50dvh]` and `max-h-[90vh]` → `max-h-[100dvh]` on details pane
- Gift detail modal: enlarged close button from `p-2` (40px) to `p-2.5` (44px) for minimum touch target compliance
- Confirm modal: verified — already compact with good button sizing, no changes needed
- Files changed: `resources/views/components/share-modal.blade.php`, `resources/views/components/gift-modal.blade.php`
- **Learnings for future iterations:**
  - The three modal components are: `share-modal.blade.php`, `gift-modal.blade.php`, `confirm-modal.blade.php`
  - Share modal's original layout had input + 2 buttons in a single flex row — ~400px wide at minimum, which overflows at 320px viewport (288px usable)
  - `dvh` (dynamic viewport height) is critical for mobile — `vh` doesn't account for iOS Safari's URL bar or Android Chrome's toolbar, causing content to be hidden behind browser chrome
  - `p-2.5` on a button with `w-6 h-6` icon gives exactly 44px total touch target (24px icon + 10px padding each side)
  - The confirm modal is small enough (max-w-md) that it doesn't need scroll handling or dvh adjustments
---
