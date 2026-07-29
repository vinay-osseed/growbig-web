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

def must(condition, message):
    if not condition:
        raise AssertionError(message)

def status(path, expected=200):
    code, body = request("GET", path)
    must(code == expected, f"{path}: expected {expected}, got {code}")
    return body

bootstrap = status("/api/v2/frontend/bootstrap")
must(bootstrap["mode"] == "single_backend", "single backend mode missing")
must(bootstrap["template"]["reusable"] is True, "template reusable flag missing")
must(len(bootstrap["navigation"]["main"]) == 5, "main nav count mismatch")
must("footer" in bootstrap, "footer missing")
must("media" in bootstrap, "media missing")
must("forms" in bootstrap, "forms missing")
must("analytics" in bootstrap, "analytics missing")

for key, media in bootstrap["media"].items():
    if media.get("status") == "placeholder":
        raise AssertionError(f"media {key} is still placeholder")
    must(media.get("status") in ["drupal_media", "external"], f"media {key} invalid status")

for slug in ["home", "about", "careers", "contact"]:
    page = status(f"/api/v2/frontend/pages/{slug}")
    must(page["page"]["sections"], f"{slug} sections missing")
    must(page["page"]["seo"]["title"], f"{slug} seo title missing")

for source, expected_count in {
    "services": 6,
    "partners": 2,
    "team": 5,
    "jobs": 1,
}.items():
    body = status(f"/api/v2/frontend/content/{source}")
    must(body["count"] == expected_count, f"{source} count mismatch")

contact_form = status("/api/v2/frontend/forms/contact-us")["form"]
job_form = status("/api/v2/frontend/forms/job-application")["form"]
must(contact_form["submitApi"] == "/api/v2/frontend/forms/contact-us/submit", "contact submitApi mismatch")
must(job_form["submitApi"] == "/api/v2/frontend/forms/job-application/submit", "job submitApi mismatch")

for form in [contact_form, job_form]:
    must("notification" not in form, "private notification config should not leak in public form response")

code, invalid = request("POST", "/api/v2/frontend/forms/contact-us/submit", {
    "email": "qa@example.com"
})
must(code == 422, "invalid contact validation did not fail")

code, contact_submit = request("POST", "/api/v2/frontend/forms/contact-us/submit", {
    "full_name": "Final QA Contact",
    "email": "final-qa-contact@example.com",
    "phone": "+91 9000000010",
    "service_needed": "website_software_development",
    "message": "Final backend QA contact submission."
})
must(code == 201, f"contact submit failed: {contact_submit}")
must("notification" not in contact_submit, "custom notification response should not be returned")

code, job_submit = request("POST", "/api/v2/frontend/forms/job-application/submit", {
    "full_name": "Final QA Applicant",
    "email": "final-qa-applicant@example.com",
    "phone": "+91 9000000011",
    "job_role": "customer_support_executive",
    "current_location": "Sawantwadi",
    "experience_years": "1",
    "resume_url": "https://example.com/final-qa-resume.pdf",
    "message": "Final backend QA job application.",
    "consent": "1"
})
must(code == 201, f"job submit failed: {job_submit}")
must("notification" not in job_submit, "custom notification response should not be returned")

analytics = status("/api/v2/frontend/analytics")["analytics"]
must(analytics["enabled"] is True, "analytics not enabled")
must(analytics["gaMeasurementId"], "GA measurement ID missing")

print("Final frontend backend QA passed.")
print("- bootstrap")
print("- media imported or external")
print("- pages")
print("- content sources")
print("- public form contracts")
print("- validation")
print("- contact submission")
print("- job application submission")
print("- Webform-backed submissions")
print("- analytics config")
