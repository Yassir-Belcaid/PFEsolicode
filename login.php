<?php
include 'config.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $prenom = trim($conn->real_escape_string($_POST["prenom"]));
    $nom = trim($conn->real_escape_string($_POST["nom"]));
    $password = $_POST["password"];

    
    $sql = "SELECT id_utilisateur, prenom, nom, mot_de_passe, type, photo 
            FROM Utilisateur 
            WHERE prenom = ? AND nom = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Erreur de préparation: " . $conn->error);
    }

    $stmt->bind_param("ss", $prenom, $nom);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        
        if (password_verify($password, $user["mot_de_passe"])) {
            
            session_start();
            
            
            $_SESSION = [
                "user_id" => $user["id_utilisateur"],
                "prenom" => $user["prenom"],
                "nom" => $user["nom"],
                "user_type" => $user["type"],
                "photo" => $user["photo"],
                "logged_in" => true
            ];

            
            if ($user["type"] === "Organisateur") {
                header("Location: dashboard_org.php");
            } else {
                header("Location: dashboard_joueur.php");
            }
            exit();
        } else {
            $error = "Mot de passe incorrect!";
        }
    } else {
        $error = "Utilisateur non trouvé! Vérifiez votre prénom et nom.";
    }
    
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
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
        .login-container {
            background: rgba(40, 40, 40, 0.95);
            border-radius: 30px 30px 40px 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px 32px 32px 32px;
            max-width: 400px;
            width: 100%;
            position: relative;
        }
        h1 {
            color: #fff;
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 18px;
            display: flex;
            align-items: center;
        }
        label {
            flex: 0 0 120px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        input {
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
        input:focus {
            box-shadow: 0 0 0 2px #4caf50;
        }
        .btn {
            width: 120px;
            margin: 18px auto 0 auto;
            display: block;
            background: #fff;
            color: #222;
            border: none;
            border-radius: 20px;
            padding: 10px 0;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
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
        .register-link {
            text-align: center;
            margin-top: 18px;
            color: #fff;
            font-size: 1rem;
        }
        .register-link a {
            color: #4caf50;
            text-decoration: underline;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .login-container { padding: 18px 4px; }
            label { flex: 0 0 90px; font-size: 1rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Connexion</h1>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required
                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Se connecter</button>
        </form>
        
        <div class="register-link">
            Pas encore de compte? <a href="register.php">S'inscrire ici</a>
        </div>
    </div>
</body>
</html>