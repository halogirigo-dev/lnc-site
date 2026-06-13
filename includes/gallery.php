<?php // includes/gallery.php — Photo gallery strip ?>
<section class="section section--dark2" id="gallery">
  <div class="container">
    <div class="flex-between mb-48">
      <div>
        <span class="eyebrow" style="color:rgba(255,255,255,.4);">Our World</span>
        <h2 class="section-title section-title--light">Captured Moments</h2>
      </div>
      <a href="#inquiry" class="btn btn--outline-light">Plan Your Journey</a>
    </div>
    <div class="gallery-grid">
      <?php
      $gallery_photos = [
        ['file'=>'BUCHSTEINERPHOTOGRAPHY-21.jpg', 'alt'=>'Lombok Nature & Culture',  'h'=>320],
        ['file'=>'BUCHSTEINERPHOTOGRAPHY-32.JPG', 'alt'=>'Gili Islands Experience',  'h'=>220],
        ['file'=>'BUCHSTEINERPHOTOGRAPHY-35.JPG', 'alt'=>'Sasak Village Life',        'h'=>220],
        ['file'=>'BUCHSTEINERPHOTOGRAPHY-42.jpg', 'alt'=>'Island Adventure',          'h'=>320, 'span'=>2],
        ['file'=>'BUCHSTEINERPHOTOGRAPHY-44.JPG', 'alt'=>'Lombok Landscapes',         'h'=>320],
      ];
      foreach ($gallery_photos as $p):
        $spanStyle = !empty($p['span']) ? 'grid-column:span '.$p['span'].';' : '';
        $webp = preg_replace('/\.(jpe?g|JPE?G|png|PNG)$/', '.webp', $p['file']);
      ?>
      <div class="gallery-item" style="height:<?= $p['h'] ?>px;<?= $spanStyle ?>">
        <picture>
          <source srcset="<?= UPLOADS_URL ?>/gallery/<?= htmlspecialchars($webp) ?>" type="image/webp">
          <img
            src="<?= UPLOADS_URL ?>/gallery/<?= htmlspecialchars($p['file']) ?>"
            alt="<?= htmlspecialchars($p['alt']) ?>"
            loading="lazy"
          >
        </picture>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>