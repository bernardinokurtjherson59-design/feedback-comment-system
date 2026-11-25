<?php /* Shared feedback form (user side). Expects $f (values), $errors, $isEdit */ ?>
<?php foreach ($errors as $er): ?>
  <div class="alert alert-danger py-2"><?= e($er) ?></div>
<?php endforeach; ?>
<form method="post" class="card card-body shadow-sm">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select name="category" class="form-select">
        <?php foreach (CATEGORIES as $c): ?>
          <option <?= $f['category'] === $c ? 'selected' : '' ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label">Rating</label>
      <select name="rating" class="form-select">
        <?php for ($i = 5; $i >= 1; $i--): ?>
          <option value="<?= $i ?>" <?= (int)$f['rating'] === $i ? 'selected' : '' ?>><?= stars($i) ?></option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">Message</label>
      <textarea name="message" rows="5" class="form-control" required><?= e($f['message']) ?></textarea>
    </div>
  </div>
  <div class="mt-3 d-flex gap-2">
    <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Update' : 'Submit' ?></button>
    <a href="index.php" class="btn btn-outline-secondary">Cancel</a>
  </div>
</form>
