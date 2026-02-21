<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['logout'])) { 
    session_destroy();
    header("Location: login.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo Utente</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .profile-item {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .profile-item label {
            font-weight: bold;
            color: #555;
            display: block;
            margin-bottom: 5px;
        }
        
        .profile-item span {
            color: #333;
            font-size: 16px;
        }
        
        .actions {
            text-align: center;
            margin-top: 30px;
        }
        
        button {
            display: inline-block;
            margin: 0 10px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        
        a:hover {
            background-color: #0056b3;
        }
        
        .logout {
            background-color: #dc3545;
        }
        
        .logout:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Profilo Utente</h1>
        <!--
        <div class="profile-item">
            <label>ID Utente:</label>
            <span><?php echo htmlspecialchars($_SESSION["user_id"]); ?></span>
        </div>-->
        
        <div class="profile-item">
            <label>Username:</label>
            <span><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
        </div>
        
        <div class="profile-item">
            <label>Nome:</label>
            <span><?php echo htmlspecialchars($_SESSION["nome"]); ?></span>
        </div>
        
        <div class="profile-item">
            <label>Cognome:</label>
            <span><?php echo htmlspecialchars($_SESSION["cognome"]); ?></span>
        </div>
        
        <div class="profile-item">
            <label>Email:</label>
            <span><?php echo htmlspecialchars($_SESSION["email"]); ?></span>
        </div>
        
        <div class="profile-item">
            <label>Ruolo:</label>
            <span><?php echo htmlspecialchars($_SESSION["ruolo"]); ?></span>
        </div>
        
        <div class="profile-item">
            <label>Data Registrazione:</label>
            <span><?php echo htmlspecialchars($_SESSION["data_registrazione"]); ?></span>
        </div>
        
        <div class="actions">
            

            <form method="post">
                <button type="submit" name="logout" class="logout">
                Logout
                </button>
                <button type="submit" name="edit_profile" class="edit_profile">
                Edit Profile (coming soon)
                </button>
            </form>
            
        </div>
    </div>
</body>
</html>