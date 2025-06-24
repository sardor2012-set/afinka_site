<?php
session_start();

// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reviews_db";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

// Создание таблицы отзывов, если не существует
$conn->exec("CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    comment TEXT NOT NULL,
    rating INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approved BOOLEAN DEFAULT FALSE
)");

// Обработка отправки отзыва
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_review'])) {
    $name = htmlspecialchars($_POST['name']);
    $comment = htmlspecialchars($_POST['comment']);
    $rating = (int)$_POST['rating'];
    
    if (!empty($name) && !empty($comment) && $rating >= 1 && $rating <= 5) {
        $stmt = $conn->prepare("INSERT INTO reviews (name, comment, rating) VALUES (?, ?, ?)");
        $stmt->execute([$name, $comment, $rating]);
        $success = "Отзыв отправлен на модерацию!";
    } else {
        $error = "Пожалуйста, заполните все поля корректно!";
    }
}

// Обработка авторизации админа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_login'])) {
    $admin_password = $_POST['admin_password'];
    if ($admin_password === '123456') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $admin_error = "Неверный пароль!";
    }
}

// Обработка выхода админа
if (isset($_GET['logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Обработка действий админа
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['admin_action']) && isset($_SESSION['admin_logged_in'])) {
    if ($_POST['admin_action'] == 'approve') {
        $stmt = $conn->prepare("UPDATE reviews SET approved = TRUE WHERE id = ?");
        $stmt->execute([$_POST['review_id']]);
    } elseif ($_POST['admin_action'] == 'delete') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$_POST['review_id']]);
    }
}
?>


<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Afinka</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
  <link rel="stylesheet" href="./styel.css">
  <script src="https://kit.fontawesome.com/aa11b2d154.js" crossorigin="anonymous"></script>
          <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
            integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
            crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&display=swap" rel="stylesheet">
  
</head>

<body>
  <header>
    <div class="qwert" style="font-size: 24px; color: #fff;"><strong><i class="fa-regular fa-gem"></i> Afinka <i
          class="fa-regular fa-gem"></i></strong></div>
    <nav>
      <a href="#home"><i class="fa-solid fa-house"></i> Главное</a>
      <a href="#advantages"><i class="fa-solid fa-circle-info"></i> О нас</a>
      <a href="#help"><i class="fa-solid fa-handshake-angle"></i> Помощь</a>
      <a href="#catalog"><i class="fa-solid fa-cart-shopping"></i> Отзывы</a>
      <a href="#news"><i class="fa-solid fa-envelope-open-text"></i> Новости</a>
    </nav>
  </header>

    <hr class="asdf" id="home">
  <section class="hero">
    <img src="./photo/11.png" alt="Afinka Logo" class="wow zoomIn" data-wow-duration="1.5s">
    <h1 class="wow fadeInUp" data-wow-delay="0.3s"><i class="fa-regular fa-gem"></i> Afinka <i
        class="fa-regular fa-gem"></i></h1>
    <p class="wow fadeInUp" data-wow-delay="0.5s"><span class="as">Афинка</span> — это уникальная возможность получать игровую валюту за приглашения друзей на сервере HolyWorld. <br> Поделись своей реферальной ссылкой, зови друзей в бота и получай награды, когда они переходят по ссылке!</p>
  </section>

     <hr class="asdf" id="advantages">
  <section class="section">
    <h2 class="wow fadeInUp">О нас</h2>
    <div class="card wow fadeInLeft" data-wow-delay="0.2s">Скорость до 300 км/ч</div>
    <div class="card wow fadeInUp" data-wow-delay="0.4s">Мгновенный разгон</div>
    <div class="card wow fadeInRight" data-wow-delay="0.6s">Современный дизайн</div>
  </section>

  <hr class="asdf" id="help">
    <section id="help" class="section">
    <h2 class="wow fadeInUp">Помощь</h2>
    <div class="card wow fadeInLeft" data-wow-delay="0.2s">Индивидуальная консультация</div>
    <div class="card wow fadeInRight" data-wow-delay="0.4s">Онлайн-подбор модели</div>
  </section>

  <hr class="asdf" id="catalog">
    <div class="review-form">
        <h2>Оставить отзыв</h2>
        <?php if (isset($success)) echo "<p class='success'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <label>Имя:</label><br>
            <input type="text" name="name" required><br><br>
            <label>Отзыв:</label><br>
            <textarea name="comment" required></textarea><br><br>
            <label>Оценка:</label><br>
            <select name="rating" required>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select><br><br>
            <button type="submit" name="submit_review">Отправить</button>
        </form>
    </div>

    <!-- Отображение утвержденных отзывов -->
    <div class="reviews-list">
        <h2>Отзывы</h2>
        <?php
        $stmt = $conn->query("SELECT * FROM reviews WHERE approved = TRUE ORDER BY created_at DESC");
        while ($row = $stmt->fetch()) {
            echo "<div class='review'>";
            echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
            echo "<p>" . htmlspecialchars($row['comment']) . "</p>";
            echo "<p class='rating'>" . str_repeat("★", $row['rating']) . "</p>";
            echo "<small>" . $row['created_at'] . "</small>";
            echo "</div>";
        }
        ?>
    </div>

    <!-- Форма входа в админ-панель или админ-панель -->
    <div class="admin-panel">
        <?php if (!isset($_SESSION['admin_logged_in'])): ?>
            <h2>Вход в админ-панель</h2>
            <?php if (isset($admin_error)) echo "<p class='error'>$admin_error</p>"; ?>
            <form method="POST">
                <label>Пароль:</label><br>
                <input type="password" name="admin_password" required><br><br>
                <button type="submit" name="admin_login">Войти</button>
            </form>
        <?php else: ?>
            <h2>Админ-панель</h2>
            <a href="?logout=true">Выйти</a>
            <?php
            $stmt = $conn->query("SELECT * FROM reviews WHERE approved = FALSE ORDER BY created_at DESC");
            while ($row = $stmt->fetch()) {
                echo "<div class='review'>";
                echo "<h3>" . htmlspecialchars($row['name']) . "</h3>";
                echo "<p>" . htmlspecialchars($row['comment']) . "</p>";
                echo "<p class='rating'>" . str_repeat("★", $row['rating']) . "</p>";
                echo "<small>" . $row['created_at'] . "</small>";
                echo "<form method='POST'>";
                echo "<input type='hidden' name='review_id' value='" . $row['id'] . "'>";
                echo "<button type='submit' name='admin_action' value='approve'>Утвердить</button>";
                echo "<button type='submit' name='admin_action' value='delete'>Удалить</button>";
                echo "</form>";
                echo "</div>";
            }
            ?>
        <?php endif; ?>
    </div>

  </section>
  <hr class="asdf" id="news">
  <section class="section">
    <h2 class="wow fadeInUp">Новости</h2>
    <div class="news">
      <a style="color: white;" href="https://t.me/hwlite" class="card wow fadeInLeft" data-wow-delay="0.3s"><img style="width: 200px;" src="./photo/Без имени.png" alt=""> <br> 23 июня произошел вай Lite Анархии HolyWorld! <br> Залетай скорее мы тебя ждём!</a>
      <a style="color: white;" href="https://t.me/HWBases" class="card wow fadeInRight" data-wow-delay="0.5s"><img style="width: 165px;" src="./photo/asdet.png" alt=""> <br> В среди с вайпом на Lite Анархии HolyWorld наш бот Afinka был запушен в 23.06.2025 14:00 по МСК!</a>
      <a style="color: white;" href="https://t.me/AfinkaTG" class="card wow fadeInRight" data-wow-delay="0.5s"><img style="width: 165px;" src="./photo/asdet2.png" alt=""> <br> Компания Афинка стала самым крупнейшим и честным сервисом по выдачи коинов за рефералы на LITE HolyWorld!</a>
    </div>
  </section>


<div class="footer-wave">
  <svg viewBox="0 0 1440 150" preserveAspectRatio="none" class="wave-flip">
    <path fill="#111" d="M0,64L60,74.7C120,85,240,107,360,101.3C480,96,600,64,720,64C840,64,960,96,1080,117.3C1200,139,1320,149,1380,154.7L1440,160V0H0Z"></path>
  </svg>
</div>

  <footer class="wow fadeInUp">
    <span><i class="fa-brands fa-telegram" style="color: #74C0FC;"></i> <span class="c"><a href="https://t.me/HWBases">https://t.me/HWBases</a></span> ✨ <i class="fa-brands fa-telegram" style="color: #63E6BE;"></i> <span class="v"><a href="https://t.me/AfinkaTG">https://t.me/AfinkaTG</a></span> ✨ <i class="fa-brands fa-telegram" style="color: #FFD43B;"></i> <span class="b"><a href="https://t.me/hwAfinkaBot">https://t.me/hwAfinkaBot</a></span></span>
    <br>
    <h1 style="margin-top: 10px;" class="det"><i class="fa-regular fa-gem"></i> Afinka <i
          class="fa-regular fa-gem"></i></h1>
    <hr style="margin: 0 auto; width: 250px;">
    <img style="width: 300px; margin: 10px;" src="./photo/Без имени.png" alt="">
    <br>
    © 2025 AFINKA. Все права защищены. | Сделано с любовью ⚡
    <br>
            <marquee style="width: 700px;" behavior="" direction="">
            <span>Нашли баг или столкнулись с проблемами? </span><span>Обращайтесь мы всегда рады помочь вам!</span>
          </marquee>
          <br>
          <a class="btn" href="./index2.html">Подержка</a>
  </footer>

  <script>
    new WOW().init();
  </script>

  <script>
        let next = document.querySelector('.next')
let prev = document.querySelector('.prev')

next.addEventListener('click', function(){
    let items = document.querySelectorAll('.item')
    document.querySelector('.slide').appendChild(items[0])
})

prev.addEventListener('click', function(){
    let items = document.querySelectorAll('.item')
    document.querySelector('.slide').prepend(items[items.length - 1]) // here the length of items = 6
})



  </script>
</body>

</html>