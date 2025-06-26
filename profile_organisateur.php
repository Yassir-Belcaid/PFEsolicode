<?php
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: find_tournoi.php");
    exit();
}

$organisateur_id = $_GET['id'];

$sql = "SELECT id_utilisateur, prenom, nom, type, photo, genre FROM Utilisateur WHERE id_utilisateur = ? AND type = 'Organisateur'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $organisateur_id);
$stmt->execute();
$result = $stmt->get_result();
$organisateur = $result->fetch_assoc();

if (!$organisateur) {
    header("Location: find_tournoi.php");
    exit();
}

$tournois_sql = "SELECT * FROM Tournoi WHERE id_organisateur = ? ORDER BY date_heure DESC";
$tournois_stmt = $conn->prepare($tournois_sql);
$tournois_stmt->bind_param("i", $organisateur_id);
$tournois_stmt->execute();
$tournois_result = $tournois_stmt->get_result();
$tournois = $tournois_result->fetch_all(MYSQLI_ASSOC);

$total_tournois = count($tournois);
$upcoming_tournois = 0;
$past_tournois = 0;

foreach ($tournois as $tournoi) {
    if (strtotime($tournoi['date_heure']) > time()) {
        $upcoming_tournois++;
    } else {
        $past_tournois++;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil de l'Organisateur</title>
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
            max-width: 1000px;
            width: 100%;
            position: relative;
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
            width: 380px;
            margin-bottom: 24px;
            display: inline-block;
            text-align: center;
            transition: background 0.2s, color 0.2s;
            text-decoration: none;
        }
        .btn:hover {
            background: #4caf50;
            color: #fff;
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #4caf50;
            margin-right: 20px;
        }
        .profile-info h1 {
            margin: 0 0 10px 0;
            color: #fff;
        }
        .profile-info p {
            margin: 5px 0;
            color: #e0e0e0;
        }
        .stats {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #fff;
            padding: 15px;
            border-radius: 20px;
            text-align: center;
            flex: 1;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4caf50;
        }
        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
        }
        .tournois-section h2 {
            color: #fff;
            margin-bottom: 20px;
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
        }
        .tournoi-title {
            font-size: 20px;
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
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-upcoming {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-past {
            background: #ffebee;
            color: #c62828;
        }
        .no-tournois {
            text-align: center;
            color: #e0e0e0;
            padding: 40px;
        }
        @media (max-width: 1100px) {
            .container { max-width: 98vw; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="find_tournoi.php" class="btn">← Retour aux tournois</a>
        
        <div class="profile-header">
            <img src="<?= $organisateur['photo'] ? $organisateur['photo'] : 'uploads/default-avatar.png' ?>" 
                 alt="Photo de profil" class="profile-photo">
            <div class="profile-info">
                <h1><?= htmlspecialchars($organisateur['prenom']) ?> <?= htmlspecialchars($organisateur['nom']) ?></h1>
                <p><strong>Type:</strong> <?= $organisateur['type'] ?></p>
                <?php if ($organisateur['genre']): ?>
                    <p><strong>Genre:</strong> <?= $organisateur['genre'] === 'Masculin' ? 'Masculin' : 'Féminin' ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $total_tournois ?></div>
                <div class="stat-label">Total Tournois</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $upcoming_tournois ?></div>
                <div class="stat-label">Tournois à venir</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $past_tournois ?></div>
                <div class="stat-label">Tournois passés</div>
            </div>
        </div>
        
        <div class="tournois-section">
            <h2>Tournois organisés</h2>
            
            <?php if (empty($tournois)): ?>
                <div class="no-tournois">
                    <p>Aucun tournoi organisé pour le moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($tournois as $tournoi): ?>
                    <div class="tournoi-card">
                        <div class="tournoi-title"><?= htmlspecialchars($tournoi['nom']) ?></div>
                        <div class="tournoi-info">
                            <span class="info-item">Lieu: <?= htmlspecialchars($tournoi['lieu']) ?></span>
                            <span class="info-item">Date: <?= date('d/m/Y', strtotime($tournoi['date_heure'])) ?></span>
                            <span class="info-item">Heure: <?= date('H:i', strtotime($tournoi['date_heure'])) ?></span>
                            <span class="info-item">Équipes: <?= $tournoi['nombre_equipes'] ?></span>
                            <span class="info-item">Joueurs: <?= $tournoi['nombre_joueurs_par_equipe'] ?></span>
                            <span class="info-item">Genre: <?= $tournoi['genre_participants'] === 'Masculin' ? 'Masculin' : 'Féminin' ?></span>
                            <span class="info-item">Frais: <?= $tournoi['participation_fee'] ?> DH</span>
                            <?php if (strtotime($tournoi['date_heure']) > time()): ?>
                                <span class="status-badge status-upcoming">À venir</span>
                            <?php else: ?>
                                <span class="status-badge status-past">Passé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 