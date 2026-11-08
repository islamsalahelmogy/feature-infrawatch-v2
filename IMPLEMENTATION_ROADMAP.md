# Implementation Roadmap

## Phase 1 — foundation (implemented, pending production acceptance)

Laravel baseline, RBAC, site/device inventory, monitoring-profile management, worker heartbeat model, MySQL schema, tests, and environment configuration.

Acceptance: `php artisan test` passes; MySQL migration and seeded administrative login must be verified in the target environment.

## Phase 2 — scheduled monitoring and administrator notifications

Python 3.11 worker, ICMP collector, check results, incident lifecycle, dashboard telemetry, and worker API authentication. Initial ICMP ingestion and worker code exist; live LAN acceptance remains pending.

Add durable scheduled runs, run completion tracking and locking, administrator CRUD, and queued email summaries of down devices after each scheduled run. Email delivery moves forward from Phase 6 into Phase 2. Track delivery per run and recipient, retry failures, and prevent duplicate notification jobs for the same run. Persistently down devices remain included in subsequent scheduled-run summaries.

## Phase 3 — protocol monitoring

SNMP, Windows, and FortiGate collectors, performance metrics and history, secure credential handling, and threshold-based incidents.

## Phase 4 — network visibility

WAN/VPN monitoring, topology, approved network discovery, and device dependencies.

## Phase 5 — address intelligence

IP conflicts, IP/MAC history, DHCP intelligence, and inventory reconciliation.

## Phase 6 — operational readiness

Reporting, retention, Windows Service packaging, scheduler/queue health, backups and restore verification, load testing, and production hardening. Basic outage email is a Phase 2 requirement, not deferred to this phase.

See [README.md](README.md) for the detailed Arabic plan, acceptance criteria, proposed data model, email behavior, and optional feature suggestions. Unchecked roadmap items describe planned work, not implemented capabilities.
