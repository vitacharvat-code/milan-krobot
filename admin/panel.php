<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: index.php');
    exit;
}
$galleryFile = __DIR__ . '/../data/gallery.json';
$gallery = json_decode(file_get_contents($galleryFile), true);
$photos = $gallery['photos'] ?? [];
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Milan Krobot – Admin</title>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Inter', system-ui, sans-serif; background: #f4f4f4; color: #1a1a1a; }

    /* Header */
    .header {
      background: #0f0f0f;
      padding: 0 32px;
      height: 60px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
    }
    .header-logo { font-family: Georgia, serif; font-size: 1.1rem; font-weight: 700; color: #f0ece4; }
    .header-logo span { color: #c8a228; }
    .header-nav { display: flex; align-items: center; gap: 20px; }
    .header-nav a {
      color: #888;
      text-decoration: none;
      font-size: 0.85rem;
      transition: color 0.2s;
    }
    .header-nav a:hover { color: #f0ece4; }
    .btn-site {
      background: #c8a228;
      color: #0f0f0f !important;
      padding: 7px 16px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.82rem !important;
    }

    /* Layout */
    .main { max-width: 1100px; margin: 0 auto; padding: 32px 24px; }
    .page-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 28px; color: #111; }

    /* Cards */
    .card {
      background: #fff;
      border-radius: 14px;
      padding: 28px 28px;
      margin-bottom: 28px;
      box-shadow: 0 1px 4px rgba(0,0,0,0.07);
    }
    .card-title {
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 20px;
      padding-bottom: 14px;
      border-bottom: 1px solid #eee;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    .card-title span { font-size: 1.2rem; }

    /* Upload zone */
    .upload-zone {
      border: 2px dashed #ddd;
      border-radius: 12px;
      padding: 36px;
      text-align: center;
      cursor: pointer;
      transition: border-color 0.2s, background 0.2s;
      margin-bottom: 16px;
    }
    .upload-zone:hover, .upload-zone.drag-over {
      border-color: #c8a228;
      background: #fffbf0;
    }
    .upload-icon { font-size: 2.5rem; margin-bottom: 10px; }
    .upload-text { color: #666; font-size: 0.9rem; }
    .upload-text strong { color: #c8a228; }
    #fileInput { display: none; }
    .upload-btn {
      background: #c8a228;
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 11px 24px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .upload-btn:hover { background: #b8921e; }
    .upload-btn:disabled { background: #ccc; cursor: not-allowed; }
    #uploadStatus { margin-top: 12px; font-size: 0.88rem; color: #555; }

    /* Photos grid */
    .photos-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 14px;
      margin-top: 20px;
    }
    .photo-item {
      position: relative;
      border-radius: 10px;
      overflow: hidden;
      background: #eee;
      aspect-ratio: 3/4;
    }
    .photo-item.wide { aspect-ratio: 16/9; grid-column: span 2; }
    .photo-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
    .photo-delete {
      position: absolute;
      top: 8px;
      right: 8px;
      background: rgba(0,0,0,0.7);
      color: #fff;
      border: none;
      border-radius: 6px;
      width: 30px;
      height: 30px;
      font-size: 1rem;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.2s;
    }
    .photo-item:hover .photo-delete { opacity: 1; }
    .photo-order {
      position: absolute;
      bottom: 6px;
      left: 6px;
      background: rgba(0,0,0,0.6);
      color: #fff;
      font-size: 0.7rem;
      padding: 2px 7px;
      border-radius: 4px;
    }

    /* Text editing */
    .form-group { margin-bottom: 20px; }
    .form-label {
      display: block;
      font-size: 0.8rem;
      font-weight: 600;
      color: #555;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      margin-bottom: 8px;
    }
    textarea {
      width: 100%;
      background: #f9f9f9;
      border: 1px solid #e0e0e0;
      border-radius: 9px;
      padding: 12px 14px;
      font-size: 0.9rem;
      font-family: inherit;
      color: #1a1a1a;
      resize: vertical;
      outline: none;
      transition: border-color 0.2s;
      min-height: 100px;
    }
    textarea:focus { border-color: #c8a228; }
    .save-btn {
      background: #1a1a1a;
      color: #fff;
      border: none;
      border-radius: 9px;
      padding: 11px 24px;
      font-size: 0.9rem;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s;
    }
    .save-btn:hover { background: #333; }

    /* Notice */
    .notice {
      background: #f0fdf4;
      border: 1px solid #86efac;
      color: #166534;
      border-radius: 9px;
      padding: 12px 16px;
      font-size: 0.88rem;
      margin-bottom: 24px;
      display: none;
    }
    .notice.show { display: block; }
    .notice-error {
      background: #fef2f2;
      border-color: #fca5a5;
      color: #991b1b;
    }

    /* Upload progress */
    .upload-preview {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 14px;
    }
    .preview-thumb {
      width: 80px;
      height: 80px;
      border-radius: 8px;
      object-fit: cover;
      border: 2px solid #c8a228;
    }
  </style>
</head>
<body>

<header class="header">
  <div class="header-logo">Milan <span>Krobot</span></div>
  <nav class="header-nav">
    <a href="../index.php" target="_blank" class="btn-site">↗ Zobrazit web</a>
    <a href="logout.php">Odhlásit</a>
  </nav>
</header>

<main class="main">
  <h1 class="page-title">Správa webu</h1>

  <div id="notice" class="notice <?= $success ? 'show' : '' ?>">
    <?= $success === 'upload' ? '✅ Fotografie byla úspěšně nahrána.' : ($success === 'delete' ? '✅ Fotografie byla smazána.' : ($success === 'texts' ? '✅ Texty byly uloženy.' : '')) ?>
  </div>

  <!-- Upload nové fotky -->
  <div class="card">
    <div class="card-title"><span>📸</span> Nahrát novou fotografii</div>
    <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
      <div class="upload-zone" id="uploadZone">
        <div class="upload-icon">🖼️</div>
        <div class="upload-text">
          Přetáhněte fotografii sem nebo <strong>klikněte pro výběr</strong>
        </div>
        <div class="upload-text" style="margin-top:6px; font-size:0.8rem;">JPG, PNG, WebP · max. 10 MB</div>
      </div>
      <input type="file" id="fileInput" name="photo" accept="image/jpeg,image/png,image/webp" required>
      <div class="upload-preview" id="uploadPreview"></div>
      <div id="uploadStatus"></div>
      <button type="submit" class="upload-btn" id="uploadBtn" disabled>Nahrát fotografii</button>
    </form>
  </div>

  <!-- Aktuální galerie -->
  <div class="card">
    <div class="card-title"><span>🗂️</span> Fotografie v galerii (<?= count($photos) ?>)</div>
    <p style="font-size:0.85rem; color:#888; margin-bottom:4px;">Kliknutím na 🗑️ fotografii odstraníte.</p>
    <div class="photos-grid">
      <?php foreach ($photos as $i => $photo): ?>
        <div class="photo-item <?= $photo['wide'] ? 'wide' : '' ?>">
          <img src="../assets/images/<?= htmlspecialchars($photo['filename']) ?>"
               alt="<?= htmlspecialchars($photo['alt']) ?>"
               loading="lazy">
          <form action="delete.php" method="POST" style="display:inline"
                onsubmit="return confirm('Smazat tuto fotografii?')">
            <input type="hidden" name="filename" value="<?= htmlspecialchars($photo['filename']) ?>">
            <button type="submit" class="photo-delete" title="Smazat">🗑️</button>
          </form>
          <div class="photo-order"><?= $i + 1 ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Úprava textů -->
  <div class="card">
    <div class="card-title"><span>✏️</span> Texty – sekce „O mně"</div>
    <?php
      $textsFile = __DIR__ . '/../data/texts.json';
      $texts = file_exists($textsFile) ? json_decode(file_get_contents($textsFile), true) : [];
    ?>
    <form action="save-texts.php" method="POST">
      <div class="form-group">
        <label class="form-label">Odstavec 1</label>
        <textarea name="bio_1" rows="4"><?= htmlspecialchars($texts['bio_1'] ?? '') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Odstavec 2</label>
        <textarea name="bio_2" rows="4"><?= htmlspecialchars($texts['bio_2'] ?? '') ?></textarea>
      </div>
      <button type="submit" class="save-btn">Uložit texty</button>
    </form>
  </div>

</main>

<script>
const zone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const uploadBtn = document.getElementById('uploadBtn');
const preview = document.getElementById('uploadPreview');

zone.addEventListener('click', () => fileInput.click());
zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
  e.preventDefault();
  zone.classList.remove('drag-over');
  const dt = new DataTransfer();
  Array.from(e.dataTransfer.files).forEach(f => dt.items.add(f));
  fileInput.files = dt.files;
  handleFileSelect();
});
fileInput.addEventListener('change', handleFileSelect);

function handleFileSelect() {
  const file = fileInput.files[0];
  if (!file) return;
  preview.innerHTML = '';
  const img = document.createElement('img');
  img.className = 'preview-thumb';
  img.src = URL.createObjectURL(file);
  preview.appendChild(img);
  uploadBtn.disabled = false;
  uploadBtn.textContent = `Nahrát: ${file.name}`;
}

// Auto-hide notice
setTimeout(() => {
  const n = document.getElementById('notice');
  if (n) n.classList.remove('show');
}, 4000);
</script>
</body>
</html>
