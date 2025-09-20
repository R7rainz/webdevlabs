<?php
include 'config.php';
session_start();

// Simple admin authentication (in production, use proper authentication)
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
    } else {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $error = "Invalid password";
        }
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Admin Login</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body>
            <div class="container mt-5">
                <div class="row justify-content-center">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Admin Login</div>
                            <div class="card-body">
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" name="password" class="form-control" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Login</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit();
    }
}

$database = new Database();
$db = $database->getConnection();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_quiz'])) {
        $title = $_POST['title'];
        $description = $_POST['description'];
        
        $query = "INSERT INTO quizzes (title, description) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$title, $description]);
        $quiz_id = $db->lastInsertId();
        
        header("Location: admin.php?quiz_id=" . $quiz_id);
        exit();
    }
    
    if (isset($_POST['add_question'])) {
        $quiz_id = $_POST['quiz_id'];
        $question_text = $_POST['question_text'];
        $question_type = $_POST['question_type'];
        
        $query = "INSERT INTO questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$quiz_id, $question_text, $question_type]);
        $question_id = $db->lastInsertId();
        
        // Add answers
        foreach ($_POST['answers'] as $index => $answer_text) {
            $is_correct = ($_POST['correct_answer'] == $index) ? 1 : 0;
            $query = "INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)";
            $stmt = $db->prepare($query);
            $stmt->execute([$question_id, $answer_text, $is_correct]);
        }
        
        header("Location: admin.php?quiz_id=" . $quiz_id);
        exit();
    }
}

// Get all quizzes
$query = "SELECT * FROM quizzes ORDER BY title";
$stmt = $db->prepare($query);
$stmt->execute();
$quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$selected_quiz = null;
if (isset($_GET['quiz_id'])) {
    $query = "SELECT * FROM quizzes WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['quiz_id']]);
    $selected_quiz = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($selected_quiz) {
        $query = "SELECT * FROM questions WHERE quiz_id = ? ORDER BY id";
        $stmt = $db->prepare($query);
        $stmt->execute([$selected_quiz['id']]);
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1 class="text-center">Quiz Admin Panel</h1>
        
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">Add New Quiz</div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Quiz Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <button type="submit" name="add_quiz" class="btn btn-primary">Add Quiz</button>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header">Select Quiz</div>
                    <div class="card-body">
                        <form method="GET">
                            <div class="mb-3">
                                <select name="quiz_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Select a quiz</option>
                                    <?php foreach ($quizzes as $quiz): ?>
                                        <option value="<?php echo $quiz['id']; ?>" <?php echo (isset($_GET['quiz_id']) && $_GET['quiz_id'] == $quiz['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($quiz['title']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <?php if ($selected_quiz): ?>
                    <div class="card">
                        <div class="card-header">Add Question to: <?php echo htmlspecialchars($selected_quiz['title']); ?></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="quiz_id" value="<?php echo $selected_quiz['id']; ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Question Text</label>
                                    <textarea name="question_text" class="form-control" rows="3" required></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Question Type</label>
                                    <select name="question_type" class="form-select">
                                        <option value="multiple_choice">Multiple Choice</option>
                                        <option value="true_false">True/False</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Answers</label>
                                    <?php for ($i = 0; $i < 4; $i++): ?>
                                        <div class="input-group mb-2">
                                            <span class="input-group-text"><?php echo $i + 1; ?></span>
                                            <input type="text" name="answers[]" class="form-control" placeholder="Answer text" required>
                                            <div class="input-group-text">
                                                <input type="radio" name="correct_answer" value="<?php echo $i; ?>" required>
                                            </div>
                                        </div>
                                    <?php endfor; ?>
                                    <small class="text-muted">Select the correct answer by clicking the radio button</small>
                                </div>
                                
                                <button type="submit" name="add_question" class="btn btn-success">Add Question</button>
                            </form>
                        </div>
                    </div>
                    
                    <?php if (isset($questions) && count($questions) > 0): ?>
                        <div class="card mt-4">
                            <div class="card-header">Existing Questions</div>
                            <div class="card-body">
                                <?php foreach ($questions as $question): ?>
                                    <div class="border p-3 mb-3">
                                        <strong><?php echo htmlspecialchars($question['question_text']); ?></strong>
                                        <?php
                                        $query = "SELECT * FROM answers WHERE question_id = ?";
                                        $stmt = $db->prepare($query);
                                        $stmt->execute([$question['id']]);
                                        $answers = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        ?>
                                        <ul class="mt-2">
                                            <?php foreach ($answers as $answer): ?>
                                                <li class="<?php echo $answer['is_correct'] ? 'text-success fw-bold' : ''; ?>">
                                                    <?php echo htmlspecialchars($answer['answer_text']); ?>
                                                    <?php echo $answer['is_correct'] ? ' (Correct)' : ''; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-secondary">Back to Quiz Site</a>
            <a href="admin.php?logout=1" class="btn btn-danger">Logout</a>
        </div>
    </div>
</body>
</html>
