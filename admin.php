<?php
session_start();

// Skydda admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$db = new SQLite3(__DIR__ . '/data.db');

// Säkerställ att tabellen existerar
$db->exec("CREATE TABLE IF NOT EXISTS articles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT,
    excerpt TEXT,
    content TEXT,
    category TEXT,
    image TEXT,
    date TEXT DEFAULT CURRENT_TIMESTAMP
)");

// Lista kategorier
$categories = [
    "Dagens läsning",
    "Veckans läsning",
    "Dagens inspiration",
    "Tematisk läsning",
    "Q&A",
    "Quotes",
    "Jihad",
    "Women in Islam",
    "Discovering God",
    "Paradise",
    "Blasphemy",
    "Islamic parenting",
    "Discovering Quran",
    "Peace",
    "Purpose of man",
    "Hijab in Islam",
    "Terrorism",
    "Salat",
    "Ramadan",
    "Zakat",
    "Hajj"
];

// ---- LÄGG TILL ----
if (isset($_POST['add'])) {
    $title   = $_POST['title'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $category= $_POST['category'] ?? '';

    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName   = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($_FILES['image']['name']));
        $targetFile = $targetDir . $fileName;
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $image = "uploads/" . $fileName;
        }
    }

    $stmt = $db->prepare("INSERT INTO articles (title, excerpt, content, category, image) 
                          VALUES (:title, :excerpt, :content, :category, :image)");
    $stmt->bindValue(':title',   $title,   SQLITE3_TEXT);
    $stmt->bindValue(':excerpt', $excerpt, SQLITE3_TEXT);
    $stmt->bindValue(':content', $content, SQLITE3_TEXT);
    $stmt->bindValue(':category',$category,SQLITE3_TEXT);
    $stmt->bindValue(':image',   $image,   SQLITE3_TEXT);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}

// ---- TA BORT ----
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $row = $db->querySingle("SELECT image FROM articles WHERE id=$id", true);
    if ($row && !empty($row['image']) && file_exists(__DIR__ . '/' . $row['image'])) {
        @unlink(__DIR__ . '/' . $row['image']);
    }

    $db->exec("DELETE FROM articles WHERE id=$id");
    header("Location: admin.php");
    exit;
}

// ---- UPPDATERA ----
if (isset($_POST['update'])) {
    $id      = (int)($_POST['id'] ?? 0);
    $title   = $_POST['title'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $content = $_POST['content'] ?? '';
    $category= $_POST['category'] ?? '';

    $image = $_POST['current_image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $fileName   = time() . "_" . preg_replace('/[^A-Za-z0-9_.-]/', '_', basename($_FILES['image']['name']));
        $targetFile = $targetDir . $fileName;
        if (is_uploaded_file($_FILES['image']['tmp_name'])) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
            $image = "uploads/" . $fileName;
        }
    }

    $stmt = $db->prepare("UPDATE articles 
                          SET title=:title, excerpt=:excerpt, content=:content, category=:category, image=:image 
                          WHERE id=:id");
    $stmt->bindValue(':title',    $title,    SQLITE3_TEXT);
    $stmt->bindValue(':excerpt',  $excerpt,  SQLITE3_TEXT);
    $stmt->bindValue(':content',  $content,  SQLITE3_TEXT);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $stmt->bindValue(':image',    $image,    SQLITE3_TEXT);
    $stmt->bindValue(':id',       $id,       SQLITE3_INTEGER);
    $stmt->execute();

    header("Location: admin.php");
    exit;
}
?>
<!doctype html>
<html lang="sv">
<head>
  <meta charset="utf-8">
  <title>Adminpanel</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script src="https://cdn.tiny.cloud/1/autczrpyzfk1nlrgo7n3f4g3buxhojq9wwl77qpj7h5tgoym/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
  <script>
    tinymce.init({
      selector: '#content, #excerpt',
      plugins: 'advlist autolink lists link image charmap preview anchor pagebreak code',
      toolbar_mode: 'floating',
      toolbar: 'undo redo | styleselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | code',
      height: 400,
      image_advtab: true,
      forced_root_block: false,  // Denna rad löser problemet med radbrytningar
      content_style: 'body { font-family: Arial, sans-serif; font-size: 16px; }'
    });
  </script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: #333;
      background-color: #f5f7fa;
      padding: 20px;
    }
    .container {
      max-width: 1200px;
      margin: 0 auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      margin-bottom: 30px;
      color: #2c3e50;
      border-bottom: 2px solid #eee;
      padding-bottom: 15px;
    }
    h2 {
      color: #3498db;
      margin: 25px 0 15px;
    }
    form {
      max-width: 900px;
      margin: auto;
      display: flex;
      flex-direction: column;
      gap: 1rem;
      background: #f9f9f9;
      padding: 25px;
      border-radius: 8px;
      border: 1px solid #ddd;
    }
    label {
      font-weight: 600;
      color: #2c3e50;
    }
    input[type="text"], 
    textarea, 
    select {
      padding: 12px;
      font-size: 16px;
      width: 100%;
      border: 1px solid #ddd;
      border-radius: 6px;
      transition: border 0.3s;
    }
    input[type="text"]:focus, 
    textarea:focus, 
    select:focus {
      border-color: #3498db;
      outline: none;
      box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
    }
    button {
      padding: 14px;
      font-size: 16px;
      width: 100%;
      border-radius: 6px;
      background: #2ecc71;
      color: white;
      border: none;
      cursor: pointer;
      font-weight: 600;
      transition: background 0.3s;
    }
    button:hover {
      background: #27ae60;
    }
    table {
      width: 100%;
      margin-top: 1rem;
      border-collapse: collapse;
      box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background-color: #3498db;
      color: white;
      font-weight: 600;
      cursor: pointer;
    }
    tr:nth-child(even) {
      background-color: #f8f9fa;
    }
    tr:hover {
      background-color: #f1f2f6;
    }
    a {
      color: #3498db;
      text-decoration: null;
      transition: color 0.3s;
    }
    a:hover {
      color: #2980b9;
      text-decoration: underline;
    }
    .btn {
      display: inline-block;
      padding: 8px 15px;
      background: #3498db;
      color: white;
      border-radius: 4px;
      margin: 0 5px;
    }
    .btn:hover {
      background: #2980b9;
      text-decoration: none;
    }
    .pagination {
      text-align: center;
      margin: 20px 0;
    }
    .action-links a {
      margin-right: 10px;
    }
    img {
      max-width: 100%;
      border-radius: 6px;
    }
    .tox-tinymce {
      border-radius: 6px !important;
    }
    .alert {
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
      border-radius: 4px;
    }
    .alert-success {
      color: #3c763d;
      background-color: #dff0d8;
      border-color: #d6e9c6;
    }
    .alert-error {
      color: #a94442;
      background-color: #f2dede;
      border-color: #ebccd1;
    }
  </style>
</head>
<body class="container">

<h1><i class="fas fa-cogs"></i> Adminpanel</h1>

<?php
// Visa meddelanden om det finns några
if (isset($_GET['message'])) {
    $message = $_GET['message'];
    $type = isset($_GET['type']) ? $_GET['type'] : 'success';
    echo '<div class="alert alert-'.$type.'">'.$message.'</div>';
}

// ===== REDIGERING =====
if (isset($_GET['edit'])):
    $editId = (int)$_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM articles WHERE id=:id");
    $stmt->bindValue(':id', $editId, SQLITE3_INTEGER);
    $res = $stmt->execute();
    $article = $res ? $res->fetchArray(SQLITE3_ASSOC) : null;

    if ($article):
?>
  <h2><i class="fas fa-edit"></i> Redigera artikel</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo (int)$article['id']; ?>">
    <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($article['image'] ?? ''); ?>">

    <label for="title"><i class="fas fa-heading"></i> Titel</label>
    <input type="text" id="title" name="title" 
           value="<?php echo htmlspecialchars($article['title']); ?>" required>

    <label for="excerpt"><i class="fas fa-align-left"></i> Sammanfattning</label>
    <textarea id="excerpt" name="excerpt" rows="4"><?php echo htmlspecialchars($article['excerpt']); ?></textarea>

    <label for="content"><i class="fas fa-file-alt"></i> Artikeltext</label>
    <textarea id="content" name="content" rows="12"><?php echo htmlspecialchars($article['content']); ?></textarea>

    <label for="category"><i class="fas fa-tag"></i> Kategori</label>
    <select id="category" name="category">
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo htmlspecialchars($cat); ?>" 
          <?php if(($article['category']??'')===$cat) echo "selected"; ?>>
          <?php echo htmlspecialchars($cat); ?>
        </option>
      <?php endforeach; ?>
    </select>

    <label for="image"><i class="fas fa-image"></i> Byt bild</label>
    <input type="file" id="image" name="image" accept="image/*">

    <?php if (!empty($article['image'])): ?>
      <p>Nuvarande bild:<br><img src="<?php echo htmlspecialchars($article['image']); ?>" style="max-width:240px;"></p>
    <?php endif; ?>

    <button type="submit" name="update">
      <i class="fas fa-save"></i> Uppdatera artikel
    </button>
  </form>

  <p><a href="admin.php"><i class="fas fa-arrow-left"></i> Tillbaka</a></p>

<?php
    else:
        echo "<p><i class='fas fa-exclamation-triangle'></i> Artikel hittades inte.</p><p><a href='admin.php'>Tillbaka</a></p>";
    endif;

// ===== STANDARD =====
else:
?>

<h2><i class="fas fa-plus"></i> Lägg till ny artikel</h2>
<form method="post" enctype="multipart/form-data">

  <label for="title"><i class="fas fa-heading"></i> Titel</label>
  <input type="text" id="title" name="title" required>

  <label for="excerpt"><i class="fas fa-align-left"></i> Sammanfattning</label>
  <textarea id="excerpt" name="excerpt" rows="4"></textarea>

  <label for="content"><i class="fas fa-file-alt"></i> Artikeltext</label>
  <textarea id="content" name="content" rows="12"></textarea>

  <label for="category"><i class="fas fa-tag"></i> Kategori</label>
  <select id="category" name="category">
    <?php foreach ($categories as $cat): ?>
      <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
    <?php endforeach; ?>
  </select>

  <label for="image"><i class="fas fa-image"></i> Ladda upp bild</label>
  <input type="file" id="image" name="image" accept="image/*">

  <button type="submit" name="add">
    <i class="fas fa-save"></i> Spara artikel
  </button>
</form>

<hr>

<h2><i class="fas fa-list"></i> Befintliga artiklar</h2>

<?php
$limit = 80;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$offset = ($page-1)*$limit;

$allowedSort = ['id','category','date'];
$sort = $_GET['sort'] ?? 'date';
if (!in_array($sort, $allowedSort)) $sort = 'date';
$dir = $_GET['dir'] ?? 'DESC';
$dir = strtoupper($dir) === 'ASC' ? 'ASC' : 'DESC';
$nextDir = $dir === 'ASC' ? 'DESC' : 'ASC';

$results = $db->query("SELECT * FROM articles ORDER BY $sort $dir LIMIT $limit OFFSET $offset");
?>

<table>
  <tr>
    <th><a href="?sort=id&dir=<?php echo ($sort==='id' ? $nextDir : 'ASC'); ?>">ID <i class="fas fa-sort"></i></a></th>
    <th>Titel</th>
    <th><a href="?sort=category&dir=<?php echo ($sort==='category' ? $nextDir : 'ASC'); ?>">Kategori <i class="fas fa-sort"></i></a></th>
    <th><a href="?sort=date&dir=<?php echo ($sort==='date' ? $nextDir : 'ASC'); ?>">Datum <i class="fas fa-sort"></i></a></th>
    <th>Bild</th>
    <th>Åtgärder</th>
  </tr>
<?php
while ($row = $results->fetchArray(SQLITE3_ASSOC)) {
    echo "<tr>";
    echo "<td>".(int)$row['id']."</td>";
    echo "<td>".htmlspecialchars(mb_strlen($row['title']) > 50 ? mb_substr($row['title'], 0, 50).'...' : $row['title'])."</td>";
    echo "<td>".htmlspecialchars($row['category'])."</td>";
    echo "<td>".htmlspecialchars($row['date'])."</td>";
    echo "<td>".(!empty($row['image']) ? "<img src='".htmlspecialchars($row['image'])."' width='80'>" : "-")."</td>";
    echo "<td class='action-links'>
            <a href='admin.php?edit=".$row['id']."'><i class='fas fa-edit'></i> Redigera</a> | 
            <a href='admin.php?delete=".$row['id']."' onclick=\"return confirm('Är du säker?')\"><i class='fas fa-trash'></i> Ta bort</a>
          </td>";
    echo "</tr>";
}
?>
</table>

<?php
$total = $db->querySingle("SELECT COUNT(*) FROM articles");
$pages = max(1, ceil($total / $limit));
?>
<div class="pagination">
  <?php if ($page > 1): ?>
    <a class="btn" href="?page=<?php echo $page-1; ?>&sort=<?php echo $sort; ?>&dir=<?php echo $dir; ?>"><i class="fas fa-arrow-left"></i> Föregående</a>
  <?php endif; ?>

  <span style="margin:0 1rem;">Sida <?php echo $page; ?> av <?php echo $pages; ?></span>

  <?php if ($page < $pages): ?>
    <a class="btn" href="?page=<?php echo $page+1; ?>&sort=<?php echo $sort; ?>&dir=<?php echo $dir; ?>">Nästa <i class="fas fa-arrow-right"></i></a>
  <?php endif; ?>
</div>

<p><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logga ut</a></p>

<?php endif; ?>
</body>
</html>