<?php
include 'config.php';


if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau de Bord Organisateur</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('uploads/TEST.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dashboard-container {
            background: rgba(40, 40, 40, 0.95);
            border-radius: 30px 30px 40px 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px 32px 32px 32px;
            max-width: 500px;
            width: 100%;
            position: relative;
            text-align: center;
        }
        h1 {
            color: #fff;
            margin-bottom: 40px;
            font-size: 24px;
        }
        .welcome-msg {
            margin-bottom: 30px;
            color: #fff;
            font-size: 1.1rem;
        }
        .menu-links {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .menu-link {
            color: #222;
            background: #fff;
            text-decoration: none;
            font-size: 18px;
            padding: 12px;
            transition: all 0.3s;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            font-weight: 600;
        }
        .menu-link:hover {
            background: #4caf50;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        @media (max-width: 600px) {
            .dashboard-container { padding: 18px 4px; }
            h1 { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Tableau de Bord Organisateur</h1>
        <div class="welcome-msg">Bonjour <?= $_SESSION["prenom"] ?></div>
        
        <div class="menu-links">
            <a href="profile.php" class="menu-link">Modifier Mon Profil</a>
            <a href="creer_tournoi.php" class="menu-link">Créer un Tournoi</a>
            <a href="mes_tournois.php" class="menu-link">Mes Tournois Organisés</a>
        </div>
    </div>
</body>
</html>