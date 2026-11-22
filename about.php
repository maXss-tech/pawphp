<?php
require 'config.php';
$title = 'About Us'; $page = 'about';
include 'includes/header.php';
?>
<main>
  <section>
    <div class="band" style="min-height:520px">
      <div class="hero" style="min-height:260px">
        <div style="max-width:560px">
          <h2 class="rv" style="font-size:44px">About PawBnB</h2>
          <p style="font-size:17px;line-height:1.7">PawBnB helps pet owners find stays that welcome the whole family. Tell us where you’re going and who’s coming along, and we show accommodations that accept your pet.</p>
        </div>
        <img class="pets" src="assets/pets.png" alt="A golden retriever and an orange tabby cat">
      </div>
    </div>
    <div class="wrap" style="padding-top:30px">
      <h3 style="color:var(--g);font-size:24px">What we stand for</h3>
      <div class="plist" style="margin:14px 0 30px">
        <div class="pc"><b>Trusted</b>Every stay is checked against your pet’s type, size and weight before you confirm.</div>
        <div class="pc"><b>Easy &amp; Hassle Free</b>Book in three steps: enter your details, review them, and confirm.</div>
        <div class="pc"><b>Accessibility</b>Search by destination and budget, with places that fit pets of every size.</div>
        <div class="pc"><b>Customer Service</b>Chat with our team, or use Emergency Assistance if your pet needs urgent help.</div>
      </div>
    </div>
  </section>
</main>
<?php include 'includes/footer.php'; ?>
