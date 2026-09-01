# jbnewgen-wp

WordPress custom theme for **jbnewgen.com** — hand-written PHP templates, no page
builder — replacing the Next.js 16 + Payload CMS + Neon Postgres stack currently
running on Railway.

**Start here → [`CONTEXT.md`](CONTEXT.md)** — the full brief: rationale, content model,
phase plan, cutover rules, known traps.
Working rules for AI sessions → [`CLAUDE.md`](CLAUDE.md).

---

## Status

**Phase 2 of 12 complete.** No PHP written yet.

| # | Phase | Status |
|---|---|---|
| 0 | Safety net — backup tags pushed | ✅ |
| 1 | New repo | ✅ |
| 2 | Context transfer | ✅ |
| 3 | Local WordPress environment | next |
| 4–12 | see [`CONTEXT.md`](CONTEXT.md) §10 | |

---

## Layout

```
C:\Users\palas\projects\
├── jbnewgen\      <- live Next.js site. FROZEN. rollback target + reference.
└── jbnewgen-wp\   <- this repo.
```

Siblings, not nested — the Next.js source at `../jbnewgen/src/**` is the reference for
transcribing components into PHP.

## Target stack

| | |
|---|---|
| Host | Hostinger shared hosting (LiteSpeed) |
| CMS | self-hosted WordPress (wordpress.org), admin at `/admin` |
| Database | MySQL |
| Custom fields | Carbon Fields, bundled in the theme — **no ACF** |
| Media | `wp-content/uploads/` — no S3, no R2 |
| Plugins | **3 maximum**: security, backup, cache |
| New spend | **$0** |

## Rollback

The Next.js site is recoverable from `../jbnewgen` at any time:

```bash
git checkout nextjs-final     # the exact working app
git checkout php-era-backup   # 48-page offline mirror + R2 media
```

Railway stays live for 30 days after cutover.
