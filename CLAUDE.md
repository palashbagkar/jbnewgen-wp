# CLAUDE.md — jbnewgen-wp

**READ `CONTEXT.md` FIRST.** It is the full brief: why this project exists, the content
model, the phase plan, the cutover rules, and every known trap. This file is only the
working rules.

---

## JOB

- You are a full stack web developer.
- At the start of each session, tell the user whether the skills (**firecrawl**,
  **graphify**, **caveman-compress**) are connected.
- Goal: rebuild jbnewgen.com as a **WordPress custom theme** (hand-written PHP
  templates, no page builder) on Hostinger shared hosting.
- The live Next.js site is the **sibling** repo `../jbnewgen`. It is frozen and is the
  rollback target. Read from it freely; do not deploy from it.
- **I love the homepage design, colour scheme and organisation. Keep it the same.**
- **Do NOT create new components without asking.** Almost everything already exists —
  42 components are inventoried in `CONTEXT.md` §6.
- **85% standardization:** each page should consist of the same components. Different
  order is fine.

---

## SKILLS — MANDATORY, NOT OPTIONAL

Breaking a HARD RULE = stop and redo that step.

**SESSION START** — before your first substantive action, post this checklist filled in:

- Skills connected? firecrawl / graphify / caveman-compress (say which are and are not)
- Read `CONTEXT.md`? (yes/no)
- Read `../jbnewgen/UPDATE/README.txt`? (yes/no)

### HARD RULES

**RULE 1 — FINDING FILES OR COMPONENTS → use the `graphify` skill.**
You may NOT use Glob, Grep or find to locate components. If you catch yourself doing so,
stop and redo the search through graphify.
- Read the graphify `SKILL.md` before first use each session.
- Refresh the graph index at the start of work so results are reliable.
- The Next.js graph is at `../jbnewgen/graphify-out/graph.json` (545 nodes).
- Build a graph for this repo once there is PHP to index.

**RULE 2 — TALKING TO THE USER → caveman-compress style.**
Terse, compressed, all technical substance kept.

**RULE 3 — CONTENT MISSING FROM `../jbnewgen/UPDATE/` → use `firecrawl`** against the
live jbnewgen.com. Only when UPDATE/ genuinely lacks it — do not overdo it. The
firecrawl CLI has no API key and blocks on an interactive prompt; curl the live site or
use the `php-era-backup` mirror instead.

**RULE 4 — NEVER invent content (words or images) shown on the site.** Everything
displayed comes from `../jbnewgen/UPDATE/` or the live jbnewgen.com only.

**RULE 5 — Read `../jbnewgen/UPDATE/README.txt` and check that folder BEFORE making any
content or design decision.**

**RULE 6 — DNS AND EMAIL ARE LIVE.**
- ⛔ **NEVER move nameservers.** DNS is at Hostinger. Moving it drops MX/SPF and **kills
  company email** (Microsoft 365 + Resend on the `send.` subdomain).
- Cutover changes the **A record only**, in Hostinger hPanel.
- Keep Railway live 30 days after cutover as rollback.
- Before any DNS change: **stop and ask the user first.**

**RULE 7 — THE OLD REPO'S DATABASE IS LIVE.**
`../jbnewgen/.env.local` points at the **production Neon database** and Payload runs in
**push mode** — saving a collection or global file reshapes production schema instantly,
with no migration step and no confirmation. Read files there freely. **Do not save
collection or global files in `../jbnewgen`.** Renaming a field, changing its type or
renaming a slug permanently drops the column and every value in it.

---

## END OF EVERY REPLY

Output exactly one line, with a reason for any "no":

```
Skills → graphify:<used|n/a> caveman:<y|n> firecrawl:<used|n/a>  (reason for any skip)
```

**Why this exists:** in past sessions these rules were loaded but ignored under task
focus. The end-of-reply line makes each turn auditable so drift is caught immediately.
