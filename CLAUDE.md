# GiveTwice — Project Instructions

## Tech Stack

- **Framework:** Laravel 12, PHP 8.2+
- **Testing:** Pest 3 (describe/it pattern, factories, `$this->artisan()`)
- **Linting:** Laravel Pint (`vendor/bin/pint --dirty`)
- **Static Analysis:** Larastan/PHPStan (`composer analyse`)
- **Queue:** Laravel Horizon (Redis)
- **Monitoring:** Oh Dear, Flare
- **Deploy:** Envoy (`envoy run deploy`) — **human-only, never automated**

## Conventions

- Follow existing code patterns — check similar files before writing new ones
- Run `vendor/bin/pint --dirty` after every code change
- Run `composer analyse` after every code change
- All tests must pass before creating a PR
- Use Pest describe/it blocks, not PHPUnit classes
- Use factories for test data, not raw DB inserts (unless testing DB-level concerns)
- Invokable controllers, action classes where appropriate

## Production Access (read-only)

```bash
ssh givetwice@srv01.givetwice.app
# App root: /home/givetwice/givetwice.app/current/
# Logs: /home/givetwice/givetwice.app/persistent/storage/logs/laravel.log
# Metrics: php artisan ops:metrics (outputs JSON)
```

## AI Ops

This project is operated by an autonomous multi-agent system. State lives in `.ai-ops/` (gitignored).

### State Files

| File | Purpose |
|------|---------|
| `.ai-ops/STATE.md` | Current priorities, dispatch flags, alerts |
| `.ai-ops/BACKLOG.md` | Task queue with priorities (P0-P3) |
| `.ai-ops/METRICS.md` | Production metrics snapshots |
| `.ai-ops/ROADMAP.md` | Quarterly goals (human-maintained) |
| `.ai-ops/CHANGELOG.md` | What shipped and when |
| `.ai-ops/BLOCKED.md` | Issues requiring human intervention |

### Dispatch Protocol

1. CEO proposes a task: writes `## Pending Approval` in STATE.md + sends Pushover notification
2. **Mattias approves** (`./ai-ops/scripts/approve.sh`) or **rejects** (`./ai-ops/scripts/reject.sh "reason"`)
3. Dev agent reads the approved `DISPATCH_DEV` flag, implements the task, creates a PR, sets `QA_NEEDED=true`
4. QA agent reads the flag, reviews the PR, approves or requests changes
5. Mattias merges approved PRs and deploys via `envoy run deploy`

### Safety Rules

- **Never** push directly to main — all changes go through PRs
- **Never** write to production database
- **Never** modify `.env`, credentials, or deployment config
- **Never** deploy — only Mattias deploys
- **Never** send emails to users directly
- **Never** invoke `claude` or spawn sub-agents — each agent runs as its own cron job
- **Max** 500 lines net new code per PR
- **Always** run Pint + PHPStan + tests before creating a PR
- Flag migrations with `NEEDS_DEPLOY_REVIEW` label

### Lesson: Agent Containment (2026-03-23)

The CEO agent's first run invoked `claude` directly via Bash, spawning Dev and QA agents inline. This caused a runaway: 22 commits, 2 PRs merged, $1+ burned in one session. Fix: agents must NEVER invoke `claude` — they only set dispatch flags. Containment is enforced at two levels: (1) agent prompt rules, (2) `--allowedTools` in run-agent.sh restricting Bash commands per agent.
