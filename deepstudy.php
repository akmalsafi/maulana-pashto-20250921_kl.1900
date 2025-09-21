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
?>
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

/* Stilar för intro-textblock */
.intro-block {
    background-color: #f0f0f0;
    padding: 25px;
    border-radius: 8px;
    margin-bottom: 30px;
    width: 100%;
    box-sizing: border-box;
    line-height: 1.6;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.intro-block h2 {
    margin-top: 0;
    color: #2c5c34;
    border-bottom: 2px solid #2c5c34;
    padding-bottom: 10px;
}

/* Anpassa grid-layout för att matcha intro-blockets bredd */
.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    width: 100%;
}
</style>


</head>
<body>

<?php include 'nav.php'; ?>

<main class="container">

  <!-- Textblock högst upp på sidan -->
  <div class="intro-block">
    <h2>د مولانا وحید الدین خان پښتو لیکنې ته ښه راغلئ</h2>
    <p>په دې وېب پاڼه کې تاسو د نامتو اسلامی عالم مولانا وحید الدین خان صاحب په اړه مقالې او لیکنې پیدا کولی شئ. د هغه لیکنې د اسلام په اړه د اصلاحي فکر او معتدلې تفسیر لپاره مشهورې دي. دلته تاسو کولی شئ د خدای ادراک، جهاد، جنت او نورو موضوعاتو په اړه د هغه قیمتي لیکنې ولولئ.</p>
    <p>مولانا وحید الدین خان د اسلام د پیغام د رسولو لپاره د سولې او عقلاني استدلال په وړاندې کولو تاکید کوي. د هغه لیکنې د هغه د ژورې پوهې، دیني علم او د عصري نړۍ سره د اسلام د اړیکو په اړه ښکاري.</p>
  </div>

  <section>
    <h2>🔎 د الهي ادراک مطلب څه دی؟</h2>
    <?php renderArticles($db, 'Discovering God', 3); ?>
    <a href="discoveryofgod.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

  <section>
    <h2>⚔️  جهاد څه دی او څه تقاضې لري؟</h2>
    <?php renderArticles($db, 'Jihad', 3); ?>
    <a href="jihad.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

  <section>
    <h2>🌴 جنت څه دی او د جنت اوسېدونکي څوک دي؟</h2>
    <?php renderArticles($db, 'Paradise', 3); ?>
    <a href="paradise.php" class="read-more-btn">دې موضوع په هکله نور مطالب</a>
  </section>

</main>

<?php include 'footer.php'; renderFooter(); ?>

</body>
</html>