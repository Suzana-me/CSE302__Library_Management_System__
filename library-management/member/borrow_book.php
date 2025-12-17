<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['member_id'])) {
    header("Location: login.php");
    exit();
}

$member_id = $_SESSION['member_id'];
$book_id = $_GET['book_id'] ?? 0;

if ($book_id) {
    // Check availability
    $check_sql = "SELECT quantity FROM books WHERE book_id = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $book = $result->fetch_assoc();

    if ($book && $book['quantity'] > 0) {
        $conn->begin_transaction();
        try {
            // Issue book (Due date: 14 days from now)
            $due_date = date('Y-m-d', strtotime('+14 days'));
            $issue_sql = "INSERT INTO issued_books (book_id, member_id, due_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($issue_sql);
            $stmt->bind_param("iis", $book_id, $member_id, $due_date);
            $stmt->execute();

            // Decrease quantity
            $update_sql = "UPDATE books SET quantity = quantity - 1 WHERE book_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("i", $book_id);
            $stmt->execute();

            $conn->commit();
            
            // Redirect with success
            header("Location: my_books.php?msg=borrowed");
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error borrowing book.";
        }
    } else {
        // Book not available
        echo "<script>
            alert('Sorry, this book is currently not available.');
            window.location.href = 'books.php';
        </script>";
        exit();
    }
} else {
    header("Location: books.php");
    exit();
}
?>
