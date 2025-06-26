<?php
include 'config.php';


if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}


$sql = "SELECT * FROM Tournoi WHERE id_organisateur = ? ORDER BY date_heure DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$tournois = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mes tournois organisés</title>
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
            margin-bottom: 20px;
        }
        .tournoi-card {
            background: #fff;
            border-radius: 20px;
            padding: 24px;
            margin-bottom: 18px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.10);
        }
        .tournoi-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #222;
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
        .no-tournois {
            text-align: center;
            color: #e0e0e0;
            padding: 40px;
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
            display: inline-block;
            transition: background 0.2s, color 0.2s;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background: #4caf50;
            color: #fff;
        }
        .btn-danger {
            background: #e74c3c;
            color: #fff;
        }
        .actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }
        .back-link {
            color: #4caf50;
            text-decoration: underline;
            font-weight: 600;
            display: block;
            text-align: center;
            margin-top: 18px;
        }
        @media (max-width: 900px) {
            .container { max-width: 98vw; padding: 10px; }
        }
        .tournoi-card.clickable {
            cursor: pointer;
            border: 2px solid #4caf50;
            box-shadow: 0 0 10px #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Mes tournois organisés</h1>
        
        <?php if (isset($_GET["success"])): ?>
            <div style="color: green; text-align: center; margin-bottom: 15px;">
                Tournoi créé avec succès!
            </div>
        <?php endif; ?>
        
        <?php if ($tournois->num_rows > 0): ?>
            <?php while($tournoi = $tournois->fetch_assoc()): ?>
                <?php
                $sql_teams = "SELECT COUNT(*) as total, SUM(
                    (SELECT COUNT(*) FROM Participation WHERE id_equipe = e.id_equipe)
                    >= t.nombre_joueurs_par_equipe
                ) as full_count
                FROM Equipe e
                JOIN Tournoi t ON e.id_tournoi = t.id_tournoi
                WHERE e.id_tournoi = ?";
                $stmt_teams = $conn->prepare($sql_teams);
                $stmt_teams->bind_param("i", $tournoi["id_tournoi"]);
                $stmt_teams->execute();
                $teams_info = $stmt_teams->get_result()->fetch_assoc();
                $all_full = $teams_info && $teams_info['total'] > 0 && $teams_info['total'] == $teams_info['full_count'];
                ?>
                <div class="tournoi-card<?php if($all_full) echo ' clickable'; ?>" <?php if($all_full) echo 'data-tid="' . $tournoi["id_tournoi"] . '"'; ?>>
                    <div class="tournoi-title"><?= $tournoi["nom"] ?></div>
                    <div class="tournoi-info">
                        <span class="info-item">Lieu: <?= $tournoi["lieu"] ?></span>
                        <span class="info-item">Date: <?= date("d/m/Y", strtotime($tournoi["date_heure"])) ?></span>
                        <span class="info-item">Heure: <?= date("H:i", strtotime($tournoi["date_heure"])) ?></span>
                        <span class="info-item">Équipes: <?= $tournoi["nombre_equipes"] ?></span>
                        <span class="info-item">Joueurs/équipe: <?= $tournoi["nombre_joueurs_par_equipe"] ?></span>
                        <span class="info-item">Genre: <?= $tournoi["genre_participants"] ?></span>
                        <span class="info-item">Frais: <?= $tournoi["participation_fee"] ?> DH</span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-tournois">
                Vous n'avez pas encore créé de tournois.
                <br>
                <a href="creer_tournoi.php" class="btn" style="margin-top: 10px;">Créer un tournoi</a>
            </div>
        <?php endif; ?>
        
        <a href="dashboard_org.php" class="btn" style="width:380px;margin-top:24px;display:inline-block;text-align:center;">← Retour au tableau de bord</a>
    </div>
    <script>
        document.querySelectorAll('.tournoi-card.clickable').forEach(card => {
            card.addEventListener('click', function() {
                const tid = this.getAttribute('data-tid');
                if (tid) {
                    window.location.href = 'bracket.php?tournoi_id=' + tid;
                }
            });
        });
    </script>
</body>
</html>