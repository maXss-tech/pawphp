<?php
require 'config.php';
$title = 'Home'; $page = 'home';
include 'includes/header.php';
?>
<main>
  <section id="home">
    <div class="band">
      <div class="hero">
        <div>
          <h1>Makes Every<br>Journey <em>Paw-sible.</em></h1>
          <a class="cta" href="booking.php" style="display:inline-block">Book Now</a>
        </div>
        <img class="pets" src="assets/pets.png" alt="A golden retriever and an orange tabby cat">
        <div class="side">
          <a href="pet-profile.php"><button type="button" tabindex="-1"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#3a2a1c" stroke-width="1.8"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8" cy="11" r="2.5"/><path d="M14 10h5M14 14h4"/></svg><span>Create Pet Profile</span><small>➔</small></button></a>
          <a href="emergency.php"><button type="button" tabindex="-1"><svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#3a2a1c" stroke-width="1.8"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V4h6v3M12 11v6M9 14h6"/></svg><span>Emergency Assistance</span><small>➔</small></button></a>
        </div>
      </div>
    </div>
    <div class="feats">
      <div><svg viewBox="0 0 24 24" fill="none" stroke="#3b4f2e" stroke-width="1.6"><path d="M12 3l8 3v6c0 5-4 8-8 9-4-1-8-4-8-9V6z"/><circle cx="12" cy="13" r="2.5"/></svg>Trusted</div>
      <div><svg viewBox="0 0 24 24" fill="none" stroke="#3b4f2e" stroke-width="1.6"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M9 16l2 2 4-4"/></svg>Easy &amp; Hassle Free</div>
      <div><svg viewBox="0 0 24 24" fill="none" stroke="#3b4f2e" stroke-width="1.6"><path d="M12 22c-5-6-7-9-7-13a7 7 0 0114 0c0 4-2 7-7 13z"/><circle cx="12" cy="9" r="2.5"/></svg>Accessibility</div>
      <div><svg viewBox="0 0 24 24" fill="none" stroke="#3b4f2e" stroke-width="1.6"><path d="M4 14v-3a8 8 0 0116 0v3M4 14h3v5H4zM17 14h3v5h-3zM20 19c0 2-3 2-6 2"/></svg>Customer Service</div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
