# Technical Debt

- Python 3.11 is not installed on this machine, so worker installation and hardware protocol integrations cannot yet be executed or verified.
- A MySQL 8 database and least-privileged database account have not been provisioned locally; automated tests use in-memory SQLite only.
- Audit-log writes need to be connected to each mutation before production deployment.
- Site-level assignments and permission-granularity tables exist as the starting model but are not yet enforced beyond role checks.
- Production setup must replace the seeded default administrator password through environment variables before exposure.
