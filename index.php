<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservation program</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>

<body>
<?php
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $db = new mysqli('localhost', 'root', '', 'reglog');

    /* register */
    if ($action == 'register') {
        $query = $db->prepare("INSERT INTO user (id, email, password) VALUES (NULL, ?, ?)");
        $query->bind_param('ss', $email, $password);
        $result = $query->execute();

        if ($result) {
            echo "Konto utworzono poprawnie";
        } else {
            echo "Błąd podczas tworzenia konta";
        }
    }

    /* login */
    if ($action == 'login') {
        $query = $db->prepare("SELECT id, password FROM user WHERE email = ? LIMIT 1");
        $query->bind_param('s', $email);
        $query->execute();
        $result = $query->get_result();
        $userRow = $result->fetch_assoc();
        if ($userRow && $password == $userRow['password']) {
            echo "Zalogowano poprawnie";
            $_SESSION['user_id'] = $userRow['id'];
            $_SESSION['user_email'] = $email;
            setcookie("user_id", $userRow['id'], time() + (86400 * 30), "/");
            setcookie("user_email", $email, time() + (86400 * 30), "/");

            // Zapis do pliku
            $logData = "Zalogowano użytkownika o ID: " . $userRow['id'] . ", Email: " . $email . "\n";
            file_put_contents("log.txt", $logData, FILE_APPEND | LOCK_EX);
        } else {
            echo "Nieprawidłowy login lub hasło";
        }
    }

    /* logout */
    if ($action == 'logout') {
        session_destroy();
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("user_email", "", time() - 3600, "/");
        header("Location: index.php");
        exit();
    }

    /* update email */
    if ($action == 'update_email') {
        $newEmail = $_POST['new_email'];
        $userId = $_SESSION['user_id'];

        $query = $db->prepare("UPDATE user SET email = ? WHERE id = ?");
        $query->bind_param('si', $newEmail, $userId);
        $result = $query->execute();

        if ($result) {
            echo "Adres e-mail został zaktualizowany";
            $_SESSION['user_email'] = $newEmail;
        } else {
            echo "Błąd podczas aktualizacji adresu e-mail";
        }
    }

    /* delete account */
    if ($action == 'delete_account') {
        $userId = $_SESSION['user_id'];

        $query = $db->prepare("DELETE FROM user WHERE id = ?");
        $query->bind_param('i', $userId);
        $result = $query->execute();

        if ($result) {
            echo "Konto zostało usunięte";
            session_destroy();
            setcookie("user_id", "", time() - 3600, "/");
            setcookie("user_email", "", time() - 3600, "/");
            header("Location: index.php");
            exit();
        } else {
            echo "Błąd podczas usuwania konta";
        }
    }
}
?>
<div class="container">
    <?php if (!isset($_SESSION['user_id']) && !isset($_SESSION['user_email'])) : ?>
        <div class="row">
            <div class="col-4 offset-4">
                <h1 class="text-center">Zarejestruj się</h1>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="register">
                    <label class="form-label" for="emailInput">Adres e-mail:</label>
                    <input class="form-control mb-3" type="email" name="email" id="emailInput" required>
                    <label class="form-label" for="passwordInput">Hasło:</label>
                    <input class="form-control mb-3" type="password" name="password" id="passwordInput">
                    <button class="btn btn-primary w-100" type="submit">Załóż konto</button>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-4 offset-4">
                <h1 class="text-center">Zaloguj się</h1>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="login">
                    <label class="form-label" for="emailInput">Adres e-mail:</label>
                    <input class="form-control mb-3" type="email" name="email" id="emailInput" required>
                    <label class="form-label" for="passwordInput">Hasło:</label>
                    <input class="form-control mb-3" type="password" name="password" id="passwordInput">
                    <button class="btn btn-primary w-100" type="submit">Zaloguj</button>
                </form>
            </div>
            </div>
        </div>
    <?php else : ?>
        <div class="row mt-5">
            <div class="col-4 offset-4">
                <h1 class="text-center">Witaj ponownie</h1>
                <p>Id użytkownika: <?php echo $_SESSION['user_id'] ?></p>
                <p>Email użytkownika: <?php echo $_SESSION['user_email'] ?></p>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="logout">
                    <button type="submit" class="btn btn-primary w-100">Wyloguj</button>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-4 offset-4">
                <h1 class="text-center">Aktualizuj dane</h1>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="update_email">
                    <label class="form-label" for="newEmailInput">Nowy adres e-mail:</label>
                    <input class="form-control mb-3" type="email" name="new_email" id="newEmailInput" required>
                    <button class="btn btn-primary w-100" type="submit">Aktualizuj</button>
                </form>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-4 offset-4">
                <h1 class="text-center">Usuń konto</h1>
                <form action="index.php" method="post">
                    <input type="hidden" name="action" value="delete_account">
                    <button class="btn btn-danger w-100" type="submit">Usuń konto</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz"
        crossorigin="anonymous"></script>
</body>
</html>