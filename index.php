<?php
$db = new SQLite3(__DIR__ . '/data.db');

function renderArticles($db, $category, $limit = 3) {
    $limit = (int)$limit;
    $sql = "
        SELECT id, title, excerpt, image, date
        FROM articles
        WHERE TRIM(category) = :category
        ORDER BY date DESC
        LIMIT $limit
    ";
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':category', $category, SQLITE3_TEXT);
    $res = $stmt->execute();

    echo '<div class="grid">';
    while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
        $img = trim($row['image'] ?? '') !== '' ? $row['image'] : 'assets/fallback.jpg';

        $excerpt = strip_tags($row['excerpt']);
        $excerpt = htmlspecialchars($excerpt);
        if (mb_strlen($excerpt) > 80) {
            $excerpt = mb_strimwidth($excerpt, 0, 80, "…", "UTF-8");
        }

        echo '<div class="card">';
        echo '<img src="'.htmlspecialchars($img).'" alt="" class="article-cover" loading="lazy">';
        echo '<h3 class="article-title"><a href="article.php?id='.$row['id'].'">'.htmlspecialchars($row['title']).'</a></h3>';
        echo '<p class="badge">'.htmlspecialchars($row['date']).'</p>';
        echo '<p>'.$excerpt.'</p>';
        echo '</div>';
    }
    echo '</div>';
}

$latestThematic = $db->querySingle("SELECT * FROM articles WHERE category='Tematisk läsning' ORDER BY date DESC LIMIT 1", true);?>

<!doctype html>
<html lang="ps" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ژوره مطالعه - د مولانا وحید الدین خان پښتو لېکنې</title>
<link rel="stylesheet" href="styles.css">


<style>
.read-more-btn {
    display: block;
    margin: 15px auto 10px;
    padding: 10px 20px;
    background-color: #2c5c34;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    text-align: center;
    width: 250px;
    transition: all 0.3s;
    box-shadow: 0 2px 5px;
}

.read-more-btn:hover {
    background-color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px;
}

section {
    margin-bottom: 10px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eaeaea;
}
</style>


</head>
<body>

<?php include 'nav.php'; ?>

<main class="container">

  <?php if ($latestThematic): ?>
  <section class="featured-thematic">
    <img src="<?php echo htmlspecialchars($latestThematic['image'] ?: 'assets/fallback.jpg'); ?>" alt="">
    <div class="info">
      <h2><a href="thematicarticles.php?id=<?php echo $latestThematic['id']; ?>">🌟 <?php echo htmlspecialchars($latestThematic['title']); ?></a></h2>
      <p class="date"><?php echo htmlspecialchars($latestThematic['date']); ?></p>
      <p>
        <?php 
          $excerpt = strip_tags($latestThematic['excerpt']);
          echo htmlspecialchars(mb_strimwidth($excerpt, 0, 120, "…", "UTF-8")); 
        ?>
      </p>
    </div>
  </section>
  <?php endif; ?>


  <section>
    <h2><a href="daily.php" class="section-heading-link">📖 لنډ مطالب‎</a></h2>
    <?php renderArticles($db, 'Dagens läsning', 6); ?>
  </section>

  <section>
    <h2><a href="weekly.php" class="section-heading-link">📚 ځانګړي مطالب</a></h2>
    <?php renderArticles($db, 'Veckans läsning', 6); ?>
  </section>

  <section>
    <h2><a href="discoveryofgod.php" class="section-heading-link">🔎 د خدای ادراک مطلب څه دی؟</a></h2>
    <?php renderArticles($db, 'Discovering God', 3); ?>
    <a href="discoveryofgod.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

  <section>
    <h2><a href="jihad.php" class="section-heading-link">⚔️ جهاد څه دی او څه تقاضې لري؟</a></h2>
    <?php renderArticles($db, 'Jihad', 3); ?>
    <a href="jihad.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

  <section>
    <h2><a href="paradise.php" class="section-heading-link">🌴 د جنت تصور څه دی؟</a></h2>
    <?php renderArticles($db, 'Paradise', 3); ?>
    <a href="paradise.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

</main>

<?php include 'footer.php'; renderFooter(); ?>

</body>
</html>