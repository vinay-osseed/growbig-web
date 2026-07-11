#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${1:-https://growbig-web.ddev.site}"
export BASE_URL

echo "Testing Site API..."
curl -ks "${BASE_URL}/api/v1/site" | python3 -m json.tool >/dev/null

echo "Testing Menu API..."
curl -ks "${BASE_URL}/api/v1/menus" | python3 -m json.tool >/dev/null
curl -ks "${BASE_URL}/api/v1/menus/header" | python3 -m json.tool >/dev/null
curl -ks "${BASE_URL}/api/v1/menus/footer" | python3 -m json.tool >/dev/null

echo "Testing Analytics Config API..."
curl -ks "${BASE_URL}/api/v1/analytics/config" | python3 -m json.tool >/dev/null

echo "Testing Pages Index API..."
python3 - <<'INNERPY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"]

result = subprocess.run(
    ["curl", "-ks", f"{base_url}/api/v1/pages"],
    check=True,
    capture_output=True,
    text=True,
)

data = json.loads(result.stdout)

assert data["contractVersion"] == "1.0"
assert data["count"] >= 2

slugs = [item["slug"] for item in data["items"]]
assert "home" in slugs
assert "about" in slugs
assert "careers" in slugs
assert "contact" in slugs

home = next(item for item in data["items"] if item["slug"] == "home")
about = next(item for item in data["items"] if item["slug"] == "about")

assert home["route"]["path"] == "/"
assert home["route"]["apiPath"] == "/api/v1/pages/home"
assert about["route"]["path"] == "/about"
assert about["route"]["apiPath"] == "/api/v1/pages/about"

print("Pages index API checks passed.")
INNERPY

echo "Testing Home Page API..."
curl -ks "${BASE_URL}/api/v1/pages/home" | python3 -m json.tool >/dev/null

echo "Testing About Page API..."
curl -ks "${BASE_URL}/api/v1/pages/about" | python3 -m json.tool >/dev/null

echo "Testing Careers Page API..."
curl -ks "${BASE_URL}/api/v1/pages/careers" | python3 -m json.tool >/dev/null

echo "Testing Contact Page API..."
curl -ks "${BASE_URL}/api/v1/pages/contact" | python3 -m json.tool >/dev/null

echo "Validating analytics config..."
python3 - <<'INNERPY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"]

result = subprocess.run(
    ["curl", "-ks", f"{base_url}/api/v1/analytics/config"],
    check=True,
    capture_output=True,
    text=True,
)

data = json.loads(result.stdout)

assert "enabled" in data
assert "provider" in data
assert "measurementId" in data
assert isinstance(data["enabled"], bool)
assert data["provider"] in ["none", "google_analytics"]

print("Analytics config checks passed.")
INNERPY

echo "Validating frontend menus..."
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
    return json.loads(result.stdout)

menus = fetch("/api/v1/menus")
header = fetch("/api/v1/menus/header")
footer = fetch("/api/v1/menus/footer")

assert "header" in menus["items"]
assert "footer" in menus["items"]

for menu in (header, footer):
    assert menu["type"] == "menu"
    assert menu["count"] >= 4

    slugs = [item["slug"] for item in menu["items"]]
    assert slugs == ["home", "about", "careers", "contact"]

    urls = [item["url"] for item in menu["items"]]
    assert urls == ["/", "/about", "/careers", "/contact"]

print("Frontend menu checks passed.")
INNERPY

echo "Validating dynamic page contract..."
python3 - <<'PY'
import json
import os
import subprocess

base_url = os.environ.get("BASE_URL", "https://growbig-web.ddev.site").rstrip("/")

def fetch(path):
    result = subprocess.run(
        ["curl", "-sk", base_url + path],
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

expected_pages = {
    "home": "/",
    "about": "/about",
    "careers": "/careers",
    "contact": "/contact",
}

for slug, route_path in expected_pages.items():
    page = fetch(f"/api/v1/pages/{slug}")

    require(page.get("contractVersion") == "1.0", f"{slug} contractVersion must be 1.0.")
    require(page.get("type") == "page", f"{slug} type must be page.")
    require(page.get("slug") == slug, f"{slug} slug mismatch.")
    require(page.get("route", {}).get("path") == route_path, f"{slug} route.path must be {route_path}.")
    require(page.get("route", {}).get("apiPath") == f"/api/v1/pages/{slug}", f"{slug} route.apiPath mismatch.")
    require(page.get("api", {}).get("self") == f"/api/v1/pages/{slug}", f"{slug} api.self mismatch.")
    require(isinstance(page.get("sections"), list), f"{slug} sections must be a list.")
    require(len(page.get("sections", [])) >= 1, f"{slug} must have at least one section.")

about_payload = json.dumps(fetch("/api/v1/pages/about"))
for required_text in [
    "We Are GrowBig",
    "Mission",
    "Vision",
    "Core Values",
    "Leadership",
]:
    require(required_text in about_payload, f"About page is missing required mockup text: {required_text}")

print("Dynamic page contract checks passed.")
PY
echo "Dynamic page contract checks passed."
echo "Testing missing Page API..."
STATUS_CODE="$(curl -ks -o /tmp/growbig-missing-page-api.json -w "%{http_code}" "${BASE_URL}/api/v1/pages/missing")"

if [ "${STATUS_CODE}" != "404" ]; then
  echo "Expected 404 for missing page, got ${STATUS_CODE}"
  cat /tmp/growbig-missing-page-api.json
  exit 1
fi

python3 -m json.tool /tmp/growbig-missing-page-api.json >/dev/null

echo "Validating top-level careers and contact pages..."
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
    return json.loads(result.stdout)

careers = fetch("/api/v1/pages/careers")
contact = fetch("/api/v1/pages/contact")

assert careers["slug"] == "careers"
assert careers["route"]["path"] == "/careers"
assert contact["slug"] == "contact"
assert contact["route"]["path"] == "/contact"

careers_section_types = [section["type"] for section in careers["sections"]]
contact_section_types = [section["type"] for section in contact["sections"]]

assert "hero" in careers_section_types
assert "contentList" in careers_section_types
assert "hero" in contact_section_types
assert "cta" in contact_section_types

jobs_sections = [
    section for section in careers["sections"]
    if section["type"] == "contentList" and section.get("source") == "jobs"
]

assert jobs_sections
assert jobs_sections[0]["items"]
assert jobs_sections[0]["items"][0]["type"] == "job"

print("Top-level careers and contact page checks passed.")
INNERPY

echo "Validating dynamic content lists..."
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
    return json.loads(result.stdout)

home = fetch("/api/v1/pages/home")
about = fetch("/api/v1/pages/about")

home_content_lists = [section for section in home["sections"] if section["type"] == "contentList"]
about_content_lists = [section for section in about["sections"] if section["type"] == "contentList"]

assert home_content_lists, "Home contentList section missing"
assert about_content_lists, "About contentList section missing"

partners = home_content_lists[0].get("items", [])
team = about_content_lists[0].get("items", [])

assert len(partners) == 4, f"Expected 4 partners, got {len(partners)}"
assert len(team) == 3, f"Expected 3 team members, got {len(team)}"

print("Dynamic content list checks passed.")
INNERPY

echo "Validating reusable content endpoints..."
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
    return json.loads(result.stdout)

services = fetch("/api/v1/content/services")
partners = fetch("/api/v1/content/partners")
team = fetch("/api/v1/content/team")
limited_services = fetch("/api/v1/content/services?limit=3")

assert services["source"] == "services"
assert services["count"] == 6
assert partners["source"] == "partners"
assert partners["count"] == 4
assert team["source"] == "team"
assert team["count"] == 3
assert limited_services["count"] == 3

print("Reusable content endpoint checks passed.")
INNERPY

echo "Validating reusable jobs endpoints..."
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
    return json.loads(result.stdout)

jobs = fetch("/api/v1/content/jobs")
job_detail = fetch("/api/v1/content/jobs/frontend-developer")

assert jobs["source"] == "jobs"
assert jobs["count"] >= 1

keys = [item["key"] for item in jobs["items"]]
assert "frontend-developer" in keys

assert job_detail["source"] == "jobs"
assert job_detail["key"] == "frontend-developer"
assert job_detail["item"]["type"] == "job"
assert job_detail["item"]["key"] == "frontend-developer"
assert job_detail["item"]["title"] == "Frontend Developer"

print("Reusable jobs endpoint checks passed.")
INNERPY

echo "Validating reusable content detail endpoints..."
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
    return json.loads(result.stdout)

service = fetch("/api/v1/content/services/website-development")
partner = fetch("/api/v1/content/partners/aws")
team = fetch("/api/v1/content/team/founder-ceo")

assert service["source"] == "services"
assert service["key"] == "website-development"
assert service["item"]["type"] == "service"

assert partner["source"] == "partners"
assert partner["key"] == "aws"
assert partner["item"]["type"] == "partner"

assert team["source"] == "team"
assert team["key"] == "founder-ceo"
assert team["item"]["type"] == "teamMember"


mobile_detail = fetch("/api/v1/content/services/mobile-app-development")
assert mobile_detail["source"] == "services"
assert mobile_detail["key"] == "mobile-app-development"
assert mobile_detail["item"]["key"] == "mobile-app-development"
assert mobile_detail["item"]["title"] == "Mobile App Development"

print("Reusable content detail endpoint checks passed.")
INNERPY

echo "Validating form API endpoints..."
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
    return json.loads(result.stdout)

def post(path, payload):
    result = subprocess.run(
        [
            "curl",
            "-ks",
            "-X", "POST",
            "-H", "Content-Type: application/json",
            "-d", json.dumps(payload),
            f"{base_url}{path}",
        ],
        check=True,
        capture_output=True,
        text=True,
    )
    return json.loads(result.stdout)

contact_form = fetch("/api/v1/forms/contact-us")
job_form = fetch("/api/v1/forms/job-application")

assert contact_form["webformId"] == "contact_us"
assert job_form["webformId"] == "job_application"

contact_fields = [field["name"] for field in contact_form["fields"]]
job_fields = [field["name"] for field in job_form["fields"]]

assert "budget_range" in contact_fields
assert "resume_upload" in job_fields

contact_submission = post("/api/v1/forms/contact-us/submit", {
    "data": {
        "name": "API Test Contact",
        "email": "contact-test@example.com",
        "subject": "API test enquiry",
        "message": "This is an automated API test contact submission.",
        "preferred_contact_method": "email",
        "consent": True
    }
})

job_submission = post("/api/v1/forms/job-application/submit", {
    "data": {
        "job_key": "frontend-developer",
        "job_title": "Frontend Developer",
        "name": "API Test Applicant",
        "email": "job-test@example.com",
        "phone": "+910000000000",
        "current_location": "Remote",
        "experience_years": 2,
        "portfolio_url": "https://example.com",
        "linkedin_url": "https://www.linkedin.com",
        "message": "This is an automated API test job application.",
        "consent": True
    }
})

assert contact_submission["status"] == "success"
assert contact_submission["submissionId"] > 0
assert job_submission["status"] == "success"
assert job_submission["submissionId"] > 0

print("Form API endpoint checks passed.")
INNERPY

echo "API smoke tests passed."
