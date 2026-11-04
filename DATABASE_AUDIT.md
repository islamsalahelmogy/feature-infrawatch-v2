# Database Audit and Redesign

There was no inherited database. The Phase 1 migration creates normalized roles, permissions, role assignments, sites, device types, monitoring profiles, devices, monitoring checks, worker heartbeats, and audit logs.

Production requires MySQL 8 with InnoDB and UTF-8 (`utf8mb4`). Foreign keys protect site/type/profile references, unique keys prevent duplicate site codes and duplicate management IPs within a site, and monitoring/device lookup indexes are included. Credentials, raw results, metrics, incidents, interfaces, WAN/VPN data, network intelligence, alert records, and retention partitions remain future migrations; no plaintext credential field exists in Phase 1.
