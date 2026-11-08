# InfraWatch monitoring worker

This Python 3.11+ worker polls the application for devices with an active ICMP monitoring profile, performs bounded-concurrency Windows `ping` checks, and submits results plus a heartbeat over the authenticated worker API.

Set `INFRAWATCH_API_URL` (for example `http://127.0.0.1:8000/api`), `INFRAWATCH_WORKER_TOKEN`, and optionally `INFRAWATCH_MAX_CONCURRENCY` before starting it:

```powershell
python worker.py
```

Run it under a dedicated, least-privilege Windows service account. Its token authorizes monitoring ingestion only; it does not expose Laravel credentials or device secrets.
