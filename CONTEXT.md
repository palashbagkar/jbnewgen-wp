# jbnewgen-wp — Project Context

**The durable brief.** Memory folders don't travel; this file does. Any session, any
machine, any tool: read this first.

Created 2026-09-01 · Companion repo: `../jbnewgen` (the live Next.js site)

---

## 1. What this is

Rebuilding **jbnewgen.com** from **Next.js 16 + Payload CMS + Neon Postgres (Railway)**
into a **WordPress custom theme** — hand-written PHP templates, no page builder — on
**Hostinger shared hosting**.

This is a **rewrite, not a conversion.** Nothing transpiles React to PHP. Markup
structure, CSS and media carry over by hand. Every template and data fetch is new.

### Why

Joyjeet (the client) wants PHP. That is his #1 ask and the actual reason. Hostinger
runs PHP + MySQL only — no Node process — so the current stack cannot live there.

**Do not sell this on cost savings.** It is ~$60/yr. That argument was tried and is weak.

### History — settled, do not re-open

- **2026-08-25** — approved, started, **cancelled the same day.** Palash: *"I recycled
  all the files remove all the progress and commits."* `main` force-reset to `08af1a4`.
- **2026-09-01** — **restarted.** Palash: *"I've changed my mind, this is the only way
  for him and things work out between us."*

The decision is made. Do not relitigate it.

---

## 2. Repo layout — deliberate

```
C:\Users\palas\projects\
├── jbnewgen\      <- live Next.js site. FROZEN. rollback target.
└── jbnewgen-wp\   <- this repo. the WordPress build.
```

**Siblings, not nested.** The Next.js source at `../jbnewgen/src/**` must stay readable
while transcribing components into PHP.

**Separate repos on purpose.** The live site deploys from `jbnewgen`'s `main`. Zero code
is shared between the stacks, so a branch would buy nothing — and one-repo-for-both is
exactly how the August work got wiped by a force-push.

### Backups (all pushed to GitHub 2026-09-01, previously laptop-only)

| Tag | Holds |
|---|---|
| `nextjs-final` | the exact working Next.js app |
| `php-era-backup` (`b5dd557`) | **48-page offline mirror, 14/14 fonts** + R2 media — the visual reference for this rebuild |
| `pre-cleanup-backup` | pre-cleanup state |

Serve the mirror with `python -m http.server 8080` from `backups/site-2026-08-25/`.
It needs nothing from the internet. **Never trust a mirror without auditing it** — the
first attempt silently saved only 4 of 14 fonts because CSS uses relative `url()` refs.

---

## 3. The numbers (measured 2026-09-01, not estimated)

| Thing | Actual | Destination |
|---|---|---|
| Cloudflare R2 | **9 files, 1.6 MB** — logos + 2 screenshots | `wp-content/uploads/` |
| `public/images` | 2.0 MB | `wp-content/uploads/` |
| `public/video` | 3.4 MB | `wp-content/uploads/` |
| `public/india-media-update/` | 87 MB — **gitignored**, source only | stays local, never ships |
| Neon Postgres | 41 tables, **309 rows**, 864 KB JSON | MySQL on Hostinger |
| Live copy hardcoded in code | **~1,671 lines** | must become CMS-editable |

**Live media totals ~7 MB.** R2 is nearly empty — dropping it costs nothing and
**frees the S3 plugin slot.**

---

## 4. Stack decisions

### No ACF

Joyjeet refused $49/yr for ACF Pro (2026-08-25). Free replacements, no feature loss:

| Need | Tool |
|---|---|
| Lists of similar things | **Custom Post Types** — WordPress core, free, own admin menu + drag-reorder |
| Nested / repeater content | **Carbon Fields** — GPL, **bundled inside the theme**, not installed. Costs no plugin slot, cannot be deactivated by accident. |

CMB2 is the fallback. **Net new spend: $0.**

### Plugin cap: 3 — hard limit

1. **Security** (also renames `/wp-admin` to `/admin`, provides 2FA)
2. **Backup to Google Drive** (free substitute for paid host backups)
3. *Reserved for cache* — Hostinger's box runs LiteSpeed, which may already cover it. **Hold this slot back.**

Plugin creep is the main way this becomes unmaintainable. And every plugin is another
developer's code inside the site, which cuts directly against the attribution goal (§8).

### Cost

Kills Railway (~$5/mo), Neon, R2. Hostinger already paid for. **New spend: $0.**

### Maintenance — stated honestly, accepted

~**1–2 hrs/month**, plus ~half a day/yr for major PHP/WP version bumps. WordPress is
the highest-maintenance option considered and runs ~43% of the web, making it the most
attacked platform. Palash chose it with that on the table. The hand-written theme is
the stable part — no build step, no npm, no dependency rot.

### CMS UX — a promise that was made, hold to it

- Self-hosted **wordpress.org**, on his own domain. Explicitly **NOT wordpress.com.**
- Login at `jbnewgen.com/wp-admin`, **renamed to `/admin`** to match today.
- Told him honestly: **it will not look pixel-identical to Payload**, but will match in
  workflow. Do not over-promise beyond that.
- **Phase 4 ends with him clicking around the local admin before any styling.** A wrong
  editing experience is cheap to fix there and expensive after templates exist.

---

## 5. Content model — Payload to WordPress

Built **once, in WordPress.** Do not widen Payload first; that models everything twice
and throws one away.

### Collections to Custom Post Types

| Payload (`src/collections/`) | WordPress |
|---|---|
| `ServicePillars.ts` | CPT `pillar` (4 pillars) |
| `Services.ts` | CPT `service`, child of pillar |
| `Insights.ts` | CPT `insight` |
| `Categories.ts` | taxonomy `insight_category` |
| `TeamMembers.ts` | CPT `team_member` |
| `Jobs.ts` | CPT `job` |
| `Media.ts` | native WP media library |
| `Users.ts` | native WP users |
| `iconOptions.ts` (`ICON_OPTIONS`) | replicate the icon set in the theme |
| `seoFields.ts` | hand-rolled meta fields (no SEO plugin — no slot for one) |

### Globals to Carbon Fields options pages

| Payload (`src/globals/`) | WordPress |
|---|---|
| `Homepage.ts` | options page |
| `About.ts` | options page (company + CEO) |
| `SiteSettings.ts` | options page |

### Hardcoded copy that MUST become editable

Not in the CMS today — lives in code and is invisible to the client:

- `src/lib/content.ts` — **1,378 lines** (nav, brand strings, markets, testimonials, misc)
- `src/lib/insightsBodies.ts` — **209 lines** (article bodies)
- `src/lib/about-copy.ts` — **54 lines** (`companyCopy`, `ceoCopy`)

---

## 6. Routes to PHP templates

| Next.js route | WordPress template |
|---|---|
| `/` | `front-page.php` |
| `/about` | `page-about.php` |
| `/about/company` · `/about/ceo` · `/about/team` | page templates |
| `/services` | `page-services.php` |
| `/services/[pillar]` | `single-pillar.php` |
| `/services/[pillar]/[service]` | `single-service.php` |
| `/insights` | `archive-insight.php` |
| `/insights/[slug]` | `single-insight.php` |
| `/insights/category/[cat]` | `taxonomy-insight_category.php` |
| `/careers` | `page-careers.php` |
| `/contact` | `page-contact.php` |
| `/quote` | `page-quote.php` |
| `/search` | `search.php` |
| `/admin-verify`, `/twofa/*` | **drop** — the security plugin handles 2FA |

### Components to `template-parts/` (42 total, inventoried via graphify)

- **ui/** — `Button` `Icon` `Reveal` `Section` `SplitHeading`
- **site/** — `Header` `Footer` `Logo` `DevTag`
- **nav/** — `Breadcrumbs` `NavGrid` `NextStep` `PageHeader`
- **home/** — `Hero` `Problem` `ProofBar` `CountUpStat` `ServicesShowcase` `PillarVisual` `Testimonials` `FeaturedInsights` `FounderBand` `GetInTouch` `Newsletter`
- **services/** — `ServiceHero` `ServiceList` `ServiceDetail` `ServiceCTA` `FeatureGrid` `ChallengeGrid` `WhyGrid` `StepTimeline`
- **insights/** — `ArticleBody` `ArticleCard` `ArticleGrid` `CategoryTabs` `FeaturedArticle` `ChartRenderer` `LexicalBody`
- **quote/** — `QuoteForm`
- **admin/** — `Icon` `Logo` (Payload-only, not needed)

---

## 7. Known traps

- **🔴 Lexical rich text.** Payload stores article bodies as Lexical JSON
  (`LexicalBody.tsx`, `ArticleBody.tsx`). WordPress uses its own editor. This needs a
  real conversion pass — **not** copy-paste. Budget time in Phase 7.
- **🔴 `ChartRenderer.tsx` + `ChartBlock`.** Insights articles embed charts. No
  WordPress equivalent exists. Decide early: rebuild in the theme, or drop. **Ask before
  dropping — it is live content.**
- **`Reveal` scroll animation.** Never wrap taller-than-viewport content in a single
  Reveal — it never becomes visible. This bit us before.
- **Brand orange is `#EA832E`** (changed 2026-08-25; the whole flame ramp was
  re-anchored). Never revert to the old `#f7941d`. Grep raw `rgb()` too.
- **Only the CEO has a LinkedIn** (`/in/joyjeet-bose`). Other team badges are
  intentionally empty. Do not "fix" it.
- **Homepage design is approved and loved.** Keep it. Do not redesign.
- **Python:** use `python`, not `python3` (broken Store alias). Set `PYTHONUTF8=1` or
  writes fail with a cp1252 error.

---

## 8. Cutover rules — the dangerous part

**DNS lives at Hostinger. The registrar is GoDaddy, but its DNS panel is inert.**

1. ⛔ **NEVER move nameservers.** That drops MX/SPF and **kills company email.**
   Email is on **Microsoft 365** (`jbnewgen-com.mail.protection.outlook.com`); Resend
   runs on the `send.` subdomain.
2. ✅ Cutover = **change the A record only**, in Hostinger hPanel.
   Current A is `69.46.46.41` (Railway). The Hostinger box is `217.21.87.139`
   (LiteSpeed, alive) and is the rollback target.
3. Keep **Railway live 30 days** post-cutover as instant rollback. Cancel Railway,
   Neon and R2 only at day 30.
4. Hostinger's Name field is relative — enter `send`, not `send.jbnewgen.com`.
5. `www` is a Vercel redirect stub; the `_vercel` TXT record is load-bearing.

### Attribution — carry it deliberately

`public/humans.txt` and the console credit in `DevTag.tsx` **do not migrate themselves.**
Home is the theme's `style.css` `Author:` header.

Palash wants a **signed attribution agreement** from Joyjeet and keeps deferring it.
**Leverage is highest at Phase 11 sign-off, before final handover.** Raise it there.

---

## 9. Content sources — hard rule

**Never invent content shown on the site.** Every word and image comes from:

1. `../jbnewgen/UPDATE/` — read its `README.txt` first
2. the live jbnewgen.com (via firecrawl, or the `php-era-backup` mirror)

### UPDATE/ folder map

| File | Purpose |
|---|---|
| File 1 | homepage slider headline corrections |
| File 2 (`.xlsx`) | correct Services list — already done, check alignment |
| Files 3–6 | service pages. **HTML** = design guide only (no picture changes), **A** = headline/landing text, **B** = sub-service text |
| `jbnewgen-about-ceo` | About → CEO |
| `jbnewgen-about-company` | About → Company |

**Decision pending:** ship UPDATE/ content to the live Next.js site now (done twice) or
land it once in WordPress (~20 day wait). Recommendation: **once, in WordPress**, unless
Joyjeet is actively chasing it.

---

## 10. Phases — ~18–20 working days

| # | Phase | Days | Notes |
|---|---|---|---|
| 0 | Safety net | ✅ | tags pushed |
| 1 | New repo | ✅ | this repo |
| 2 | Context transfer | ✅ | this file |
| 3 | Local WordPress | 0.5 | LocalWP/Docker, empty theme wired |
| 4 | 🔴 **Content model** | 4 | CPTs, options pages, Carbon Fields. **Ends with client review of the admin.** |
| 5 | Theme skeleton | 2 | header/footer/nav, `#EA832E` ramp, 14 fonts, tokens |
| 6 | 🔴 **Page templates** | 4 | all routes; **UPDATE/ content lands here** |
| 7 | Content migration | 2 | 41 tables to MySQL; **Lexical conversion** |
| 8 | Media | 0.5 | ~7 MB to `wp-content/uploads/` |
| 9 | Forms & email | 1.5 | contact + quote + newsletter; M365/Resend untouched |
| 10 | Admin polish | 1 | `/admin` rename, 2FA, branding, hide clutter |
| 11 | Staging | 1 | `staging.jbnewgen.com` — live site untouched; **client sign-off** |
| 12 | Cutover | 0.5 + 30d | A record only; Railway stays as rollback |

Phases 4 and 6 carry the most risk.

---

## 11. Working rules

1. **Finding files/components uses the `graphify` skill.** Not Glob/Grep/find. Refresh
   the index at the start of work. The `jbnewgen` graph lives at
   `../jbnewgen/graphify-out/graph.json` (545 nodes, 22 communities).
2. **Missing content uses `firecrawl`** against the live site — only when `UPDATE/`
   genuinely lacks it. Note: the firecrawl CLI has no API key and blocks on an
   interactive prompt; curl the live site or use the mirror instead.
3. **Replies in caveman-compress style** — terse, compressed, all technical substance kept.
4. **Never invent site content.** See §9.
5. **Check `UPDATE/README.txt` before any content or design decision.**
6. **85% standardization** — pages share the same components; order may differ.
7. **Do not create new components without asking.** Almost everything already exists.

### Note on the old repo's RULE 6

`../jbnewgen/.env.local` points at the **live production Neon database** in **push
mode** — saving a collection file reshapes production schema instantly, with no
migration step and no confirmation. **Additive changes only there.** Renaming a field,
changing its type, or renaming a slug **drops the column and its data permanently.**

That risk **does not apply to this repo** — nothing here touches Neon. But if you switch
back to `../jbnewgen` for reference, do not save collection files.
