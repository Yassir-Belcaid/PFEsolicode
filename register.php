<?php
include 'config.php';

$error = "";
$preservedValues = [
    'type' => $_POST['type'] ?? '',
    'genre' => $_POST['genre'] ?? '',
    'prenom' => $_POST['prenom'] ?? '',
    'nom' => $_POST['nom'] ?? ''
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["type"]) || !in_array($_POST["type"], ["Participant", "Organisateur"])) {
        $error = "Vous devez choisir un type: Joueur ou Organisateur!";
    } elseif ($_POST["type"] === "Participant" && (empty($_POST["genre"]) || !in_array($_POST["genre"], ["Masculin", "Féminin"]))) {
        $error = "Vous devez choisir votre genre!";
    } else {
        $prenom = $_POST["prenom"];
        $nom = $_POST["nom"];
        $password = $_POST["password"];
        $confirm_password = $_POST["confirm_password"];
        $type = $_POST["type"];
        $genre = $_POST["genre"] ?? null;
        
        if ($password !== $confirm_password) {
            $error = "Les mots de passe ne correspondent pas!";
        } else {
            if (empty($prenom) || empty($nom)) {
                $error = "Le prénom et le nom sont obligatoires!";
            } elseif (strlen($password) < 6) {
                $error = "Le mot de passe doit contenir au moins 6 caractères!";
            } elseif ($type === 'Participant' && empty($genre)) {
                $error = "Vous devez choisir votre genre!";
            } else {
                $photoPath = null;
                
                if (!isset($_FILES["photo"]) || $_FILES["photo"]["error"] != 0) {
                    $error = "Vous devez choisir une photo de profil!";
                } else {
                    $targetDir = "uploads/";
                    if (!file_exists($targetDir)) {
                        mkdir($targetDir, 0777, true);
                    }
                    
                    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                    if (!in_array($_FILES["photo"]["type"], $allowedTypes)) {
                        $error = "Seuls les fichiers JPG, PNG et GIF sont autorisés!";
                    } else {
                        if ($_FILES["photo"]["size"] > 5 * 1024 * 1024) {
                            $error = "La taille de l'image ne doit pas dépasser 5MB!";
                        } else {
                            $photoName = uniqid() . "_" . basename($_FILES["photo"]["name"]);
                            $photoPath = $targetDir . $photoName;
                            
                            if (!move_uploaded_file($_FILES["photo"]["tmp_name"], $photoPath)) {
                                $error = "Erreur lors du téléchargement de l'image!";
                            }
                        }
                    }
                }
                
                if (empty($error)) {
                    try {
                        $checkSql = "SELECT id_utilisateur FROM Utilisateur WHERE prenom = ? AND nom = ?";
                        $checkStmt = $conn->prepare($checkSql);
                        $checkStmt->bind_param("ss", $prenom, $nom);
                        $checkStmt->execute();
                        $checkResult = $checkStmt->get_result();
                        
                        if ($checkResult->num_rows > 0) {
                            $error = "Un utilisateur avec ce prénom et nom existe déjà!";
                        } else {
                            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                            
                            $sql = "INSERT INTO Utilisateur (prenom, nom, mot_de_passe, type, photo, genre) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ssssss", $prenom, $nom, $hashedPassword, $type, $photoPath, $genre);
                            
                            if ($stmt->execute()) {
                                header("Location: login.php?success=1");
                                exit();
                            } else {
                                $error = "Erreur lors de l'inscription: " . $conn->error;
                            }
                        }
                    } catch (Exception $e) {
                        $error = "Erreur de base de données: " . $e->getMessage();
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inscription</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: url('uploads/TEST.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        .register-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: rgba(0,0,0,0.3);
        }
        .register-container {
            background: rgba(40, 40, 40, 0.95);
            border-radius: 30px 30px 40px 40px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            padding: 40px 32px 32px 32px;
            max-width: 500px;
            width: 100%;
            position: relative;
        }
        .tab-switcher {
            display: flex;
            margin-bottom: 30px;
            border-radius: 20px 20px 0 0;
            overflow: hidden;
        }
        #genderSection.tab-switcher {
            justify-content: center;
            margin-bottom: 18px;
        }
        .type-btn { 
            flex: 1; 
            padding: 16px 0;
            background: #444;
            color: #fff;
            border: none;
            outline: none;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
            border-right: 1px solid #333;
        }
        .type-btn:last-child { border-right: none; }
        .type-btn.selected {
            background: #fff;
            color: #222;
        }
        .form-group {
            margin-bottom: 18px;
            display: flex;
            align-items: center;
        }
        .form-group label {
            flex: 0 0 160px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 100;
            letter-spacing: 0.5px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
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
        .form-group input[type="text"]:focus,
        .form-group input[type="password"]:focus {
            box-shadow: 0 0 0 2px #4caf50;
        }
        .photo-upload {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
        }
        .photo-upload label {
            flex: 0 0 160px;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .photo-upload .file-input-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            margin-left: 10px;
        }
        .photo-upload input[type="file"] {
            display: none;
        }
        .upload-label {
            display: inline-block;
            background: #fff;
            color: #222;
            border-radius: 50px;
            padding: 6px 18px;
            font-size: 1.2rem;
            font-weight: bold;
            cursor: pointer;
            border: 2px solid #bbb;
            transition: background 0.2s, color 0.2s;
        }
        .upload-label:hover {
            background: #4caf50;
            color: #fff;
            border-color: #4caf50;
        }
        .photo-preview {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-left: 10px;
            border: 2px solid #fff;
            display: none;
        }
        .btn-submit {
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
        .btn-submit:hover {
            background: #4caf50;
            color: #fff;
        }
        .error-message {
            color: #ff5252;
            background: #fff0f0;
            border-radius: 10px;
            padding: 6px 12px;
            margin-bottom: 10px;
            text-align: center;
            font-size: 0.95rem;
        }
        .login-link {
            text-align: center;
            margin-top: 18px;
            color: #fff;
            font-size: 1rem;
        }
        .login-link a {
            color: #4caf50;
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            .register-container { padding: 18px 4px; }
            .form-group label, .photo-upload label { flex: 0 0 90px; font-size: 1rem; }
        }
        #genderSection .type-btn {
            flex: unset;
            min-width: 120px;
            margin: 0 8px;
        }
    </style>
</head>
<body>
    <div class="register-wrapper">
      <div class="register-container">
        <form action="register.php" method="POST" enctype="multipart/form-data" id="registrationForm">
          <div class="tab-switcher">
            <button type="button" class="type-btn <?php echo ($preservedValues['type'] === 'Participant') ? 'selected' : '' ?>" data-type="Participant">
               Player
            </button>
            <button type="button" class="type-btn <?php echo ($preservedValues['type'] === 'Organisateur') ? 'selected' : '' ?>" data-type="Organisateur">
               Organizer
            </button>
                </div>
                <input type="hidden" name="type" id="userType" value="<?php echo htmlspecialchars($preservedValues['type']); ?>" required>
                <div class="error-message" id="typeError">
                    <?php if ($error && !isset($_POST["type"])) echo $error; ?>
                </div>
          <div class="tab-switcher" id="genderSection" style="<?php echo ($preservedValues['type'] === 'Participant') ? 'display: flex;' : 'display: none;' ?> margin-bottom: 18px; align-items: center;">
            <span style="color: #fff; font-size: 1.1rem; font-weight: 500; margin-right: 18px;">Genre :</span>
                    <button type="button" class="type-btn <?php echo ($preservedValues['genre'] === 'Masculin') ? 'selected' : '' ?>" data-gender="Masculin">Masculin</button>
                    <button type="button" class="type-btn <?php echo ($preservedValues['genre'] === 'Féminin') ? 'selected' : '' ?>" data-gender="Féminin">Féminin</button>
            <input type="hidden" name="genre" id="userGender" value="<?php echo htmlspecialchars($preservedValues['genre']); ?>" <?php echo ($preservedValues['type'] === 'Participant') ? 'required' : '' ?>>
                </div>
                <div class="error-message" id="genderError">
                    <?php if ($error && strpos($error, 'genre') !== false) echo $error; ?>
                </div>
          <div class="form-group">
            <label for="prenom">Your First Name :</label>
            <input type="text" name="prenom" id="prenom" required value="<?php echo htmlspecialchars($preservedValues['prenom']); ?>">
          </div>
          <div class="form-group">
            <label for="nom">Your Last Name :</label>
            <input type="text" name="nom" id="nom" required value="<?php echo htmlspecialchars($preservedValues['nom']); ?>">
            </div>
            <div class="photo-upload">
            <label for="photoInput">Your picture :</label>
            <div class="file-input-wrapper">
              <label for="photoInput" class="upload-label">Add your picture</label>
                <input type="file" name="photo" id="photoInput" accept="image/*" required>
              
            </div>
            
            </div>
            <div class="form-group">
            <label for="password">Password :</label>
            <input type="password" name="password" id="password" required>
            </div>
            <div class="form-group">
            <label for="confirm_password">Confirm Password :</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            </div>
                <div class="error-message" id="passwordError">
                    <?php if ($error && $error !== "Vous devez choisir un type: Joueur ou Organisateur!") echo $error; ?>
                </div>
          <button type="submit" class="btn-submit" id="submitBtn">Login</button>
        </form>
        <div class="login-link">
          Already have an account? <a href="login.php">Login here</a>
        </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeButtons = document.querySelectorAll('.type-btn[data-type]');
            const genderButtons = document.querySelectorAll('.type-btn[data-gender]');
            const userTypeInput = document.getElementById('userType');
            const userGenderInput = document.getElementById('userGender');
            const typeError = document.getElementById('typeError');
            const genderError = document.getElementById('genderError');
            const passwordError = document.getElementById('passwordError');
            const photoError = document.getElementById('photoError');
            const photoInput = document.getElementById('photoInput');
            const photoPreview = document.getElementById('photoPreview');
            const genderSection = document.getElementById('genderSection');
            const form = document.getElementById('registrationForm');
            const submitBtn = document.getElementById('submitBtn');
            
            // Initialize form state based on PHP values
            if (userTypeInput.value) {
                document.querySelector(`.type-btn[data-type="${userTypeInput.value}"]`).classList.add('selected');
                if (userTypeInput.value === 'Participant') {
                    genderSection.style.display = 'block';
                }
            }
            
            if (userGenderInput.value) {
                document.querySelector(`.type-btn[data-gender="${userGenderInput.value}"]`).classList.add('selected');
            }
            
            // Type button handling
            typeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    typeButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    userTypeInput.value = this.getAttribute('data-type');
                    typeError.textContent = '';
                    
                    if (userTypeInput.value === 'Participant') {
                        genderSection.style.display = 'block';
                        userGenderInput.required = true;
                    } else {
                        genderSection.style.display = 'none';
                        userGenderInput.value = '';
                        userGenderInput.required = false;
                        genderButtons.forEach(btn => btn.classList.remove('selected'));
                    }
                });
            });
            
            // Gender button handling
            genderButtons.forEach(button => {
                button.addEventListener('click', function() {
                    genderButtons.forEach(btn => btn.classList.remove('selected'));
                    this.classList.add('selected');
                    userGenderInput.value = this.getAttribute('data-gender');
                    genderError.textContent = '';
                });
            });
            
            // Photo preview
            photoInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        photoPreview.src = event.target.result;
                        photoPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                    photoError.textContent = '';
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                if (photoInput.files.length === 0) {
                    photoError.textContent = 'Veuillez choisir une photo de profil.';
                    isValid = false;
                } else {
                    const file = photoInput.files[0];
                    const allowedTypes = ["image/jpeg", "image/png", "image/gif"];
                    if (!allowedTypes.includes(file.type)) {
                        photoError.textContent = 'Seuls les fichiers JPG, PNG et GIF sont autorisés';
                        isValid = false;
                    } else {
                        photoError.textContent = '';
                    }
                }
                
                if (!userTypeInput.value || (userTypeInput.value !== 'Participant' && userTypeInput.value !== 'Organisateur')) {
                    typeError.textContent = 'Vous devez choisir un type (Joueur ou Organisateur) !';
                    isValid = false;
                }
                
                if (userTypeInput.value === 'Participant' && (!userGenderInput.value || (userGenderInput.value !== 'Masculin' && userGenderInput.value !== 'Féminin'))) {
                    genderError.textContent = 'Vous devez choisir votre genre !';
                    isValid = false;
                }
                
                const password = document.querySelector('input[name="password"]').value;
                const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
                
                if (password !== confirmPassword) {
                    passwordError.textContent = 'Les mots de passe ne correspondent pas!';
                    isValid = false;
                }
                
                if (!isValid) {
                    e.preventDefault();
                } else {
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Enregistrement...';
                    submitBtn.classList.add('loading');
                }
            });
        });
    </script>
</body>
</html>