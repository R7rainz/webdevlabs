<?php
include 'config.php';
session_start();

if (!isset($_GET['attempt_id'])) {
    header("Location: index.php");
    exit();
}

$attempt_id = $_GET['attempt_id'];
$database = new Database();
$db = $database->getConnection();

// Get attempt details
$query = "SELECT qa.*, q.title as quiz_title 
          FROM quiz_attempts qa 
          JOIN quizzes q ON qa.quiz_id = q.id 
          WHERE qa.id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$attempt_id]);
$attempt = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$attempt) {
    header("Location: index.php");
    exit();
}

$percentage = ($attempt['score'] / $attempt['total_questions']) * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .progress {
            height: 30px;
        }
        .result-card {
            border: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6 mx-auto">
                <div class="card result-card">
                    <div class="card-header text-center bg-success text-white">
                        <h3>Quiz Completed!</h3>
                    </div>
                    <div class="card-body text-center">
                        <h4><?php echo htmlspecialchars($attempt['quiz_title']); ?></h4>
                        <p class="text-muted">Attempted by: <?php echo htmlspecialchars($attempt['user_name']); ?></p>
                        
                        <div class="my-4">
                            <h2><?php echo $attempt['score']; ?> / <?php echo $attempt['total_questions']; ?></h2>
                            <p class="lead"><?php echo number_format($percentage, 1); ?>%</p>
                            
                            <div class="progress my-3">
                                <div class="progress-bar 
                                    <?php echo $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger'); ?>" 
                                    role="progressbar" 
                                    style="width: <?php echo $percentage; ?>%"
                                    aria-valuenow="<?php echo $percentage; ?>" 
                                    aria-valuemin="0" 
                                    aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <?php if ($percentage >= 80): ?>
                                <div class="alert alert-success">Excellent! Great job!</div>
                            <?php elseif ($percentage >= 60): ?>
                                <div class="alert alert-warning">Good effort! Keep practicing!</div>
                            <?php else: ?>
                                <div class="alert alert-danger">Keep trying! You'll get better!</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-4">
                            <a href="index.php" class="btn btn-primary">Take Another Quiz</a>
                            <a href="results.php" class="btn btn-outline-secondary">View All Results</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
