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

$joueur_id = $_GET['id'];

$sql = "SELECT id_utilisateur, prenom, nom, type, photo, genre FROM Utilisateur WHERE id_utilisateur = ? AND type = 'Participant'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $joueur_id);
$stmt->execute();
$result = $stmt->get_result();
$joueur = $result->fetch_assoc();

if (!$joueur) {
    echo "Joueur non trouvé.";
    exit();
}

$sql_wins = "SELECT COUNT(t.id_tournoi) as wins 
             FROM Tournoi t
             JOIN Participation p ON t.id_equipe_gagnante = p.id_equipe
             WHERE p.id_utilisateur = ? AND t.id_equipe_gagnante IS NOT NULL";
$stmt_wins = $conn->prepare($sql_wins);
$stmt_wins->bind_param("i", $joueur_id);
$stmt_wins->execute();
$wins_result = $stmt_wins->get_result()->fetch_assoc();
$win_count = $wins_result['wins'] ?? 0;

$final_count = "N/A"; 

?>

<!DOCTYPE html>
<html>
<head>
    <title>Profil de <?= htmlspecialchars($joueur['prenom']) ?></title>
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
            justify-content: center;
        }
        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 20px;
            text-align: center;
            flex: 1;
            max-width: 200px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            color: #4caf50;
        }
        .stat-label {
            color: #7f8c8d;
            margin-top: 5px;
            font-size: 1.1em;
        }
        @media (max-width: 900px) {
            .container { max-width: 98vw; padding: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="btn">← Retour</a>
        
        <div class="profile-header">
            <img src="<?= $joueur['photo'] ? $joueur['photo'] : 'uploads/default-avatar.png' ?>" 
                 alt="Photo de profil" class="profile-photo">
            <div class="profile-info">
                <h1><?= htmlspecialchars($joueur['prenom']) ?> <?= htmlspecialchars($joueur['nom']) ?></h1>
            </div>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?= $win_count ?></div>
                <div class="stat-label">Victoires</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= $final_count ?></div>
                <div class="stat-label">Finales Atteintes</div>
            </div>
        </div>

    </div>
</body>
</html> 