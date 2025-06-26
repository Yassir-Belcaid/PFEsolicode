<?php
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$tournoi_id = intval($_GET['tournoi_id']);
$user_id = $_SESSION["user_id"];

$sql_t = "SELECT * FROM Tournoi WHERE id_tournoi = ?";
$stmt_t = $conn->prepare($sql_t);
$stmt_t->bind_param("i", $tournoi_id);
$stmt_t->execute();
$tournoi = $stmt_t->get_result()->fetch_assoc();
if (!$tournoi) { die('Tournoi introuvable.'); }

$is_organizer = ($tournoi['id_organisateur'] == $user_id);
$sql_part = "SELECT 1 FROM Participation p JOIN Equipe e ON p.id_equipe = e.id_equipe WHERE p.id_utilisateur = ? AND e.id_tournoi = ?";
$stmt_part = $conn->prepare($sql_part);
$stmt_part->bind_param("ii", $user_id, $tournoi_id);
$stmt_part->execute();
$is_player = $stmt_part->get_result()->fetch_row();
if (!$is_organizer && !$is_player) {
    die('Accès refusé. Vous n\'êtes pas inscrit à ce tournoi.');
}

$sql_matches = "SELECT m.round, e1.nom as equipe1_nom, e2.nom as equipe2_nom, w.nom as winner_nom
    FROM TournoiMatch m
    JOIN Equipe e1 ON m.equipe1_id = e1.id_equipe
    JOIN Equipe e2 ON m.equipe2_id = e2.id_equipe
    LEFT JOIN Equipe w ON m.winner_id = w.id_equipe
    WHERE m.id_tournoi = ?
    ORDER BY m.round, m.id_match";
$stmt_matches = $conn->prepare($sql_matches);
$stmt_matches->bind_param("i", $tournoi_id);
$stmt_matches->execute();
$matches = $stmt_matches->get_result()->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gestion du Bracket</title>
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
            margin-bottom: 10px;
        }
        .tournament-date {
            color: #4caf50;
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        .match {
            background: #fff;
            color: #222;
            border-radius: 20px;
            padding: 18px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
        }
        .match .team {
            font-weight: 700;
            font-size: 1.1rem;
        }
        .winner {
            font-weight: bold;
            color: #27ae60;
        }
        .round-title {
            color: #4caf50;
            margin: 24px 0 10px 0;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Gestion du Bracket</h1>
        <div class="tournament-date">
            Date du tournoi : <?= date('d/m/Y H:i', strtotime($tournoi['date_heure'])) ?>
        </div>
        <?php
        $current_round = null;
        foreach ($matches as $match) {
            if ($current_round !== $match['round']) {
                if ($current_round !== null) echo '</div>';
                $current_round = $match['round'];
                echo '<div class="round-title">Round ' . $current_round . '</div><div>';
            }
            echo '<div class="match">';
            echo '<span class="team">' . htmlspecialchars($match['equipe1_nom']) . '</span>';
            echo '<span style="font-weight:600;">VS</span>';
            echo '<span class="team">' . htmlspecialchars($match['equipe2_nom']) . '</span>';
           
            echo '</div>';
        }
        if ($current_round !== null) echo '</div>';
        ?>
    </div>
</body>
</html> 