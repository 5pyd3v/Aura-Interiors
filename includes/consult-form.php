<?php
/**
 * Reusable consultation / quote form partial.
 * Included on the homepage and contact page — posts via AJAX to ajax/submit-inquiry.php.
 */
$cities = array_map('trim', explode(',', setting('cities', 'Islamabad,Rawalpindi,Lahore,Karachi')));
?>
<div class="form-msg" role="status" aria-live="polite"></div>
<form class="js-consult-form" data-source="<?= e(basename($_SERVER['SCRIPT_NAME'] ?? 'form')) ?>" novalidate>
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="form-group">
      <label for="full_name">Full Name <span class="req">*</span></label>
      <input class="form-control" type="text" id="full_name" name="full_name" placeholder="Your name" required>
      <span class="field-error" data-error-for="full_name"></span>
    </div>
    <div class="form-group">
      <label for="phone">Phone Number <span class="req">*</span></label>
      <input class="form-control" type="tel" id="phone" name="phone" placeholder="03XX XXXXXXX" required>
      <span class="field-error" data-error-for="phone"></span>
    </div>
    <div class="form-group">
      <label for="whatsapp">WhatsApp Number</label>
      <input class="form-control" type="tel" id="whatsapp" name="whatsapp" placeholder="Same as phone, if different">
    </div>
    <div class="form-group">
      <label for="email">Email</label>
      <input class="form-control" type="email" id="email" name="email" placeholder="you@example.com">
      <span class="field-error" data-error-for="email"></span>
    </div>
    <div class="form-group">
      <label for="city">City</label>
      <select class="form-control" id="city" name="city">
        <option value="">Select city</option>
        <?php foreach ($cities as $c): ?>
          <option value="<?= e($c) ?>"><?= e($c) ?></option>
        <?php endforeach; ?>
        <option value="Other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label for="project_type">Project Type</label>
      <select class="form-control" id="project_type" name="project_type">
        <option value="">Select type</option>
        <?php foreach (['House','Apartment','Office','Restaurant','Cafe','Commercial','Bedroom','Kitchen','Other'] as $t): ?>
          <option value="<?= e($t) ?>"><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="property_type">Property Type</label>
      <select class="form-control" id="property_type" name="property_type">
        <option value="">Select property</option>
        <option>Owned</option>
        <option>Rented</option>
        <option>Under Construction</option>
        <option>Commercial Lease</option>
      </select>
    </div>
    <div class="form-group">
      <label for="area">Approximate Area (sq. ft)</label>
      <input class="form-control" type="text" id="area" name="area" placeholder="e.g. 2500 sq. ft">
    </div>
    <div class="form-group">
      <label for="budget">Estimated Budget</label>
      <select class="form-control" id="budget" name="budget">
        <option value="">Select budget</option>
        <?php foreach (['Under 5 Lakh','5–10 Lakh','10–25 Lakh','25–50 Lakh','50 Lakh+','Not Sure'] as $b): ?>
          <option value="<?= e($b) ?>"><?= e($b) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label for="start_date">Preferred Start Date</label>
      <input class="form-control" type="date" id="start_date" name="start_date">
    </div>
    <div class="form-group full">
      <label for="message">Message</label>
      <textarea class="form-control" id="message" name="message" placeholder="Tell us a little about your space and vision..."></textarea>
    </div>
  </div>
  <button type="submit" class="btn btn--gradient btn--block">Request Free Consultation</button>
  <p class="form-note">By submitting, you agree to be contacted by our design team via phone, email or WhatsApp regarding your inquiry.</p>
</form>
