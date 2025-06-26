<?php
include 'config.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Participant") {
    header("Location: login.php");
    exit();
}


$sql = "SELECT t.*, t.nom as nom_tournoi, u.prenom, u.nom as nom_organisateur
        FROM Tournoi t
        JOIN Utilisateur u ON t.id_organisateur = u.id_utilisateur
        WHERE t.genre_participants = (SELECT genre FROM Utilisateur WHERE id_utilisateur = ?)
        ORDER BY t.date_heure DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$result = $stmt->get_result();
$tournois = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Trouver un Tournoi</title>
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
        .container {
            background: rgba(40, 40, 40, 0.95);
            border-radius: 30px 30px 40px 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px 32px 32px 32px;
            max-width: 800px;
            width: 100%;
            position: relative;
        }
        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 30px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #4caf50;
            text-decoration: underline;
            font-weight: 600;
        }
        .tournoi-card {
            border: none;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 18px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
            transition: all 0.3s;
        }
        .tournoi-card:hover {
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            transform: translateY(-2px);
        }
        .tournoi-title {
            font-size: 22px;
            color: #222;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .tournoi-info {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .info-item {
            background: #eafaf1;
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 15px;
            color: #222;
            font-weight: 500;
        }
        .organisateur {
            font-style: italic;
            color: #7f8c8d;
            margin-bottom: 8px;
        }
        .btn {
            background: #fff;
            color: #222;
            border: none;
            border-radius: 20px;
            padding: 10px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 180px;
            margin: 10px 0 0 0;
            display: block;
            transition: background 0.2s, color 0.2s;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background: #4caf50;
            color: #fff;
        }
        p, .tournoi-card, .organisateur {
            color: #222;
        }
        @media (max-width: 900px) {
            .container { max-width: 98vw; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard_joueur.php" class="btn" style="width:380px;margin-bottom:24px;display:inline-block;text-align:center;">← Retour au tableau de bord</a>
        <h1>Tournois Disponibles</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div style="color: green; text-align: center; margin-bottom: 20px; padding: 10px; background: #e8f5e9; border-radius: 5px;">
                Inscription réussie au tournoi!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error']) && $_GET['error'] === 'gender_mismatch'): ?>
            
        <?php endif; ?>
        
        <?php 
        
        $userGenderSql = "SELECT genre FROM Utilisateur WHERE id_utilisateur = ?";
        $userGenderStmt = $conn->prepare($userGenderSql);
        $userGenderStmt->bind_param("i", $_SESSION["user_id"]);
        $userGenderStmt->execute();
        $userGender = $userGenderStmt->get_result()->fetch_assoc();
        ?>
        
       
        
        <?php if (empty($tournois)): ?>
            <p>Aucun tournoi disponible pour votre genre pour le moment.</p>
        <?php else: ?>
            <?php foreach ($tournois as $tournoi): ?>
                <div class="tournoi-card">
                    <div class="tournoi-title"><?= htmlspecialchars($tournoi['nom_tournoi']) ?></div>
                    <div class="tournoi-info">
                        <span class="info-item">Lieu: <?= htmlspecialchars($tournoi['lieu']) ?></span>
                        <span class="info-item">Date: <?= date('d/m/Y', strtotime($tournoi['date_heure'])) ?></span>
                        <span class="info-item">Heure: <?= date('H:i', strtotime($tournoi['date_heure'])) ?></span>
                        <span class="info-item">Équipes: <?= $tournoi['nombre_equipes'] ?></span>
                        <span class="info-item">Joueurs: <?= $tournoi['nombre_joueurs_par_equipe'] ?></span>
                        <span class="info-item">Frais: <?= $tournoi['participation_fee'] ?> DH</span>
                    </div>
                    <div class="organisateur">
                        Organisé par: <a href="profile_organisateur.php?id=<?= $tournoi['id_organisateur'] ?>" style="color: #3498db; text-decoration: none; font-weight: bold;"><?= htmlspecialchars($tournoi['prenom']) ?> <?= htmlspecialchars($tournoi['nom_organisateur']) ?></a>
                    </div>
                    <a href="inscription_tournoi.php?id=<?= $tournoi['id_tournoi'] ?>" class="btn">S'inscrire</a>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</body>
</html>