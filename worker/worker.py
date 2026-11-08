"""InfraWatch Phase 2 ICMP worker. Requires Python 3.11+ on Windows."""

import asyncio
import json
import os
import re
import subprocess
import time
import urllib.error
import urllib.request
from datetime import datetime, timezone

API_URL = os.environ.get("INFRAWATCH_API_URL", "http://127.0.0.1:8000/api").rstrip("/")
TOKEN = os.environ.get("INFRAWATCH_WORKER_TOKEN", "")
MAX_CONCURRENCY = max(1, int(os.environ.get("INFRAWATCH_MAX_CONCURRENCY", "50")))
POLL_SECONDS = max(5, int(os.environ.get("INFRAWATCH_WORKER_POLL_SECONDS", "15")))
WORKER_NAME = os.environ.get("INFRAWATCH_WORKER_NAME", "icmp-worker-01")


def api_request(path: str, method: str = "GET", payload: dict | None = None) -> dict:
    if not TOKEN:
        raise RuntimeError("INFRAWATCH_WORKER_TOKEN is required")
    data = None if payload is None else json.dumps(payload).encode("utf-8")
    request = urllib.request.Request(f"{API_URL}{path}", data=data, method=method, headers={"X-Worker-Token": TOKEN, "Content-Type": "application/json"})
    with urllib.request.urlopen(request, timeout=15) as response:
        return json.loads(response.read().decode("utf-8"))


def run_ping(ip_address: str, timeout_ms: int) -> tuple[int, str]:
    try:
        completed = subprocess.run(["ping", "-n", "1", "-w", str(timeout_ms), ip_address], capture_output=True, text=True, timeout=(timeout_ms / 1000) + 2, check=False)
        return completed.returncode, completed.stdout + completed.stderr
    except subprocess.TimeoutExpired:
        return 1, "Ping command timed out."


async def ping(device: dict, semaphore: asyncio.Semaphore) -> dict:
    async with semaphore:
        timeout_ms = int(device["timeout_seconds"]) * 1000
        started = time.perf_counter()
        return_code, output = await asyncio.to_thread(run_ping, device["ip_address"], timeout_ms)
        elapsed_ms = round((time.perf_counter() - started) * 1000, 2)
        match = re.search(r"time[=<](\d+)", output, re.IGNORECASE)
        return {"device_id": device["id"], "status": "success" if return_code == 0 else "failure", "response_time_ms": float(match.group(1)) if match else elapsed_ms, "packet_loss_percent": 0 if return_code == 0 else 100, "error_message": None if return_code == 0 else output[-1000:], "checked_at": datetime.now(timezone.utc).isoformat()}


async def run() -> None:
    semaphore = asyncio.Semaphore(MAX_CONCURRENCY)
    next_due: dict[int, float] = {}
    while True:
        try:
            api_request("/worker/heartbeats", "POST", {"worker_name": WORKER_NAME, "status": "healthy", "metadata": {"max_concurrency": MAX_CONCURRENCY}, "seen_at": datetime.now(timezone.utc).isoformat()})
            scheduled = api_request("/worker/runs/next").get("run")
            scheduled_ids: set[int] = set()
            if scheduled:
                scheduled_devices = scheduled["devices"]
                scheduled_results = await asyncio.gather(*(ping(device, semaphore) for device in scheduled_devices))
                for result in scheduled_results:
                    result["monitoring_run_id"] = scheduled["id"]
                    api_request("/worker/icmp-results", "POST", result)
                    scheduled_ids.add(result["device_id"])
            devices = api_request("/worker/devices")["devices"]
            due = [device for device in devices if device["id"] not in scheduled_ids and time.monotonic() >= next_due.get(device["id"], 0)]
            results = await asyncio.gather(*(ping(device, semaphore) for device in due))
            for device, result in zip(due, results):
                api_request("/worker/icmp-results", "POST", result)
                next_due[device["id"]] = time.monotonic() + device["interval_seconds"]
        except (RuntimeError, urllib.error.URLError, TimeoutError, ValueError) as error:
            print(f"worker error: {error}", flush=True)
        await asyncio.sleep(POLL_SECONDS)


if __name__ == "__main__":
    asyncio.run(run())
