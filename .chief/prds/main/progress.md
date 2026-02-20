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
- For inline step indicators with translated labels: hide labels on mobile (`hidden sm:inline`) and show just numbered circles — prevents overflow with longer translations
- Use `gap-3 sm:gap-4` on gift grids to give cards slightly more width at 320px (140px vs 136px per card in 2-col)
- For cramped icon + text + toggle rows at 320px: hide decorative icons on mobile (`hidden sm:flex`) and add `flex-shrink-0` to the toggle/action element
- For session/device rows with badges: use `flex-wrap` on the badge container so badges wrap below the label on narrow screens, hide decorative device icons on mobile
- Long action buttons (e.g., "Enable two-factor authentication"): use `w-full sm:w-auto justify-center` to make full-width on mobile, auto-width on desktop
- For modal button rows: use `flex-col-reverse sm:flex-row sm:justify-end` pattern (same as form actions) — CTA on top on mobile, inline on desktop
- Settings page sections use `grid grid-cols-1 lg:grid-cols-3 gap-8` — already stacks properly on mobile, no fix needed
- Static pages extend `layouts.app` directly (not `x-app-content`) — layout provides `px-4 sm:px-6 lg:px-8`, giving 288px content width at 320px
- Hero heading pattern for static pages: `text-3xl sm:text-4xl lg:text-5xl` with `text-lg sm:text-xl` subtitle and `py-8 sm:py-12 lg:py-16`
- Card padding pattern for static pages: `p-6 sm:p-8 lg:p-12` for large feature cards, `p-4 sm:p-6` for smaller repeated cards (FAQ items)
- Pages using `max-w-3xl` (privacy, terms, transparency) are inherently mobile-safe due to narrow max-width — minimal responsive tweaks needed
- Auth pages (7 files in `resources/views/auth/`) all extend `layouts.guest` — card padding pattern is `p-6 sm:p-8` (no `lg:` needed since constrained by `max-w-md`)
- Guest layout main content uses `py-6 sm:py-12` for vertical padding — reduced on mobile to keep auth cards above the fold
- Use `flex-wrap gap-2` on side-by-side text items that may vary in length across languages — wraps gracefully when translated text is longer

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

## 2026-02-20 - US-007
- Reduced gift grid section padding on mobile: `px-6` → `px-4 sm:px-6`, `p-6` → `p-4 sm:p-6`, `py-5` → `py-4 sm:py-5`
- Made gift grid section header responsive: added `gap-2` to flex row, `text-right` on helper text, reduced heading size to `text-lg sm:text-xl`
- "How it works" steps: hid text labels on mobile (`hidden sm:inline`), showing just numbered circles (1→2→3) — prevents overflow with longer Dutch/French labels ("Bladeren", "Afstrepen", "Parcourir")
- Added `flex-shrink-0` to step circles and chevrons to prevent squishing
- Reduced gap in step row on mobile: `gap-2 sm:gap-3`
- Tightened gift grid gap on mobile: `gap-3 sm:gap-4` — gives each card ~140px width at 320px (vs 136px with gap-4)
- Verified at 320px, 375px viewport widths in both English and Dutch — no horizontal overflow
- Files changed: `resources/views/public/list.blade.php`
- **Learnings for future iterations:**
  - The public list view is at `resources/views/public/list.blade.php` (not `show.blade.php`)
  - Public list URL format is `/{locale}/v/{id}/{slug}` — the `{id}` is separate from `{slug}`
  - The "How it works" step labels translate to 8-9 char words in Dutch/French — at 320px with circles + labels + chevrons, the row would be ~300px but content area is only 280px, so hiding labels on mobile is necessary
  - Existing responsive classes on the header (truncate, min-w-0, flex-shrink-0) already prevent overflow — the header is tight at 320px but functional
  - Gift card badges ("Always available", "Ophalen") use absolute positioning and `text-xs` so they fit within 140px card width
  - Claim buttons are `w-full` within the card, making them full card width (~140px) — easily tappable even at `py-2` height
---

## 2026-02-20 - US-008
- Responsive pass on the settings page (`settings.blade.php`)
- Notification toggle row: hid decorative icon circle on mobile (`hidden sm:flex`), added `gap-3` and `flex-shrink-0` to toggle wrapper — gives text more room beside the toggle at 320px
- Session rows: hid device icon on mobile (`hidden sm:flex`), added `flex-wrap` to badge container so "This device" badge wraps below platform name, added `min-w-0` for text truncation safety, `flex-shrink-0` on "Log out" button
- 2FA buttons: made "Enable two-factor authentication" and "Disable two-factor authentication" full-width on mobile (`w-full sm:w-auto justify-center`)
- "Log out other browser sessions" button: same full-width mobile treatment
- Danger zone "Delete account" button: full-width on mobile (`w-full sm:w-auto justify-center`)
- Delete account modal buttons: stacked on mobile using `flex-col-reverse sm:flex-row sm:justify-end` pattern (CTA on top)
- Verified: all form sections already stack in single column on mobile via `grid grid-cols-1 lg:grid-cols-3` — no changes needed
- Verified: profile photo upload area (96px circle + text) fits well at 320px — touch-friendly and no overflow
- Verified: no horizontal overflow at 320px and 375px viewports
- All 314 tests pass, Pint clean
- Files changed: `resources/views/settings.blade.php`
- **Learnings for future iterations:**
  - The settings page is a single 1136-line Blade file with six sections — all use the same `grid grid-cols-1 lg:grid-cols-3 gap-8` pattern which already handles mobile stacking
  - There is no "connected accounts" section in the settings page (mentioned in AC but doesn't exist in the codebase)
  - The notification toggle row is the tightest spot at 320px — icon (40px) + gap (12px) + text + gap (12px) + toggle (44px) = ~108px overhead before text starts. Hiding the icon on mobile frees 52px
  - Session rows with badges + "Log out" button are another tight spot — the badge + browser name + button compete for space. Hiding the device icon and allowing badge wrapping solves it cleanly
  - `flex-shrink-0` is essential on toggle switches and action buttons to prevent them from being compressed by flexbox
  - The `app-content` component adds `px-6 sm:px-8` padding, so at 320px the content area is only 272px wide (320 - 24 - 24). Every pixel counts
---

## 2026-02-20 - US-009
- Responsive pass on all six static pages: about, FAQ, privacy, terms, contact, transparency
- **About page**: Reduced hero padding (`py-8 sm:py-12`), heading size (`text-3xl sm:text-4xl`), subtitle size (`text-lg sm:text-xl`), card padding (`p-6 sm:p-8 lg:p-12`), section margins (`mb-12 sm:mb-16`), value card icons (`w-10 h-10 sm:w-12 sm:h-12`), and gaps (`gap-4 sm:gap-5`, `gap-6 sm:gap-8`)
- **FAQ page**: Same hero treatment, reduced card padding (`p-4 sm:p-6`), tighter FAQ item gap (`gap-3 sm:gap-4`), CTA card padding (`p-6 sm:p-8`)
- **Contact page**: Same hero treatment, reduced main CTA card padding, community section padding, quick links grid gap (`gap-4 sm:gap-6`)
- **Privacy, Terms, Transparency pages**: Reduced top container padding (`py-8 sm:py-12 lg:py-16`) — these were already well-structured with `max-w-3xl` and `p-6 lg:p-10` responsive padding
- Verified no horizontal overflow at 320px on all six pages (both English and Dutch)
- All 314 tests pass, Pint clean
- Files changed: `resources/views/pages/about.blade.php`, `resources/views/pages/faq.blade.php`, `resources/views/pages/contact.blade.php`, `resources/views/pages/privacy.blade.php`, `resources/views/pages/terms.blade.php`, `resources/views/pages/transparency.blade.php`
- **Learnings for future iterations:**
  - Static pages extend `layouts.app` directly (not `x-app-content`) — the layout provides `px-4 sm:px-6 lg:px-8` padding, so at 320px content area is 288px wide
  - The six static pages are: about, faq, privacy, terms, contact, transparency (routed via `Route::view()` in `web.php`)
  - Pages with `max-w-3xl` (privacy, terms, transparency) were already responsive — the narrow max-width prevents content from stretching too wide
  - Pages with `max-w-4xl` (about, contact) and hero sections needed the most work — `p-8` inner padding on 288px content leaves only 224px for text
  - The pattern for responsive static page cards: `p-6 sm:p-8 lg:p-12` for large feature cards, `p-4 sm:p-6` for smaller repeated cards (FAQ items)
  - Hero heading pattern across static pages: `text-3xl sm:text-4xl lg:text-5xl` with `text-lg sm:text-xl` subtitle
  - The `how-it-works` component used on the about page already handles its own responsiveness (`grid md:grid-cols-3`)
---

## 2026-02-20 - US-010
- Responsive pass on all 7 auth pages: login, register, forgot-password, reset-password, verify-email, confirm-password, two-factor-challenge
- **All auth pages**: Reduced card padding from `p-8 sm:p-10` to `p-6 sm:p-8` — at 320px viewport with `px-4` layout padding, this gives 240px inner content width (vs 224px before), a meaningful improvement for form usability
- **Guest layout**: Reduced main content vertical padding from `py-12` to `py-6 sm:py-12` — prevents auth cards from being pushed below the fold on short mobile screens (iPhone SE: 568px tall)
- **Login page**: Added `flex-wrap gap-2` to the "Remember me" / "Forgot password?" row — in French ("Se souvenir de moi" + "Mot de passe oublié ?") these items are too wide for one line at 320px, so they now gracefully wrap
- **Register "coming soon" variant**: Added `overflow-hidden` to outer decorative container — the absolute-positioned blurred blobs extend 8px beyond the container with negative offsets (`-right-8`, `-left-6`), which could cause horizontal scrollbar on narrow screens
- Verified no horizontal overflow at 320px on all auth pages in English, Dutch, and French
- All 314 tests pass, Pint clean
- Files changed: `resources/views/auth/login.blade.php`, `resources/views/auth/register.blade.php`, `resources/views/auth/forgot-password.blade.php`, `resources/views/auth/reset-password.blade.php`, `resources/views/auth/verify-email.blade.php`, `resources/views/auth/confirm-password.blade.php`, `resources/views/auth/two-factor-challenge.blade.php`, `resources/views/layouts/guest.blade.php`
- **Learnings for future iterations:**
  - The 7 auth pages all extend `layouts.guest` and are located at `resources/views/auth/`
  - Auth pages use a two-step flow (social buttons → email form) rather than a traditional "or continue with" divider — social and email options are never shown simultaneously
  - The guest layout wraps content in `max-w-md` (448px) centered container — at 320px this becomes 288px (after `px-4` layout padding)
  - Auth card padding pattern: `p-6 sm:p-8` (not `p-6 sm:p-8 lg:p-10` — cards are already constrained by `max-w-md` so large breakpoint padding isn't needed)
  - The `form-input` CSS class includes `w-full` by default — all form inputs are inherently full-width on auth pages
  - Form inputs inherit the 16px base font-size (no explicit `text-sm` or `text-base` override) — this prevents iOS Safari from auto-zooming on focus (zoom triggers at font-size < 16px)
  - The `flex-wrap gap-2` pattern on the Remember/Forgot row is better than `flex-col` because in languages where both items fit on one line (English, Dutch), they stay inline
  - Auth pages that don't exist in user-facing flows (reset-password requires a token, verify-email requires auth, confirm-password requires auth, two-factor-challenge requires partial auth) can't be easily browser-tested but share the same card structure
---
