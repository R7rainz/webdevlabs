<?php
include 'config.php';
session_start();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$quiz_id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get quiz details
$query = "SELECT * FROM quizzes WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quiz) {
    header("Location: index.php");
    exit();
}

// Get questions for this quiz
$query = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id";
$stmt = $db->prepare($query);
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get answers for each question
foreach ($questions as &$question) {
    $query = "SELECT * FROM answers WHERE question_id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$question['id']]);
    $question['answers'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
unset($question);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? 'Anonymous';
    $score = 0;
    $total_questions = count($questions);
    
    foreach ($questions as $question) {
        $user_answer = $_POST['question_' . $question['id']] ?? '';
        
        if ($question['question_type'] === 'true_false') {
            $correct_answer = '';
            foreach ($question['answers'] as $answer) {
                if ($answer['is_correct']) {
                    $correct_answer = $answer['answer_text'];
                    break;
                }
            }
            if ($user_answer === $correct_answer) {
                $score++;
            }
        } else {
            // For multiple choice, check if the selected answer is correct
            $query = "SELECT is_correct FROM answers WHERE id = ? AND question_id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$user_answer, $question['id']]);
            $answer = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($answer && $answer['is_correct']) {
                $score++;
            }
        }
    }
    
    // Save attempt to database
    $query = "INSERT INTO quiz_attempts (quiz_id, user_name, score, total_questions) VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([$quiz_id, $user_name, $score, $total_questions]);
    $attempt_id = $db->lastInsertId();
    
    $_SESSION['quiz_result'] = [
        'score' => $score,
        'total' => $total_questions,
        'attempt_id' => $attempt_id
    ];
    
    header("Location: result.php?attempt_id=" . $attempt_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                        <p class="text-muted"><?php echo htmlspecialchars($quiz['description']); ?></p>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="user_name" class="form-label">Your Name (optional)</label>
                                <input type="text" class="form-control" id="user_name" name="user_name" placeholder="Enter your name">
                            </div>
                            
                            <?php foreach ($questions as $index => $question): ?>
                                <div class="mb-4 p-3 border rounded">
                                    <h5 class="mb-3">Question <?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></h5>
                                    
                                    <?php if ($question['question_type'] === 'true_false'): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_true" value="True" required>
                                            <label class="form-check-label" for="q<?php echo $question['id']; ?>_true">True</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="question_<?php echo $question['id']; ?>" id="q<?php echo $question['id']; ?>_false" value="False">
                                            <label class="form-check-label" for="q<?php echo $question['id']; ?>_false">False</label>
                                        </div>
                                    <?php else: ?>
                                        <?php foreach ($question['answers'] as $answer): ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="question_<?php echo $question['id']; ?>" id="answer_<?php echo $answer['id']; ?>" value="<?php echo $answer['id']; ?>" required>
                                                <label class="form-check-label" for="answer_<?php echo $answer['id']; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                </label>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                            
                            <button type="submit" class="btn btn-success btn-lg">Submit Quiz</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
