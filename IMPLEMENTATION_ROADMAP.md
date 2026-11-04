# Implementation Roadmap

## Phase 1 — foundation (implemented, pending production acceptance)

Laravel baseline, RBAC, site/device inventory, monitoring-profile management, worker heartbeat model, MySQL schema, tests, and environment configuration.

Acceptance: `php artisan test` passes; MySQL migration and seeded administrative login must be verified in the target environment.

## Phase 2

Python 3.11 worker, ICMP collector, check results, performance metrics, scheduler locking, incident lifecycle, dashboard telemetry, and worker API authentication.

## Phases 3–6

SNMP/Windows/FortiGate; WAN/VPN/topology/discovery; IP conflict and DHCP intelligence; email alerts, reporting, retention, Windows Service packaging, and production hardening.
