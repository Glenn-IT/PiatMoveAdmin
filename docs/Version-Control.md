# Version Control — PiatMove Admin

## Rollout Schedule

| Version | Week | Feature              | Pages Unlocked                          | Pages Still Gated                                              |
|---------|------|----------------------|-----------------------------------------|----------------------------------------------------------------|
| v1.00   | 1    | Login / Forgot PW    | index.php, forgot-password.php          | dashboard.php, users.php, drivers.php, bookings.php, profile.php |
| v1.01   | 2    | Dashboard            | dashboard.php                           | users.php, drivers.php, bookings.php, profile.php              |
| v1.02   | 3    | Manage Users         | users.php                               | drivers.php, bookings.php, profile.php                         |
| v1.03   | 4    | Manage Drivers       | drivers.php                             | bookings.php, profile.php                                      |
| v1.04   | 5    | Bookings             | bookings.php                            | profile.php                                                    |
| v1.05   | 6    | Profile (Full System)| profile.php                             | —                                                              |

---

## Under Construction Strategy

- `components/under-construction.php` defines `CURRENT_VERSION` at the top.
- Every gated page has `<?php require_once __DIR__ . '/components/under-construction.php'; ?>` as its **very first line**.
- The component renders a full-page card with a version badge and a **Logout button** that calls `logout.php`, which fully wipes the session and cookie before redirecting to the login page. This prevents any session trapping when a user navigates to a gated URL.
- To unlock a page for a version: **remove** the `require_once` gate line from that page and **bump** `CURRENT_VERSION` in `components/under-construction.php`.

---

## Git Commands Per Version

```bash
# Stage only the files changed for this version
git add components/under-construction.php <unlocked-page.php>

# Commit
git commit -m "feat: implement vX.XX - unlock [Feature Name]"

# Tag and push
git tag vX.XX
git push origin main
git push origin vX.XX
```

---

## How Git Tags Work

Each `git tag vX.XX` creates a **permanent, immutable snapshot** of the repo at that exact commit. Even if later commits change the code, the tag always points back to the state of the project at that presentation moment. Tags are pushed separately with `git push origin vX.XX`.

---

## GitHub Release Tags

| Version | Tag Name | Commit Hash |
|---------|----------|-------------|
| v1.00   | v1.00    | f02282ef02ae5666f9ef8c985aa9bd017bdd7164 |
| v1.01   | v1.01    | 72adbf4b5ff7d74964f777c2c7c4662828182e81 |
| v1.02   | v1.02    | 7c1f71173c9a61156394da853cde0fad1152d615 |
| v1.03   | v1.03    | c4aee5a630a1128a148be3e5a9052a10540a58ee |
| v1.04   | v1.04    | ea19d9a5ef101becd77e60ff43ebbbb72cd00f69 |
| v1.05   | v1.05    | cf0859925bb1e6ef5446e592d6753da8d8cf4a05 |

After all versions are pushed, fill in the hash column by running:

```bash
git tag | sort | xargs -I{} git log -1 --format="{} %H" {}
```

Then commit and push the updated docs:

```bash
git add docs/Version-Control.md
git commit -m "docs: add commit hashes to version control table"
git push origin main
```

---

## When a Prof or Client Requests Changes After a Presentation

```bash
# 1. Fix on main
git checkout main
git add <changed-files>
git commit -m "feat: update [page] per feedback"
git push origin main

# 2. Move the tag to the new commit
git tag -d vX.XX
git push origin :refs/tags/vX.XX
git tag vX.XX
git push origin vX.XX
```

This re-points the tag to the corrected commit so the GitHub release reflects the updated version.
