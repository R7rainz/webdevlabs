<?php
include 'config.php';
$database = new Database();
$db = $database->getConnection();

// Get all quizzes
$query = "SELECT * FROM quizzes ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Quiz Application</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .quiz-card {
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .quiz-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-12 text-center">
                <h1 class="mb-4">Online Quiz Application</h1>
                <p class="lead">Test your knowledge with our interactive quizzes</p>
            </div>
        </div>

        <div class="row mt-4">
            <?php if (count($quizzes) > 0): ?>
                <?php foreach ($quizzes as $quiz): ?>
                    <div class="col-md-4">
                        <div class="card quiz-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($quiz['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($quiz['description']); ?></p>
                                <a href="quiz.php?id=<?php echo $quiz['id']; ?>" class="btn btn-primary">Start Quiz</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">No quizzes available at the moment.</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="row mt-4">
            <div class="col-md-12 text-center">
                <a href="results.php" class="btn btn-outline-secondary">View Previous Results</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
