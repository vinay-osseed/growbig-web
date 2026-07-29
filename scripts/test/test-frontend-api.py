import json
import ssl
import sys
import urllib.error
import urllib.request

BASE_URL = sys.argv[1] if len(sys.argv) > 1 else "https://site-platform.ddev.site"
CTX = ssl._create_unverified_context()

def request(method, path, payload=None):
    data = None
    headers = {}

    if payload is not None:
        data = json.dumps(payload).encode("utf-8")
        headers["Content-Type"] = "application/json"

    req = urllib.request.Request(f"{BASE_URL}{path}", data=data, headers=headers, method=method)

    try:
        with urllib.request.urlopen(req, context=CTX, timeout=30) as res:
            return res.status, json.loads(res.read().decode("utf-8"))
    except urllib.error.HTTPError as err:
        body = err.read().decode("utf-8")
        try:
            parsed = json.loads(body)
        except json.JSONDecodeError:
            parsed = {"raw": body}
        return err.code, parsed

def expect_status(status, expected, label):
    if status != expected:
        raise AssertionError(f"{label}: expected HTTP {expected}, got {status}")

def expect(value, label):
    if not value:
        raise AssertionError(label)

def expect_eq(actual, expected, label):
    if actual != expected:
        raise AssertionError(f"{label}: expected {expected!r}, got {actual!r}")

checks = []

status, bootstrap = request("GET", "/api/v2/frontend/bootstrap")
expect_status(status, 200, "bootstrap")
expect_eq(bootstrap["mode"], "single_backend", "bootstrap mode")
expect(bootstrap["template"]["reusable"] is True, "template reusable")
expect_eq(bootstrap["site"]["name"], "GrowBig Technologies LLP", "site name")
expect_eq(len(bootstrap["navigation"]["main"]), 5, "navigation count")
checks.append("bootstrap")

for slug, heading in {
    "home": "Building Digital Solutions That Drive Business Growth",
    "about": "We Are GrowBig",
    "careers": "Build Your Career at GrowBig",
    "contact": "Let's Start a Conversation",
}.items():
    status, body = request("GET", f"/api/v2/frontend/pages/{slug}")
    expect_status(status, 200, f"page:{slug}")
    expect_eq(body["page"]["sections"][0]["heading"], heading, f"{slug} hero heading")
    checks.append(f"page:{slug}")

for source, count in {
    "services": 6,
    "partners": 2,
    "team": 5,
    "jobs": 1,
}.items():
    status, body = request("GET", f"/api/v2/frontend/content/{source}")
    expect_status(status, 200, f"content:{source}")
    expect_eq(body["count"], count, f"{source} count")
    checks.append(f"content:{source}")

status, body = request("GET", "/api/v2/frontend/content/invalid")
expect_status(status, 400, "invalid content")
expect_eq(body["error"]["code"], "invalid_source", "invalid source error")
checks.append("invalid-content")

status, body = request("GET", "/api/v2/frontend/forms/contact-us")
expect_status(status, 200, "contact form")
expect_eq(
    [field["name"] for field in body["form"]["fields"] if field["required"]],
    ["full_name", "email", "message"],
    "contact required fields"
)
checks.append("form:contact-us")

status, body = request("GET", "/api/v2/frontend/forms/job-application")
expect_status(status, 200, "job form")
expect_eq(
    [field["name"] for field in body["form"]["fields"] if field["required"]],
    ["full_name", "email", "job_role", "consent"],
    "job required fields"
)
checks.append("form:job-application")

status, body = request("GET", "/api/v2/frontend/analytics")
expect_status(status, 200, "analytics")
expect("gaMeasurementId" in body["analytics"], "analytics GA key exists")
checks.append("analytics")

status, body = request("GET", "/api/v2/frontend/pages/missing")
expect_status(status, 404, "missing page")
expect_eq(body["error"]["code"], "not_found", "missing page error")
checks.append("missing-page")

status, body = request("POST", "/api/v2/frontend/forms/contact-us/submit", {
    "email": "api-test@example.com"
})
expect_status(status, 422, "invalid contact submit")
expect_eq(body["error"]["code"], "validation_failed", "validation error")
checks.append("invalid-contact-submit")

status, body = request("POST", "/api/v2/frontend/forms/contact-us/submit", {
    "full_name": "API Test Contact",
    "email": "api-test-contact@example.com",
    "phone": "+91 9000000000",
    "service_needed": "website_software_development",
    "message": "Automated API test contact submission."
})
expect_status(status, 201, "valid contact submit")
expect_eq(body["status"], "ok", "contact submit status")
expect(body["submissionId"] > 0, "contact submission id")
checks.append("valid-contact-submit")

status, body = request("POST", "/api/v2/frontend/forms/job-application/submit", {
    "full_name": "API Test Applicant",
    "email": "api-test-applicant@example.com",
    "phone": "+91 9000000001",
    "job_role": "customer_support_executive",
    "current_location": "Sawantwadi",
    "experience_years": "1",
    "resume_url": "https://example.com/resume.pdf",
    "message": "Automated API test job application.",
    "consent": "1"
})
expect_status(status, 201, "valid job submit")
expect_eq(body["status"], "ok", "job submit status")
expect(body["submissionId"] > 0, "job submission id")
checks.append("valid-job-submit")

print("Generic frontend API battle test passed.")
for check in checks:
    print(f"- {check}")
