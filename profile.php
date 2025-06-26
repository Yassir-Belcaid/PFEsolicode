<?php
include 'config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $prenom = $_POST["prenom"];
    $nom = $_POST["nom"];
    
    $stmt = $conn->prepare("UPDATE Utilisateur SET prenom = ?, nom = ? WHERE id_utilisateur = ?");
    $stmt->bind_param("ssi", $prenom, $nom, $_SESSION["user_id"]);
    $stmt->execute();
    
    $_SESSION["prenom"] = $prenom;
    $_SESSION["nom"] = $nom;
    
    header("Location: profile.php?success=1");
    exit();
}

$stmt = $conn->prepare("SELECT prenom, nom, photo FROM Utilisateur WHERE id_utilisateur = ?");
$stmt->bind_param("i", $_SESSION["user_id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Mon Profil</title>
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
            max-width: 500px;
            width: 100%;
            position: relative;
        }
        .header-container {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-title {
            color: #fff;
            font-size: 28px;
            font-weight: bold;
        }
        .profile-section {
            display: flex;
            gap: 30px;
            align-items: center;
            margin-bottom: 30px;
        }
        .profile-pic-container {
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 30%;
            object-fit: cover;
            border: 3px solid #ecf0f1;
        }
        .name-display h2 {
            margin: 0;
            color: #fff;
            font-size: 24px;
        }
        .form-group {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        .form-group label {
            flex: 0 0 120px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        .form-group input {
            flex: 1;
            padding: 10px 16px;
            border: none;
            border-radius: 20px;
            font-size: 1rem;
            background: #eee;
            margin-left: 10px;
            outline: none;
            transition: box-shadow 0.2s;
        }
        .form-group input:focus {
            box-shadow: 0 0 0 2px #4caf50;
        }
        .save-btn {
            background: #fff;
            color: #222;
            border: none;
            border-radius: 20px;
            padding: 10px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 500px;
            margin: 18px auto 0 auto;
            display: block;
            transition: background 0.2s, color 0.2s;
        }
        .save-btn:hover {
            background: #4caf50;
            color: #fff;
        }
        .success-msg {
            color: #27ae60;
            text-align: center;
            margin-bottom: 20px;
        }
        @media (max-width: 600px) {
            .container { padding: 18px 4px; }
            .profile-title { font-size: 1.2rem; }
            .form-group label { flex: 0 0 90px; font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <h1 class="profile-title">Mon Profil</h1>
        </div>
        
        <?php if (isset($_GET['success'])) { ?>
            <div class="success-msg">Profil mis à jour avec succès!</div>
        <?php } ?>

        <div class="profile-section">
            <div class="profile-pic-container">
                <img src="<?php echo isset($user['photo']) ? $user['photo'] : 'https://via.placeholder.com/150?text=PROFIL'; ?>" 
                     class="profile-pic">
            </div>
            <div class="name-display" style="margin-left: 60px;">
                <h2><?php echo htmlspecialchars($user['prenom']) . ' ' . htmlspecialchars($user['nom']); ?></h2>
            </div>
        </div>

        <form action="profile.php" method="POST">
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($user['prenom']); ?>" required>
            </div>

            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($user['nom']); ?>" required>
            </div>

            <button type="submit" class="save-btn">Enregistrer les modifications</button>
        </form>
    </div>

</body>
</html>