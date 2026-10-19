<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Submit an article</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="page">
    <p class="eyebrow">Write for NSBM NewsDesk</p>
    <h1>Submit an article</h1>
    <p class="lede">An admin reviews every submission before it is published.</p>

    <form method="post" action="submit_article.php" enctype="multipart/form-data">

      <div class="left">
        <label for="title">Title</label>
        <input type="text" id="title" name="title" class="title" placeholder="Article title" required>

        <label for="summary">Summary (one or two sentences, shown on the card)</label>
        <textarea id="summary" name="summary" class="summary" placeholder="A short summary of the story" required></textarea>

        <label for="body">Body</label>
        <textarea id="body" name="body" class="body" placeholder="Write the article here" required></textarea>
      </div>

      <div class="right">
        <label for="category">Category</label>
        <select id="category" name="category" required>
          <option value="" disabled selected>Choose a category</option>
          <option value="academic">Academic</option>
          <option value="events">Events</option>
          <option value="sports">Sports</option>
          <option value="general">General</option>
          <option value="campus-news">Campus News</option>
        </select>

        <label for="image">Image (optional)</label>
        <input type="file" id="image" name="image" class="upload" accept="image/png, image/jpeg">
        <small>JPG or PNG, max 2 MB</small>

        <button type="submit" name="action" value="submit" class="primary">Submit for approval</button>
        <button type="submit" name="action" value="draft" class="secondary" formnovalidate>Save draft</button>

        <div class="how">
          <h2>How it works</h2>
          <p><span>1</span> You submit the article.</p>
          <p><span>2</span> An admin reads it.</p>
          <p><span>3</span> It is published, or sent back with feedback.</p>
        </div>
      </div>

    </form>
  </div>
</body>
</html>