<?php
require_once __DIR__ . '/includes/auth.php';

$categories = db()->query('SELECT * FROM blog_categories ORDER BY name')->fetchAll();

$id = (int) ($_GET['id'] ?? 0);
$post = [
    'title' => '', 'slug' => '', 'category_id' => '', 'featured_image' => '', 'excerpt' => '', 'content' => '',
    'author_name' => $currentAdmin['name'], 'seo_title' => '', 'seo_description' => '', 'is_published' => 1,
    'published_at' => date('Y-m-d'),
];
$isEdit = false;

if ($id) {
    $stmt = db()->prepare('SELECT * FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) {
        $post = $found;
        $post['published_at'] = $post['published_at'] ? date('Y-m-d', strtotime($post['published_at'])) : date('Y-m-d');
        $isEdit = true;
    }
}

$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    admin_verify_csrf('blog-form.php' . ($id ? '?id=' . $id : ''));

    $title = trim((string) ($_POST['title'] ?? ''));
    $slugInput = trim((string) ($_POST['slug'] ?? ''));
    $categoryId = (int) ($_POST['category_id'] ?? 0) ?: null;
    $excerpt = trim((string) ($_POST['excerpt'] ?? ''));
    $content = (string) ($_POST['content'] ?? '');
    $authorName = trim((string) ($_POST['author_name'] ?? ''));
    $seoTitle = trim((string) ($_POST['seo_title'] ?? ''));
    $seoDescription = trim((string) ($_POST['seo_description'] ?? ''));
    $publishedAt = trim((string) ($_POST['published_at'] ?? '')) ?: date('Y-m-d');
    $isPublished = isset($_POST['is_published']) ? 1 : 0;

    if ($title === '') $errors[] = 'Title is required.';
    if ($content === '') $errors[] = 'Content is required.';

    $featuredImage = $post['featured_image'];
    if (!empty($_FILES['featured_image']['name'])) {
        $up = handle_image_upload($_FILES['featured_image'], 'blog', $uploadError);
        if ($up) { if ($isEdit && $featuredImage) delete_upload($featuredImage); $featuredImage = $up['path']; }
        else { $errors[] = $uploadError; }
    }

    if (!$errors) {
        $slug = unique_slug('blog_posts', slugify($slugInput !== '' ? $slugInput : $title), $isEdit ? $id : null);
        if ($isEdit) {
            $stmt = db()->prepare('UPDATE blog_posts SET title=?, slug=?, category_id=?, featured_image=?, excerpt=?, content=?, author_name=?, seo_title=?, seo_description=?, is_published=?, published_at=? WHERE id=?');
            $stmt->execute([$title, $slug, $categoryId, $featuredImage, $excerpt, $content, $authorName, $seoTitle, $seoDescription, $isPublished, $publishedAt . ' 10:00:00', $id]);
            flash_set('success', 'Article updated.');
        } else {
            $stmt = db()->prepare('INSERT INTO blog_posts (title, slug, category_id, featured_image, excerpt, content, author_name, seo_title, seo_description, is_published, published_at) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$title, $slug, $categoryId, $featuredImage, $excerpt, $content, $authorName, $seoTitle, $seoDescription, $isPublished, $publishedAt . ' 10:00:00']);
            flash_set('success', 'Article published.');
        }
        redirect('blog.php');
    }
    $post = array_merge($post, compact('title', 'excerpt', 'content', 'authorName', 'seoTitle', 'seoDescription', 'isPublished', 'publishedAt'));
    $post['category_id'] = $categoryId;
    $post['featured_image'] = $featuredImage;
    $post['slug'] = $slugInput;
}

$adminPageTitle = $isEdit ? 'Edit Article' : 'Write Article';
$adminActive = 'blog';
require __DIR__ . '/includes/admin-header.php';
?>

<a href="blog.php" style="display:inline-flex;align-items:center;gap:8px;font-size:.88rem;color:var(--ink-soft);margin-bottom:18px"><i class="fa-solid fa-arrow-left"></i> Back to Blog</a>

<div class="panel">
  <div class="panel__body">
    <?php foreach ($errors as $err): ?><div class="alert alert--error"><?= e($err) ?></div><?php endforeach; ?>
    <form method="post" enctype="multipart/form-data" class="admin-form">
      <?= csrf_field() ?>
      <div class="form-grid">
        <div class="form-group full">
          <label for="title">Title <span style="color:var(--accent-2)">*</span></label>
          <input class="form-control" type="text" id="title" name="title" data-slug-source="#slug" value="<?= e($post['title']) ?>" required>
        </div>
        <div class="form-group">
          <label for="slug">URL Slug</label>
          <input class="form-control" type="text" id="slug" name="slug" value="<?= e($post['slug']) ?>" placeholder="auto-generated from title">
        </div>
        <div class="form-group">
          <label for="category_id">Category</label>
          <select class="form-control" id="category_id" name="category_id">
            <option value="">Uncategorized</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= (int) $post['category_id'] === (int) $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="author_name">Author</label>
          <input class="form-control" type="text" id="author_name" name="author_name" value="<?= e($post['author_name']) ?>">
        </div>
        <div class="form-group">
          <label for="published_at">Publish Date</label>
          <input class="form-control" type="date" id="published_at" name="published_at" value="<?= e($post['published_at']) ?>">
        </div>
        <div class="form-group full">
          <label for="excerpt">Excerpt</label>
          <textarea class="form-control" id="excerpt" name="excerpt" rows="2"><?= e($post['excerpt']) ?></textarea>
        </div>
        <div class="form-group full">
          <label for="content">Content (HTML supported)</label>
          <textarea class="form-control" id="content" name="content" rows="12" required><?= e($post['content']) ?></textarea>
          <p class="form-hint">Use &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;&lt;li&gt; tags to format your article.</p>
        </div>
        <div class="form-group full">
          <label>Featured Image</label>
          <div class="image-upload-box" onclick="document.getElementById('featured_image').click()">
            <img src="<?= e(img($post['featured_image'], 'assets/images/placeholder.jpg')) ?>" alt="">
            <p>Click to upload / replace featured image</p>
          </div>
          <input type="file" id="featured_image" name="featured_image" accept="image/jpeg,image/png,image/webp" class="js-image-input" style="display:none">
        </div>
        <div class="form-group">
          <label for="seo_title">SEO Title</label>
          <input class="form-control" type="text" id="seo_title" name="seo_title" value="<?= e($post['seo_title']) ?>" placeholder="Defaults to article title">
        </div>
        <div class="form-group">
          <label>&nbsp;</label>
          <label style="display:flex;align-items:center;gap:10px;font-weight:500">
            <input type="checkbox" name="is_published" value="1" <?= $post['is_published'] ? 'checked' : '' ?>> Published (visible on website)
          </label>
        </div>
        <div class="form-group full">
          <label for="seo_description">SEO Description</label>
          <textarea class="form-control" id="seo_description" name="seo_description" rows="2" placeholder="Defaults to excerpt"><?= e($post['seo_description']) ?></textarea>
        </div>
      </div>
      <button type="submit" class="btn--admin"><i class="fa-solid fa-check"></i> <?= $isEdit ? 'Update Article' : 'Publish Article' ?></button>
    </form>
  </div>
</div>

<?php require __DIR__ . '/includes/admin-footer.php'; ?>
