<?php
include 'config.php';


if (!isset($_SESSION["user_id"]) || $_SESSION["user_type"] != "Organisateur") {
    header("Location: login.php");
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = $_POST["nom"];
    $lieu = $_POST["lieu"];
    $date = $_POST["date"];
    $heure = $_POST["heure"];
    $nb_equipes = $_POST["nb_equipes"];
    $nb_joueurs = $_POST["nb_joueurs"];
    $genre = $_POST["genre"];
    $frais = $_POST["frais"];
    
    $date_heure = $date . " " . $heure . ":00";
    
    $sql = "INSERT INTO Tournoi (nom, lieu, date_heure, nombre_equipes, nombre_joueurs_par_equipe, genre_participants, participation_fee, id_organisateur) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssiisii", $nom, $lieu, $date_heure, $nb_equipes, $nb_joueurs, $genre, $frais, $_SESSION["user_id"]);
    
    if ($stmt->execute()) {
        $new_tournoi_id = $conn->insert_id;
        
        $sql_equipe = "INSERT INTO Equipe (nom, id_tournoi) VALUES (?, ?)";
        $stmt_equipe = $conn->prepare($sql_equipe);
        
        for ($i = 0; $i < $nb_equipes; $i++) {
            $nom_equipe = "Team " . chr(65 + $i);
            $stmt_equipe->bind_param("si", $nom_equipe, $new_tournoi_id);
            $stmt_equipe->execute();
        }
        
        header("Location: mes_tournois.php?success=1");
        exit();
    } else {
        $error = "Erreur: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Créer un tournoi</title>
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
            max-width: 600px;
            width: 100%;
            position: relative;
        }
        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #fff;
        }
        input, select {
            width: 90%;
            padding: 10px 16px;
            border: none;
            border-radius: 20px;
            font-size: 1rem;
            background: #eee;
            margin-top: 5px;
            outline: none;
            transition: box-shadow 0.2s;
        }
        input:focus, select:focus {
            box-shadow: 0 0 0 2px #4caf50;
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
            width: 100%;
            margin: 18px 0 0 0;
            display: block;
            transition: background 0.2s, color 0.2s;
            text-align: center;
            text-decoration: none;
        }
        .btn:hover {
            background: #4caf50;
            color: #fff;
        }
        .error {
            color: #ff5252;
            background: #fff0f0;
            border-radius: 10px;
            padding: 6px 12px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 0.95rem;
        }
        .back-link {
            color: #4caf50;
            text-decoration: underline;
            font-weight: 600;
            display: block;
            text-align: center;
            margin-top: 18px;
        }
        @media (max-width: 700px) {
            .container { padding: 18px 4px; }
            h1 { font-size: 1.2rem; }
        }
        .type-switcher {
            display: flex;
            gap: 15px;
            margin-bottom: 10px;
        }
        .type-btn {
            flex: 1;
            padding: 12px 0;
            background: #444;
            color: #fff;
            border: none;
            outline: none;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border-radius: 20px;
            transition: background 0.2s, color 0.2s;
        }
        .type-btn.selected {
            background: #fff;
            color: #222;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Créer un nouveau tournoi</h1>
        
        <?php if (isset($error)): ?>
            <div class="error"><?= $error ?></div>
        <?php endif; ?>
        
        <form action="creer_tournoi.php" method="POST">
            <div class="form-group">
                <label for="nom">Nom du tournoi</label>
                <input type="text" id="nom" name="nom" required>
            </div>
            
            <div class="form-group">
                <label for="lieu">Lieu</label>
                <select id="lieu" name="lieu" required>
                    <option value="">-- Choisir un terrain --</option>
                    <option value="Terrain de Hay Al Wahda">Terrain de Hay Al Wahda</option>
                    <option value="Terrain de Hay Al Qods">Terrain de Hay Al Qods</option>
                    <option value="Terrain de Hay Al Manar">Terrain de Hay Al Manar</option>
                    <option value="Terrain de Hay Al Massira">Terrain de Hay Al Massira</option>
                    <option value="Terrain de Hay Al Farah">Terrain de Hay Al Farah</option>
                    <option value="Terrain de Hay Al Boughaz">Terrain de Hay Al Boughaz</option>
                    <option value="Terrain de Hay Al Firdaous">Terrain de Hay Al Firdaous</option>
                    <option value="Terrain de Hay Al Oulfa">Terrain de Hay Al Oulfa</option>
                    <option value="Terrain de Hay Al Mansour">Terrain de Hay Al Mansour</option>
                    <option value="Terrain de Hay Al Nahda">Terrain de Hay Al Nahda</option>
                    <option value="Terrain de Hay Al Wafa">Terrain de Hay Al Wafa</option>
                    <option value="Terrain de Boukhalef">Terrain de Boukhalef</option>
                    <option value="Terrain de Dradeb">Terrain de Dradeb</option>
                    <option value="Terrain de Rmilat">Terrain de Rmilat</option>
                    <option value="Terrain de Sidi Bouknadel">Terrain de Sidi Bouknadel</option>
                    <option value="Terrain de Sidi Moumen">Terrain de Sidi Moumen</option>
                    <option value="Terrain de Hay Al Karam">Terrain de Hay Al Karam</option>
                    <option value="Terrain de Hay Al Andalous">Terrain de Hay Al Andalous</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="date">Date</label>
                <input type="date" id="date" name="date" required>
            </div>
            
            <div class="form-group">
                <label for="heure">Heure</label>
                <select id="heure" name="heure" required>
                    <option value="13:00">13:00</option>
                    <option value="14:00">14:00</option>
                    <option value="15:00">15:00</option>
                    <option value="16:00">16:00</option>
                    <option value="17:00">17:00</option>
                    <option value="18:00">18:00</option>
                    <option value="19:00">19:00</option>
                    <option value="20:00">20:00</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_equipes">Nombre d'équipes</label>
                <select id="nb_equipes" name="nb_equipes" required>
                    <option value="4">4</option>
                    <option value="8">8</option>
                    
                </select>
            </div>
            
            <div class="form-group">
                <label for="nb_joueurs">Nombre de joueurs par équipe</label>
                <select id="nb_joueurs" name="nb_joueurs" required>
                    <option value="6">6</option>
                    <option value="7">7</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Genre des participants</label>
                <div class="type-switcher">
                    <button type="button" class="type-btn<?php echo (!isset($_POST['genre']) || $_POST['genre'] === 'Masculin') ? ' selected' : '' ?>" data-genre="Masculin">Masculin</button>
                    <button type="button" class="type-btn<?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Féminin') ? ' selected' : '' ?>" data-genre="Féminin">Féminin</button>
                    <input type="hidden" name="genre" id="genreInput" value="<?php echo isset($_POST['genre']) ? htmlspecialchars($_POST['genre']) : 'Masculin'; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="frais">Frais de participation (DH)</label>
                <input type="number" id="frais" name="frais" min="0" step="1" required placeholder="Montant en DH">
            </div>
            
            <button type="submit" class="btn">Créer le tournoi</button>
            <a href="dashboard_org.php" class="btn" style="width:380px;margin-top:24px;display:inline-block;text-align:center;">← Retour au tableau de bord</a>
        </form>
    </div>

    <script>
        
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
        // Gender toggle button logic
        document.querySelectorAll('.type-btn[data-genre]').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.type-btn[data-genre]').forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('genreInput').value = this.getAttribute('data-genre');
            });
        });
    </script>
</body>
</html>