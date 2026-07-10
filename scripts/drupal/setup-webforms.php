<?php

declare(strict_types=1);

use Drupal\webform\Entity\Webform;

if (!\Drupal::moduleHandler()->moduleExists('webform')) {
  throw new RuntimeException('Webform module is not enabled.');
}

function ensure_webform(string $id, string $title, string $description, string $elements): void {
  $webform = Webform::load($id);

  if (!$webform) {
    $webform = Webform::create([
      'id' => $id,
      'title' => $title,
      'description' => $description,
      'status' => 'open',
      'elements' => $elements,
    ]);

    $webform->save();
    print "Created webform: {$id}\n";
    return;
  }

  $webform->set('title', $title);
  $webform->set('description', $description);
  $webform->set('elements', $elements);
  $webform->setStatus(TRUE);
  $webform->save();

  print "Updated webform: {$id}\n";
}

$job_application_elements = <<<'YAML'
job_details:
  '#type': fieldset
  '#title': 'Job Details'
job_key:
  '#type': hidden
  '#title': 'Job Key'
job_title:
  '#type': textfield
  '#title': 'Job Title'
  '#readonly': true
  '#required': true
applicant_details:
  '#type': fieldset
  '#title': 'Applicant Details'
name:
  '#type': textfield
  '#title': 'Full Name'
  '#required': true
email:
  '#type': email
  '#title': 'Email'
  '#required': true
phone:
  '#type': tel
  '#title': 'Phone'
  '#required': true
current_location:
  '#type': textfield
  '#title': 'Current Location'
experience_years:
  '#type': number
  '#title': 'Experience in Years'
  '#min': 0
  '#step': 0.5
current_company:
  '#type': textfield
  '#title': 'Current Company'
resume_upload:
  '#type': managed_file
  '#title': 'Resume'
  '#required': false
  '#file_extensions': 'pdf doc docx'
portfolio_url:
  '#type': url
  '#title': 'Portfolio URL'
linkedin_url:
  '#type': url
  '#title': 'LinkedIn URL'
message:
  '#type': textarea
  '#title': 'Message'
consent:
  '#type': checkbox
  '#title': 'I consent to being contacted about this job application.'
  '#required': true
actions:
  '#type': webform_actions
  '#title': 'Submit button(s)'
  '#submit__label': 'Submit Application'
YAML;

$contact_us_elements = <<<'YAML'
name:
  '#type': textfield
  '#title': 'Full Name'
  '#required': true
email:
  '#type': email
  '#title': 'Email'
  '#required': true
phone:
  '#type': tel
  '#title': 'Phone'
company:
  '#type': textfield
  '#title': 'Company'
subject:
  '#type': textfield
  '#title': 'Subject'
  '#required': true
service_interest:
  '#type': select
  '#title': 'Service Interest'
  '#empty_option': '- Select -'
  '#options':
    website_development: 'Website Development'
    mobile_app_development: 'Mobile App Development'
    custom_software: 'Custom Software'
    cloud_solutions: 'Cloud Solutions'
    digital_marketing: 'Digital Marketing'
    branding: 'Branding'
    other: 'Other'
preferred_contact_method:
  '#type': radios
  '#title': 'Preferred Contact Method'
  '#options':
    email: 'Email'
    phone: 'Phone'
    whatsapp: 'WhatsApp'
  '#default_value': email
budget_range:
  '#type': select
  '#title': 'Budget Range'
  '#empty_option': '- Optional -'
  '#options':
    under_50000: 'Under ₹50,000'
    50000_100000: '₹50,000 - ₹1,00,000'
    100000_250000: '₹1,00,000 - ₹2,50,000'
    250000_500000: '₹2,50,000 - ₹5,00,000'
    above_500000: 'Above ₹5,00,000'
message:
  '#type': textarea
  '#title': 'Message'
  '#required': true
consent:
  '#type': checkbox
  '#title': 'I consent to being contacted about this enquiry.'
  '#required': true
actions:
  '#type': webform_actions
  '#title': 'Submit button(s)'
  '#submit__label': 'Send Message'
YAML;

ensure_webform(
  'job_application',
  'Job Application',
  'Candidate application form for open job positions.',
  $job_application_elements
);

ensure_webform(
  'contact_us',
  'Contact Us',
  'General contact and project enquiry form.',
  $contact_us_elements
);

print "Webform setup complete.\n";
