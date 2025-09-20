<?php
include 'config.php';
$database = new Database();
$db = $database->getConnection();

// Get all quiz attempts
$query = "SELECT qa.*, q.title as quiz_title 
          FROM quiz_attempts qa 
          JOIN quizzes q ON qa.quiz_id = q.id 
          ORDER BY qa.attempted_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$attempts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Results History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center mb-4">Quiz Results History</h1>
                
                <?php if (count($attempts) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Quiz</th>
                                    <th>User</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attempts as $attempt): ?>
                                    <?php $percentage = ($attempt['score'] / $attempt['total_questions']) * 100; ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                        <td><?php echo htmlspecialchars($attempt['user_name']); ?></td>
                                        <td><?php echo $attempt['score']; ?>/<?php echo $attempt['total_questions']; ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php echo $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo number_format($percentage, 1); ?>%
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y g:i A', strtotime($attempt['attempted_at'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">
                        No quiz attempts found. <a href="index.php" class="alert-link">Take a quiz</a> to see results here.
                    </div>
                <?php endif; ?>
                
                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">Back to Quizzes</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
