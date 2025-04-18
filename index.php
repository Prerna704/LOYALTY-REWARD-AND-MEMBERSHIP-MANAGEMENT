<?php
// index.php - Main portal page
require_once 'config.php';

// Check if the user is logged in, if not then redirect to login page
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Fetch user's current points and membership level
$user_id = $_SESSION["id"];
$points = $_SESSION["points"];
$membership_level = $_SESSION["membership_level"];

// Fetch available rewards
$rewards = [];
$sql = "SELECT * FROM rewards ORDER BY points_required ASC";
if ($stmt = $pdo->prepare($sql)) {
    if ($stmt->execute()) {
        $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Fetch user's recent transactions
$transactions = [];
$sql = "SELECT * FROM transactions WHERE user_id = :user_id ORDER BY transaction_date DESC LIMIT 5";
if ($stmt = $pdo->prepare($sql)) {
    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Handle reward redemption
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["redeem"])) {
    $reward_id = $_POST["reward_id"];
    
    // Get reward details
    $sql = "SELECT * FROM rewards WHERE id = :reward_id";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":reward_id", $reward_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $reward = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($reward && $points >= $reward["points_required"]) {
                // Start transaction
                $pdo->beginTransaction();
                
                try {
                    // Deduct points
                    $new_points = $points - $reward["points_required"];
                    $sql = "UPDATE users SET points = :points WHERE id = :user_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":points", $new_points, PDO::FETCH_ASSOC);
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $stmt->execute();
                    
                    // Record transaction
                    $sql = "INSERT INTO transactions (user_id, points, type, description) VALUES (:user_id, :points, 'redeemed', :description)";
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                    $stmt->bindParam(":points", $reward["points_required"], PDO::PARAM_INT);
                    $stmt->bindValue(":description", "Redeemed: " . $reward["name"]);
                    $stmt->execute();
                    
                    // Update membership level if applicable
                    if ($new_points >= 500 && $membership_level != "VIP") {
                        $sql = "UPDATE users SET membership_level = 'VIP' WHERE id = :user_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
                        $stmt->execute();
                        $_SESSION["membership_level"] = "VIP";
                    }
                    
                    // Commit transaction
                    $pdo->commit();
                    
                    // Update session variables
                    $_SESSION["points"] = $new_points;
                    $points = $new_points;
                    
                    $success_message = "Successfully redeemed " . $reward["name"] . "!";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error_message = "Error processing redemption. Please try again.";
                }
            } else {
                $error_message = "Not enough points to redeem this reward.";
            }
        }
    }
}

// Handle points earning (simulated for demo)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["earn_points"])) {
    $earn_amount = 10; // Demo - earn 10 points
    
    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Add points
        $new_points = $points + $earn_amount;
        $sql = "UPDATE users SET points = :points WHERE id = :user_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":points", $new_points, PDO::PARAM_INT);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->execute();
        
        // Record transaction
        $sql = "INSERT INTO transactions (user_id, points, type, description) VALUES (:user_id, :points, 'earned', :description)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
        $stmt->bindParam(":points", $earn_amount, PDO::PARAM_INT);
        $stmt->bindValue(":description", "Points earned for activity");
        $stmt->execute();
        
        // Update membership level if applicable
        if ($new_points >= 500 && $membership_level != "VIP") {
            $sql = "UPDATE users SET membership_level = 'VIP' WHERE id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $_SESSION["membership_level"] = "VIP";
        }
        
        // Commit transaction
        $pdo->commit();
        
        // Update session variables
        $_SESSION["points"] = $new_points;
        $points = $new_points;
        
        $success_message = "Earned $earn_amount points!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error_message = "Error earning points. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loyalty Rewards Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4A6FA5;
            --secondary: #166088;
            --accent: #4FC3F7;
            --success: #4CAF50;
            --warning: #FFC107;
            --danger: #F44336;
            --light: #F8F9FA;
            --dark: #343A40;
            --bg-gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f7fa;
            color: var(--dark);
            min-height: 100vh;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: var(--bg-gradient);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: var(--card-shadow);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-content h1 {
            font-size: 2rem;
            margin-bottom: 5px;
        }

        .header-content p {
            opacity: 0.9;
        }

        .user-info {
            text-align: right;
        }

        .user-info p {
            margin-bottom: 5px;
        }

        .logout-btn {
            background: white;
            color: var(--primary);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: var(--accent);
            color: white;
        }

        .dashboard {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: var(--card-shadow);
            margin-bottom: 20px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .card-header h2 {
            font-size: 1.5rem;
            color: var(--secondary);
        }

        .points-display {
            text-align: center;
            padding: 20px;
            background: var(--bg-gradient);
            color: white;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .points-value {
            font-size: 3rem;
            font-weight: bold;
            margin: 10px 0;
        }

        .membership-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.8rem;
            margin-top: 10px;
        }

        .basic-badge {
            background: #6c757d;
            color: white;
        }

        .vip-badge {
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: white;
        }

        .reward-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .reward-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .reward-info h3 {
            margin-bottom: 5px;
            color: var(--secondary);
        }

        .reward-info p {
            color: #666;
            font-size: 0.9rem;
        }

        .reward-points {
            background: var(--accent);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            min-width: 70px;
            text-align: center;
        }

        .redeem-btn {
            background: var(--success);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .redeem-btn:hover {
            background: #3d8b40;
        }

        .redeem-btn:disabled {
            background: #cccccc;
            cursor: not-allowed;
        }

        .earn-btn {
            background: var(--warning);
            color: var(--dark);
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
            margin-top: 10px;
        }

        .earn-btn:hover {
            background: #e0a800;
        }

        .transaction-item {
            padding: 10px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
        }

        .transaction-item:last-child {
            border-bottom: none;
        }

        .transaction-points {
            font-weight: bold;
        }

        .earned {
            color: var(--success);
        }

        .redeemed {
            color: var(--danger);
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: rgba(76, 175, 80, 0.1);
            border: 1px solid rgba(76, 175, 80, 0.3);
            color: var(--success);
        }

        .alert-danger {
            background-color: rgba(244, 67, 54, 0.1);
            border: 1px solid rgba(244, 67, 54, 0.3);
            color: var(--danger);
        }

        .progress-container {
            width: 100%;
            background-color: #e0e0e0;
            border-radius: 10px;
            margin: 15px 0;
        }

        .progress-bar {
            height: 20px;
            border-radius: 10px;
            background: var(--bg-gradient);
            text-align: center;
            line-height: 20px;
            color: white;
            font-weight: bold;
        }

        @media (max-width: 768px) {
            .dashboard {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php
// header.php - Common header for all pages
?>
<header style="background: #4A6FA5; color: white; padding: 15px 0;">
    <div class="container" style="max-width: 1200px; margin: 0 auto; display: flex; justify-content: space-between;">
        <h1 style="margin: 0;"><i class="fas fa-crown"></i> Loyalty Rewards</h1>
        <nav>
            <a href="index.php" style="color: white; margin: 0 20px; text-decoration: none;">Dashboard</a>
            <a href="profile.php" style="color: white; margin: 0 20px;text-decoration: none;">My Profile</a>
            <a href="edit_profile.php" style="color: white; margin: 0 20px;text-decoration: none;">Edit Profile</a>
            <a href="logout.php" style="color: white; margin: 0 20px;text-decoration: none;">Logout</a>
        </nav>
    </div>
</header>
    <div class="container">
        

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="dashboard">
            <div class="left-column">
                <div class="card points-display">
                    <h2>Your Points</h2>
                    <div class="points-value"><?php echo $points; ?></div>
                    <div class="membership-badge <?php echo ($membership_level == 'VIP') ? 'vip-badge' : 'basic-badge'; ?>">
                        <?php echo $membership_level; ?> Member
                    </div>
                    
                    <div class="progress-container">
                        <?php 
                        $next_level = 500;
                        $progress = min(100, ($points / $next_level) * 100);
                        ?>
                        <div class="progress-bar" style="width: <?php echo $progress; ?>%">
                            <?php if ($progress < 100) echo round($progress, 0) . "%"; else echo "VIP Level!" ?>
                        </div>
                    </div>
                    
                    <p><?php 
                    if ($membership_level == 'Basic') {
                        echo ($next_level - $points) . " points to VIP status";
                    } else {
                        echo "You've reached VIP status!";
                    }
                    ?></p>
                    
                    <form method="post">
                        <button type="submit" name="earn_points" class="earn-btn">
                            <i class="fas fa-plus-circle"></i> Earn 10 Points (Demo)
                        </button>
                    </form>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Recent Activity</h2>
                        <a href="#"><i class="fas fa-history"></i></a>
                    </div>
                    <div class="card-body">
                        <?php if (count($transactions) > 0): ?>
                            <?php foreach ($transactions as $transaction): ?>
                                <div class="transaction-item">
                                    <div>
                                        <p><?php echo htmlspecialchars($transaction["description"]); ?></p>
                                        <small><?php echo date("M j, Y g:i a", strtotime($transaction["transaction_date"])); ?></small>
                                    </div>
                                    <div class="transaction-points <?php echo $transaction["type"]; ?>">
                                        <?php echo ($transaction["type"] == "earned" ? "+" : "-") . $transaction["points"]; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No recent activity</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="right-column">
                <div class="card">
                    <div class="card-header">
                        <h2>Available Rewards</h2>
                    </div>
                    <div class="card-body">
                        <?php if (count($rewards) > 0): ?>
                            <?php foreach ($rewards as $reward): ?>
                                <form method="post" class="reward-card">
                                    <input type="hidden" name="reward_id" value="<?php echo $reward["id"]; ?>">
                                    <div class="reward-info">
                                        <h3><?php echo htmlspecialchars($reward["name"]); ?></h3>
                                        <p><?php echo htmlspecialchars($reward["description"]); ?></p>
                                    </div>
                                    <div class="reward-points">
                                        <?php echo $reward["points_required"]; ?> pts
                                    </div>
                                    <button type="submit" name="redeem" class="redeem-btn" 
                                        <?php if ($points < $reward["points_required"]) echo "disabled"; ?>>
                                        Redeem
                                    </button>
                                </form>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No rewards available at this time</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h2>Membership Benefits</h2>
                    </div>
                    <div class="card-body">
                        <h3>Basic Membership</h3>
                        <ul style="margin-left: 20px; margin-bottom: 15px;">
                            <li>Earn points on every purchase</li>
                            <li>Redeem points for rewards</li>
                            <li>Exclusive member-only offers</li>
                        </ul>
                        
                        <h3>VIP Membership (500+ points)</h3>
                        <ul style="margin-left: 20px;">
                            <li>All Basic benefits plus:</li>
                            <li>Double points on all purchases</li>
                            <li>Early access to new products</li>
                            <li>Birthday rewards</li>
                            <li>Priority customer service</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>