#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-https://growbig-web.ddev.site}"
export BASE_URL

echo "Verifying backend MVP at ${BASE_URL}..."

echo "Checking Git working tree..."
git status --short

echo "Checking Drupal custom code..."
./scripts/check-code.sh

echo "Checking Drupal platform..."
./scripts/check-drupal.sh

echo "Checking API smoke tests..."
./scripts/test-api.sh

echo "Checking required API endpoints..."
python3 - <<'INNERPY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"]

def fetch(path):
    result = subprocess.run(
        ["curl", "-ks", f"{base_url}{path}"],
        check=True,
        capture_output=True,
        text=True,
    )

    try:
        return json.loads(result.stdout)
    except json.JSONDecodeError as exc:
        raise AssertionError(f"{path} did not return valid JSON: {result.stdout[:500]}") from exc

def require(condition, message):
    if not condition:
        raise AssertionError(message)

site = fetch("/api/v1/site")
pages = fetch("/api/v1/pages")
header_menu = fetch("/api/v1/menus/header")
footer_menu = fetch("/api/v1/menus/footer")
home = fetch("/api/v1/pages/home")
about = fetch("/api/v1/pages/about")
careers = fetch("/api/v1/pages/careers")
contact = fetch("/api/v1/pages/contact")
services = fetch("/api/v1/content/services")
partners = fetch("/api/v1/content/partners")
team = fetch("/api/v1/content/team")
jobs = fetch("/api/v1/content/jobs")
service_detail = fetch("/api/v1/content/services/website-development")
job_detail = fetch("/api/v1/content/jobs/frontend-developer")

require(isinstance(site, dict), "Site endpoint must return an object.")
require("name" in site or "title" in site, "Site endpoint must include a site name/title field.")

require(pages.get("contractVersion") == "1.0", "Pages index must use contractVersion 1.0.")
require(pages.get("count", 0) >= 2, "Pages index must include at least home and about pages.")

page_slugs = [item.get("slug") for item in pages.get("items", [])]
require("home" in page_slugs, "Pages index must include home.")
require("about" in page_slugs, "Pages index must include about.")

require(home.get("contractVersion") == "1.0", "Home page must use contractVersion 1.0.")
require(about.get("contractVersion") == "1.0", "About page must use contractVersion 1.0.")

require(home.get("route", {}).get("path") == "/", "Home page route.path must be /.")
require(about.get("route", {}).get("path") == "/about", "About page route.path must be /about.")
require(careers.get("route", {}).get("path") == "/careers", "Careers page route.path must be /careers.")
require(contact.get("route", {}).get("path") == "/contact", "Contact page route.path must be /contact.")

require(services.get("source") == "services", "Services endpoint source mismatch.")
require(services.get("count", 0) >= 1, "Services endpoint must return at least one item.")

require(partners.get("source") == "partners", "Partners endpoint source mismatch.")
require(partners.get("count", 0) >= 1, "Partners endpoint must return at least one item.")

require(team.get("source") == "team", "Team endpoint source mismatch.")
require(team.get("count", 0) >= 1, "Team endpoint must return at least one item.")

require(jobs.get("source") == "jobs", "Jobs endpoint source mismatch.")
require(jobs.get("count", 0) >= 1, "Jobs endpoint must return at least one item.")

require(service_detail.get("source") == "services", "Service detail source mismatch.")
require(service_detail.get("key") == "website-development", "Service detail key mismatch.")
require(service_detail.get("item", {}).get("key") == "website-development", "Service detail item key mismatch.")

require(job_detail.get("source") == "jobs", "Job detail source mismatch.")
require(job_detail.get("key") == "frontend-developer", "Job detail key mismatch.")
require(job_detail.get("item", {}).get("key") == "frontend-developer", "Job detail item key mismatch.")

print("Required backend MVP endpoints verified.")
INNERPY

echo "Backend MVP verification passed."
