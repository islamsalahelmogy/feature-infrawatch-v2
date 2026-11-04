# Existing Features

## Before rebuild

None. The repository at commit `7e17d10` contained only `README.md`.

## Implemented in Phase 1

- Session authentication with throttled sign-in and session regeneration.
- Database-backed roles (`administrator`, `operator`, `viewer`) and route authorization.
- Site/factory inventory CRUD.
- Device inventory CRUD with site, type, monitoring profile, IP, criticality, and status.
- Monitoring-profile CRUD with validation for interval, timeout, retries, and failure threshold.
- Database-backed dashboard counts and worker-heartbeat display.

ICMP, SNMP, incidents, alert delivery, discovery, and the Python worker are intentionally not marked complete; they are Phase 2+ work.
